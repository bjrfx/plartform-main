<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1><span v-text="form.merchant_name"></span>-<span v-text="form.name"></span>-Manage URL Query String</h1>
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
          <div>Payment Url For Client Usage:</div>
          <CopyToClipboard
              :text="usageURL"
              buttonText="Copy Link"
              :showText="(usageURL && usageURL.length > 0)"
          />
          <div v-if="(!usageURL || usageURL.length === 0)">---</div>
        </div>
        <div>
          <div>
            <FormTextInput
                id="username"
                label="Username"
                v-model="form.client_id"
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
            <FormTextInput
                id="callback_url"
                label="Callback URL"
                v-model="form.callback_url"
                placeholder="Callback URL"
            />
          </div>
          <div>
            <div>URL Query Keys</div>
            <div>
              <FormTextInput
                  id="bill_amount"
                  label="Bill Amount Key"
                  v-model="form.bill_amount"
                  placeholder="Bill Amount Key"
                  :required="true"
              />
            </div>
            <div>
              <FormTextInput
                  id="bill_number"
                  label="Bill Number Key"
                  v-model="form.bill_number"
                  placeholder="Bill Number Key"
                  :required="true"
              />
            </div>
            <div>
              <FormTextInput
                  id="bill_payer_id"
                  label="Bill Payer Id (Reporting)"
                  v-model="form.bill_payer_id"
                  placeholder="Bill Payer Id"
              />
            </div>
            <div>
              <FormTextInput
                  id="product_code"
                  label="Bill Product Code (Reporting)"
                  v-model="form.product_code"
                  placeholder="Bill Product Code"
              />
            </div>
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

import CopyToClipboard from "@/components/general/CopyToClipboard.vue";

import {computed, onMounted, reactive} from "vue";
import {useRoute} from "vue-router";
import {get, getApiError, post, responseMerge} from '@/services/api';
import FormAlerts from "@/components/forms/FormAlerts.vue";


const route = useRoute();

const form = reactive({
  subdomain: null,
  merchant_name: null,
  name: null,
  is_active: true,
  client_id: "",
  password: "",
  callback_url: "",
  bill_amount: "",
  bill_payer_id: "",
  product_code: "",
  bill_number: "",
});

const breadcrumbs = computed(() => {
  return {
    merchantName: form.merchant_name,
    departmentName: form.name,
  }
});

const usageURL = computed(() => {
  const urlQuery = new URLSearchParams();

  let showUrl = true;
  if (form.bill_amount && form.bill_amount.length > 0) {
    urlQuery.append(form.bill_amount, '123.99');
  } else {
    showUrl = false;
  }

  if (form.bill_number && form.bill_number.length > 0) {
    urlQuery.append(form.bill_number, 'XYZ-09876');
  } else {
    showUrl = false;
  }

  if (form.product_code && form.product_code.length > 0) {
    urlQuery.append(form.product_code, "D4E5F6");
  } else {
    showUrl = false;
  }

  if (!showUrl) {
    return null;
  }

  if (form.bill_payer_id && form.bill_payer_id.length > 0) {
    urlQuery.append(form.bill_payer_id, 'ABC-123');
  } else {
    showUrl = false;
  }
  if (!showUrl) {
    return "";
  }
  const queryString = urlQuery.toString()
      .replace(/%7B/g, '{')
      .replace(/%7D/g, '}');

  return `https://${form.subdomain}?${queryString}`;
});


const merchantId = route.params.merchantId;
const departmentId = route.params.departmentId;


const fetchItem = async () => {
  try {
    const response = await get(`merchants/${merchantId}/departments/${departmentId}/url-query-pay`);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const handleSubmit = async () => {
  try {
    await post(`merchants/${merchantId}/departments/${departmentId}/url-query-pay`, form);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};


onMounted(fetchItem);
</script>