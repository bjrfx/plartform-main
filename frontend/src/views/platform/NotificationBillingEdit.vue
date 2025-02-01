<template>
  <div class="content">
    <BreadcrumbComponent/>
    <h1>Billing Notification Default Content</h1>
    <div>
      <form @submit.prevent="handleSubmit">
        <div>
          <NotificationBillingsComponent v-model="form.body"/>
        </div>
        <FormAlerts :data="getApiError()"/>
        <FormSubmitButton/>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";

import {onMounted, reactive} from "vue";
import {get, post, responseMerge, getApiError} from "@/services/api";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import NotificationBillingsComponent from "@/components/Notifications/NotificationBillingsComponent.vue";

interface FormField {
  id: string | null;
  body: string;
}

const form = reactive<FormField>({
  id: null,
  body: '',
});


const fetchItem = async () => {
  try {
    const response = await get(`notification-billings`);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to fetch :", error);
  }
};

const handleSubmit = async () => {
  try {
    await post(`notification-billings`, form);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};


onMounted(fetchItem);
</script>