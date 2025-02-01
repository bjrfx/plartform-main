<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1><span v-text="form.merchant_name"></span>-<span v-text="form.name"></span>-Paya</h1>
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
          <FormCheckBox
              id="allow_guest_payment"
              label="Allow guest checkout (No sign-in required)"
              v-model="form.allow_guest_payment"
          />
        </div>
        <div>
          <FormTextInput
              id="username"
              label="Username"
              v-model="form.username"
              placeholder="Username"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="password"
              label="Password"
              v-model="form.password"
              placeholder="Password"
              :required="true"
          />
        </div>
        <div>
          <FormSelect
              id="gateway_id"
              :options="itemList"
              v-model="form.gateway_id"
              label="API"
              placeholder="Select API"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="web_terminal_id"
              label="WEB Terminal ID"
              v-model="form.web_terminal_id"
              placeholder="WEB Terminal ID"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="large_transaction_terminal_id"
              label="Large Transaction Terminal ID"
              v-model="form.large_transaction_terminal_id"
              placeholder="Large Transaction Terminal ID"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="ccd_void_terminal_id"
              label="CCD Terminal ID (Void)"
              v-model="form.ccd_void_terminal_id"
              placeholder="CCD Terminal ID (Void)"
              :required="true"
          />
        </div>
        <div>
          <div>Convenience Fee</div>
          <div>
            <FormAmountInput
                id="fee_amount"
                label="Amount Fee $"
                v-model="form.fee_amount"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="fee_percentage"
                label="Percentage Fee $"
                v-model="form.fee_percentage"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="fee_amount_large"
                label="Large Amount Fee $"
                v-model="form.fee_amount_large"
                placeholder="#.##"
                :required="false"
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
import FormAmountInput from "@/components/forms/FormAmountInput.vue";
import FormSelect from "@/components/forms/FormSelect.vue";

const route = useRoute();

const breadcrumbs = computed(() => {
  return {
    merchantName: form.merchant_name,
    departmentName: form.name,
  }
});

const form = reactive({
  merchant_name: null,
  name: null,
  api: "",
  is_active: true,
  username: "",
  password: "",
  web_terminal_id: "",
  large_transaction_terminal_id: "",
  ccd_void_terminal_id: "",
  big_amount_void_terminal_id: "",
  gateway_id: null,
  allow_guest_payment: false,
  fee_amount: null,
  fee_percentage: null,
  fee_amount_large: null,
});

const items = reactive<{ id: string, name: string }[]>([]);

const itemList = computed(() => {
  return items.map(item => ({
    value: item.id,
    name: item.name
  }));
});

const merchantId = route.params.merchantId;
const departmentId = route.params.departmentId;

const fetchItem = async () => {
  try {
    const response = await get(`merchants/${merchantId}/departments/${departmentId}/paya`);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const handleSubmit = async () => {
  try {
    await post(`merchants/${merchantId}/departments/${departmentId}/paya`, form);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

const fetchUrls = async () => {
  try {
    const response = await get(`gateways/paya`);
    responseMerge(items, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

onMounted(() => {
  fetchUrls();
  fetchItem();
});
</script>