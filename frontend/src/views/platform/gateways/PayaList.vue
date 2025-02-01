<template>
  <div class="content">
    <BreadcrumbComponent/>
    <h1>Gateway - Paya API urls</h1>
    <div @click="openPage()">Add New</div>

    <BasicTable :headers="headers" v-model="itemList">
      <template #cell-edit-button="{ item }">
        <div @click="openPage(item.id)">Edit</div>
      </template>
    </BasicTable>

  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";

import {computed, onMounted, reactive} from "vue";
import {useRouter} from "vue-router";
import {get, responseMerge} from '@/services/api';
import BasicTable from "@/components/general/BasicTable.vue";

const router = useRouter();

// Table headers
const headers = [
  {name: 'Name', key: 'name'},
  {name: 'API', key: 'base_url'},
  {name: 'Edit', key: 'edit-button'},
];

const items = reactive<{ id: string, name: string, base_url: string }[]>([]);

const itemList = computed(() => items);

const fetchItem = async () => {
  try {
    const response = await get(`gateways/paya`);
    responseMerge(items, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};


const openPage = async (id: string | null = null) => {
  await router.push({
    name: 'SystemPayaEdit',
    params: {gatewayId: id}
  });
}

onMounted(fetchItem);
</script>