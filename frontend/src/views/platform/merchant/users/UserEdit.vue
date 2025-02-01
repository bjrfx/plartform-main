<template>
  <div class="content">
    <BreadcrumbComponent :placeholders="breadcrumbs"/>
    <h1>Users:</h1>
    <UserEditComponent :form="userForm" @submit="handleSubmit"/>
  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import type {FormUser} from "@/components/users/UserEditComponent.vue";  // Use "type" for imports
import UserEditComponent from "@/components/users/UserEditComponent.vue";
import {computed, onMounted, ref} from "vue";
import {get, post, responseMerge} from "@/services/api";
import {useRoute, useRouter} from "vue-router";

const route = useRoute();
const router = useRouter();

const userForm = ref<FormUser>({
  merchant_id: null,
  first_name: '',
  middle_name: null,
  last_name: '',
  id: null,
  email: '',
  name: '',
  city: null,
  street: null,
  state: '',
  zip_code: null,
  phone: null,
  phone_country_code: null,
  is_ebilling_enabled: false,
  password: null,
  role: null,
  is_card_payment_only: false,
  is_enabled: true,
  department_ids: [],
});

const userId = route.params?.userId;

const breadcrumbs = computed(() => {
  return {
    user: userForm.value?.name || 'New User',
  };
});

const fetchItem = async () => {
  try {
    const response = await get(`merchants/users/${userId}`);
    if (response) {
      responseMerge(userForm.value, response.data);
    }
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const handleSubmit = async (formData: FormUser) => {
  try {
    const response = await post(`merchants/users/${userId}`, formData);
    if (!userId && "id" in response.data) {
      await router.replace({
        name: "UserEdit",
        params: {userId: response.data.id},
      });
    }
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

onMounted(async () => {
  if (userId) {
    await fetchItem();
  }
});
</script>
