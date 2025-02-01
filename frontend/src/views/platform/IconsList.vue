<template>
  <div class="content">
    <BreadcrumbComponent/>
    <div class="">
      <h1>Icons</h1>
      <button @click="openItem()" class="btn-add">+ Add Icon</button>
    </div>

    <div class="row">
      <div class="col-2 d-flex justify-content-center" v-for="item in itemsList" :key="item.id">
        <div @click="openItem(item.id)" v-bind:title="item.name" class="btn btn-link">
          <ImgSvg v-model="item.svg_code"/>
          <div v-text="item.name"></div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import {onMounted, ref, computed} from "vue";
import {useRouter} from "vue-router";
import {get} from "@/services/api";
import ImgSvg from "@/components/general/ImgSvg.vue";

// Router instance
const router = useRouter();

// Reactive state for items
const items = ref<{ name: string; svg_code: string; id: string }[]>([]);

// Computed to return the items list
const itemsList = computed(() => items.value);

// Fetch items from API
const fetchItems = async () => {
  try {
    const response = await get("icons");
    items.value = response.data ?? [];
  } catch (error) {
    console.error("Failed to fetch icons:", error);
  }
};

// Navigate to item edit or create page
const openItem = (id: string | null = null) => {
  router.push({
    name: "IconEdit",
    params: {iconId: id},
  });
};

// Fetch items on component mount
onMounted(fetchItems);
</script>

<style scoped>
</style>