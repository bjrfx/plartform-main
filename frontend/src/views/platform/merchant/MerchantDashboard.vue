<template>
  <div class="content">
    <BreadcrumbComponent/>
    <h1>Admin Dashboard</h1>

  </div>

  <SidebarMenu
      title=""
      description=""
      :items="sidebarMenu"
  >

    <template #cell-users="{ item }">
      <div @click="openPage('UsersList')" v-text="item.title"></div>
    </template>
  </SidebarMenu>

</template>

<script setup lang="ts">
import {computed} from 'vue';
import {useRouter} from 'vue-router';
import BreadcrumbComponent from '@/components/BreadcrumbComponent.vue';
import SidebarMenu from "@/components/SidebarMenu.vue";

// Router instance
const router = useRouter();


const sidebarMenu = computed(() => [
  {
    title: 'All Users',
    children: [{key: "users", title: "Users List"}]
  },
]);


// Navigate to generic page
const openPage = async (name: string) => {
  await router.push({name});
};

</script>
