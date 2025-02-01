<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1><span v-text="form.merchant_name"></span>-<span v-text="form.name"></span>-Sub Department</h1>
    <div>
      <form @submit.prevent="handleSubmit">
        <div>
          <div>
            <FormTextInput
                id="label"
                label="Hosted Payment Form Label"
                v-model="form.label"
                placeholder="Hosted Payment Form Label"
                :required="true"
            />
          </div>
          <div>
            <div>Sub Departments <span @click="addSub()">+ Add Sub Department</span></div>

            <BasicTable :headers="headers" v-model="form.subs">
              <template #cell-name="{ index }">
                <div>
                  <FormTextInput
                      :id="'name' + index"
                      label=""
                      v-model="form.subs[index].name"
                      placeholder="Name"
                      :required="true"
                  />
                </div>
              </template>
              <template #cell-is_active="{ index }">
                <div>
                  <FormCheckBox
                      :id="'is_active' + index"
                      label=""
                      v-model="form.subs[index].is_active"
                  />
                </div>
              </template>
              <template #cell-remove-button="{ index }">
                <div v-if="showRemove(index)" @click="removeSub(index)">X</div>
              </template>
            </BasicTable>

          </div>
        </div>
        <FormAlerts :data="getApiError()"/>
        <FormSubmitButton/>
      </form>
      TO DEFINE - If a sub department had a transaction can it be deleted and can it be rename?
    </div>
  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";

import {computed, onMounted, reactive, watch} from "vue";
import {useRoute} from "vue-router";
import {get, getApiError, post, responseMerge} from '@/services/api';
import FormAlerts from "@/components/forms/FormAlerts.vue";
import BasicTable from "@/components/general/BasicTable.vue";

interface FormSubs {
  id: string | null;
  name: string;
  is_active: boolean;
}

const route = useRoute();

// Table headers
const headers = [
  {name: 'Name', key: 'name'},
  {name: 'Is Active', key: 'is_active'},
  {name: 'Remove', key: 'remove-button'},
];

const form = reactive({
  merchant_name: null,
  name: null,
  label: "",
  subs: [] as FormSubs[],
});

const breadcrumbs = computed(() => {
  return {
    merchantName: form.merchant_name,
    departmentName: form.name,
  }
});

const merchantId = route.params.merchantId;
const departmentId = route.params.departmentId;

// Watch form.subs but avoid triggering immediately after fetch
watch(() => form.subs, (newVal) => {
  if (newVal.length === 0) {
    addSub();
  }
}, {deep: true});

const fetchItem = async () => {
  try {
    const response = await get(`merchants/${merchantId}/departments/${departmentId}/sub-departments`);
    // Merge fetched sub-department data
    responseMerge(form, response.data);

    // After data load, ensure at least one sub exists
    if (form.subs.length === 0) {
      addSub();
    }
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const handleSubmit = async () => {
  try {
    const response = await post(`merchants/${merchantId}/departments/${departmentId}/sub-departments`, form);
    responseMerge(form, response.data);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

const addSub = () => {
  form.subs.push({
    id: null,
    name: '',
    is_active: true
  });
};

const removeSub = (index: number) => {
  form.subs.splice(index, 1);
};

const showRemove = (index: number) => {

  return form.subs[index].id && form.subs[index].id.length > 0;
};

// Fetch data on mount
onMounted(fetchItem);
</script>