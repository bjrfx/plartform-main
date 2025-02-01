<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1><span v-text="form.merchant_name"></span>-<span v-text="form.name"></span>-Hosted Payment Page</h1>
    <div>
      <form @submit.prevent="handleSubmit">
        To-Do: some departments are based on uploaded data file, as example AgingAccount, PermitAccount files upload
        <div>
          <div>Default Fields:</div>

          <BasicTable :headers="headersDefault" v-model="form.default">
            <template #cell-custom_label="{ item }">
              <div>
                <FormTextInput
                    :id="'default_label' + item.id"
                    label=""
                    v-model="item.custom_label"
                    :placeholder="'Override Label: ' + item.default_label"
                />
              </div>
            </template>
          </BasicTable>

          <div>
            <div>Custom Fields: <span @click="addCustom()">+ Add</span></div>

            <BasicTable :headers="headersCustom" v-model="form.custom" :reorder="true">
              <template #cell-label="{ item, index }">
                <div>
                  <FormTextInput
                      :id="'custom_label' + index"
                      label=""
                      v-model="item.label"
                      placeholder="Label"
                      :required="true"
                  />
                </div>
              </template>
              <template #cell-remove-button="{ index }">
                <div @click="removeCustom(index)">Remove</div>
              </template>
            </BasicTable>

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

import {computed, onMounted, reactive} from "vue";
import {useRoute} from "vue-router";
import {get, getApiError, post, responseMerge} from '@/services/api';
import FormAlerts from "@/components/forms/FormAlerts.vue";
import BasicTable from "@/components/general/BasicTable.vue";

const route = useRoute();

// Table headers
const headersDefault = [
  {name: 'Default Label', key: 'default_label'},
  {name: 'Override Label', key: 'custom_label'},
];
// Table headers
const headersCustom = [
  {name: 'Label', key: 'label'},
  {name: 'Remove', key: 'remove-button'},
];


const form = reactive({
  merchant_name: '',
  name: '',
  default: [] as { id: string, default_label: string, custom_label: string }[],
  custom: [] as { label: string, is_required: boolean }[],
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
    const response = await get(`merchants/${merchantId}/departments/${departmentId}/hosted-payments`);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const handleSubmit = async () => {
  try {
    const response = await post(`merchants/${merchantId}/departments/${departmentId}/hosted-payments`, form);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

const addCustom = () => {
  form.custom.push({
    label: '',
    is_required: false
  });
}
const removeCustom = (index: number) => {
  form.custom.splice(index, 1);
}

onMounted(fetchItem);
</script>