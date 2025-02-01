<template>
  <div class="content">
    <h1>Select another department to pay bill</h1>

    <DepartmentsGalleryComponent :departments="departments"/>
  </div>
  <SidebarCart :key="cartKey"/>
</template>

<script setup lang="ts">
import {onMounted, ref} from 'vue';
import {get} from "@/services/api";
import DepartmentsGalleryComponent from "@/components/merchant/DepartmentsGalleryComponent.vue";
import SidebarCart from "@/components/cart/SidebarCart.vue";

const cartKey = ref(Symbol()); // A Symbol in JavaScript is a unique and immutable value

// Reactive merchants list with explicit typing
const departments = ref([]);


const getDepartments = async () => {
  try {
    const response = await get("merchants/departments");
    departments.value = response.data || [];
  } catch (error) {
    console.error("Failed to fetch merchants:", error);
  }
};

onMounted(getDepartments);
</script>