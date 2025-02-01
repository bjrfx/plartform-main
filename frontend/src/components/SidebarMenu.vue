<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-title" v-text="title"></div>
      <div class="sidebar-description" v-text="description"></div>
    </div>
    <div class="sidebar-body">
      <div v-for="(item, index) in items" :key="index" class="sidebar-item">
        <div v-if="item.title" class="sidebar-item-title">{{ item.title }}</div>
        <ul v-if="item.children && item.children.length">
          <li
              v-for="subItem in item.children"
              :key="subItem.key"
          >
            <div class="sub-item">
              <slot :name="`cell-${subItem.key}`" :item="subItem">
                <div v-text="subItem.title"></div>
              </slot>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <div class="sidebar-logout" v-if="logout">
      <div @click="handleLogout">Logout</div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import {useAuthStore} from "@/stores/auth";
import {useRouter} from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

export interface SidebarChildItem {
  key: string;
  title: string;
  icon?: string | null;
}

interface SidebarItem {
  title: string | null;
  children?: SidebarChildItem[];
}

defineProps({
  title: {
    type: String,
    required: false,
    default: null,
  },
  description: {
    type: String,
    required: false,
    default: null,
  },
  items: {
    type: Array as () => SidebarItem[],
    required: true,
  },
  logout: {
    type: Boolean,
    default: true,
  }
});

const handleLogout = async () => {
  try {
    await authStore.logout();
    await router.push('/');
  } catch (err) {
    console.error('Logout failed:', err);
  }
};
</script>

