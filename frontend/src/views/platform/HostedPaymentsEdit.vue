<template>
  <div class="content">
    <BreadcrumbComponent/>
    <h1>Hosted Payments Form Default Fields</h1>
    <div>Default Fields: <span @click="addCustom()">+ Add</span></div>
    <div v-if="form.default.length > 0">
      <form @submit.prevent="handleSubmit">

        <BasicTable :headers="headers" v-model="form.default" :reorder="true">
          <template #cell-label="{ item, index }">
            <div>
              <FormTextInput
                  :id="'label' + index"
                  label=""
                  v-model="item.label"
                  placeholder="Label"
                  :disabled="item.type === 'DIVIDER'"
              />
            </div>
          </template>
          <template #cell-type="{ item, index }">
            <div>
              <FormSelect
                  :id="'type' + index"
                  label=""
                  v-model="item.type"
                  :options="typeList"
                  :required="true"
              />
            </div>
          </template>
          <template #cell-is_required="{ item, index }">
            <div>
              <FormCheckBox
                  :id="'is_required' + index"
                  label=""
                  v-model="item.is_required"
                  :disabled="item.type === 'DIVIDER'"
              />
            </div>
          </template>
          <template #cell-remove-button="{ index }">
            <div @click="removeCustom(index)">X</div>
          </template>
        </BasicTable>

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

import {onMounted, reactive, ref, watch} from "vue";
import {get, post, responseMerge, getApiError} from "@/services/api";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";
import FormSelect from "@/components/forms/FormSelect.vue";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import BasicTable from "@/components/general/BasicTable.vue";

// Table headers
const headers = [
  {name: 'Label', key: 'label'},
  {name: 'Type', key: 'type'},
  {name: 'Required', key: 'is_required'},
  {name: 'Remove', key: 'remove-button'},
];

interface FormField {
  id: string | null;
  label: string;
  type: string;
  is_required: boolean;
}

const form = reactive({
  default: [] as FormField[],
});

const typeList = ref([
  {value: "TEXT", name: "Text Box", disabled: false},
  {value: "TEXTAREA", name: "Multi-line Text Box", disabled: false},
  {value: "PHONE", name: "Phone Text Box", disabled: false},
  {value: "DIVIDER", name: "Divider", disabled: false},
  {value: "AMOUNT", name: "Amount", disabled: true},
  {value: "REFERENCE", name: "REFERENCE", disabled: true},
])

const ensureAmountFieldExists = () => {
  const hasField = form.default.some((field) => field.type === "AMOUNT");
  if (!hasField) {
    form.default.push({
      id: null,
      label: "Amount",
      type: "AMOUNT",
      is_required: true,
    });
  }
};

const ensureReferenceFieldExists = () => {
  const hasField = form.default.some((field) => field.type === "REFERENCE");
  if (!hasField) {
    form.default.push({
      id: null,
      label: "Account Reference",
      type: "REFERENCE",
      is_required: true,
    });
  }
};

watch(
    () => form.default,
    () => {
      ensureAmountFieldExists();
      ensureReferenceFieldExists();
    },
    {deep: true, immediate: true} // Watch deeply and enforce immediately
);

const fetchItem = async () => {
  try {
    const response = await get(`hosted-payments`);
    responseMerge(form.default, response.data);
  } catch (error) {
    console.error("Failed to fetch :", error);
  }
};

const handleSubmit = async () => {
  try {
    const response = await post(`hosted-payments`, form);
    responseMerge(form.default, response.data);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

const addCustom = () => {
  form.default.push({
    id: null,
    label: '',
    type: '',
    is_required: false
  });
}


const removeCustom = (index: number) => {
  form.default.splice(index, 1);
}
onMounted(fetchItem);
</script>