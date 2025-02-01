<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1><span v-text="form.merchant_name"></span>-<span v-text="form.name"></span>-SmartPay</h1>
    <div>
      <form @submit.prevent="handleSubmit">
        <div>
          <FormCheckBox
              id="is_active"
              label="Is Active"
              v-model="form.is_active"
          />
        </div>
        <div>
          <div>
            <FormTextInput
                id="token"
                label="Token"
                v-model="form.token"
                placeholder="Token"
                :required="true"
            />
          </div>
          <div>
            <FormTextInput
                id="custom_url"
                label="API URL"
                v-model="form.custom_url"
                placeholder="API URL"
                :required="true"
            />
          </div>
        </div>
        <FormAlerts :data="getApiError()"/>
        <FormSubmitButton/>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";

import {computed, onMounted, reactive} from "vue";
import {useRoute} from "vue-router";
import {get, getApiError, post, responseMerge} from '@/services/api';
import FormAlerts from "@/components/forms/FormAlerts.vue";


const route = useRoute();

const form = reactive({
  merchant_name: null,
  name: null,
  token: "",
  is_active: true,
  custom_url: "",
});

const breadcrumbs = computed(() => {
  return {
    merchantName: form.merchant_name,
    departmentName: form.name,
  }
});


const merchantId = route.params.merchantId;
const departmentId = route.params.departmentId;


const fetchItem = async () => {
  try {
    const response = await get(`merchants/${merchantId}/departments/${departmentId}/smart-pay`);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const handleSubmit = async () => {
  try {
    await post(`merchants/${merchantId}/departments/${departmentId}/smart-pay`, form);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

onMounted(fetchItem);
</script>