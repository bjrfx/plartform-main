<template>
  <div class="content">
    <h1>Welcome to Our Site</h1>

    <DepartmentsGalleryComponent :departments="departments"/>
  </div>
  <div class="sidebar">
    <div class="sign-wrapper">
      <div class="sign-buttons">
        <div @click="toggleSign(true)" class="sign-in" :class="{active:isSignIn}">Sign In</div>
        <div @click="toggleSign(false)" class="sign-up" :class="{active:!isSignIn}">Sign Up</div>
      </div>

      <LoginComponent v-if="isSignIn"/>
      <RegistrationComponent v-else/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {onMounted, ref} from 'vue';
import LoginComponent from '@/components/auth/LoginComponent.vue';
import RegistrationComponent from '@/components/auth/RegistrationComponent.vue';
import {get} from "@/services/api";
import DepartmentsGalleryComponent from "@/components/merchant/DepartmentsGalleryComponent.vue";

const isSignIn = ref(true);
// Toggle function
const toggleSign = (val: boolean) => {
  isSignIn.value = val;
};


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