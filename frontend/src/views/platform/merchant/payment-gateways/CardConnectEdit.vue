<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1><span v-text="form.merchant_name"></span>-<span v-text="form.name"></span>-CardConnect</h1>
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
          <div>Merchant:</div>
          <div>
            <FormTextInput
                id="merchant_mid"
                label="MID"
                v-model="form.merchant_mid"
                placeholder="MID"
                :required="true"
            />
          </div>
          <div>
            <FormTextInput
                id="merchant_username"
                label="Username"
                v-model="form.merchant_username"
                placeholder="Username"
                :required="true"
            />
          </div>
          <div>
            <FormTextInput
                id="merchant_password"
                label="Password"
                v-model="form.merchant_password"
                placeholder="Password"
                :required="true"
            />
          </div>
        </div>
        <div>
          <div>Service Fee:</div>
          <div>
            <FormTextInput
                id="fee_mid"
                label="MID"
                v-model="form.fee_mid"
                placeholder="MID"
                :required="true"
            />
          </div>
          <div>
            <FormTextInput
                id="fee_username"
                label="Username"
                v-model="form.fee_username"
                placeholder="Username"
                :required="true"
            />
          </div>
          <div>
            <FormTextInput
                id="fee_password"
                label="Password"
                v-model="form.fee_password"
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
            <FormCheckBox
                id="has_same_fee"
                label="Apply The Same Fee For Credit And Debit Cards"
                v-model="form.has_same_fee"
            />
          </div>
        </div>

        <div v-if="form.has_same_fee">
          <div>Credit and Debit Cards Convenience Fee</div>
          <div>
            <FormAmountInput
                id="credit_card_min"
                label="Card Min $"
                v-model="form.credit_card_min"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="credit_card_amount"
                label="Card $"
                v-model="form.credit_card_amount"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="credit_card_percentage"
                label="Card %"
                v-model="form.credit_card_percentage"
                placeholder="#.##"
                :required="false"
            />
          </div>
        </div>

        <div v-if="!form.has_same_fee">
          <div>Credit Card Convenience Fee</div>
          <div>
            <FormAmountInput
                id="credit_card_min"
                label="Credit Card Min $"
                v-model="form.credit_card_min"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="credit_card_amount"
                label="Credit Card $"
                v-model="form.credit_card_amount"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="credit_card_percentage"
                label="Credit Card %"
                v-model="form.credit_card_percentage"
                placeholder="#.##"
                :required="false"
            />
          </div>

        </div>
        <div v-if="!form.has_same_fee">
          <div>Debit Card Convenience Fee</div>
          <div>
            <FormAmountInput
                id="debit_card_min"
                label="Debit Card Min $"
                v-model="form.debit_card_min"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="debit_card_amount"
                label="Debit Card $"
                v-model="form.debit_card_amount"
                placeholder="#.##"
                :required="false"
            />
          </div>
          <div>
            <FormAmountInput
                id="debit_card_percentage"
                label="Debit Card %"
                v-model="form.debit_card_percentage"
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
import FormSelect from "@/components/forms/FormSelect.vue";
import FormAmountInput from "@/components/forms/FormAmountInput.vue";


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
  merchant_mid: "",
  merchant_username: "",
  merchant_password: "",
  fee_mid: "",
  fee_username: "",
  fee_password: "",
  gateway_id: "",
  has_same_fee: false,
  credit_card_min: null,
  credit_card_amount: null,
  credit_card_percentage: null,
  debit_card_min: null,
  debit_card_amount: null,
  debit_card_percentage: null,
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
    const response = await get(`merchants/${merchantId}/departments/${departmentId}/card-connect`);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const fetchUrls = async () => {
  try {
    const response = await get(`gateways/card-connect`);
    responseMerge(items, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

onMounted(() => {
  fetchUrls();
  fetchItem();
});

const handleSubmit = async () => {
  try {
    await post(`merchants/${merchantId}/departments/${departmentId}/card-connect`, form);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

</script>