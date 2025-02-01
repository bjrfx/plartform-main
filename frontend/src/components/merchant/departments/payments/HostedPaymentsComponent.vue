<template>
  <div>
    <h1>Hosted Payment</h1>
    <form @submit.prevent="handleSubmit" class="form-container">
      <div class="row">
        <div v-if="subDepartmentsList.length > 0" class="col-xl-auto col-lg-12">
          <FormSelect
              class="col-xl-auto col-lg-12"
              id="merchant_id"
              :options="subDepartmentsList"
              v-model="formPayload['sub_department_id']"
              :label="department?.sub_department_label??''"
              placeholder="Select Sub-Department"
              :required="true"
          />
        </div>
        <div v-for="item in formItemsLoop" :key="item.id" :class="itemClass(item.type)">
          <FormTextInput
              v-if="item.type === 'TEXT' || item.type === 'REFERENCE'"
              :id="item.id"
              :label="item.label"
              :placeholder="item.label"
              v-model="formPayload[item.id]"
              :required="item.is_required"
          />
          <FormTextArea
              v-if="item.type === 'TEXTAREA'"
              :id="item.id"
              :label="item.label"
              :placeholder="item.label"
              v-model="formPayload[item.id]"
              :required="item.is_required"
              maxlength="500"
          />
          <FormAmountInput
              v-if="item.type === 'AMOUNT'"
              :id="item.id"
              :label="item.label"
              :placeholder="item.label"
              v-model="formPayload[item.id]"
              :required="item.is_required"
          />
          <div v-if="item.type === 'PHONE'" class="d-flex">
            <FormPhoneCodeInput
                :id="item.id + '_code'"
                v-model="formPayload[item.id + '_code']"
                :required="(item.is_required || (formPayload[item.id] && formPayload[item.id].length > 0))"
            />
            <FormPhoneInput
                :id="item.id"
                :label="item.label"
                v-model="formPayload[item.id]"
                :required="item.is_required"
            />
          </div>
        </div>
      </div>
      <CartExists :show="shopStore.triggerItemExists"/>

      <button type="submit">Add To Cart</button>
    </form>
  </div>
</template>

<script setup lang="ts">

import {get, responseMerge} from "@/services/api";
import {onMounted, defineProps, reactive, computed} from "vue";
import {useShopStore} from '@/stores/shop';
import FormSelect from "@/components/forms/FormSelect.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormTextArea from "@/components/forms/FormTextArea.vue";
import FormAmountInput from "@/components/forms/FormAmountInput.vue";
import FormPhoneInput from "@/components/forms/FormPhoneInput.vue";
import FormPhoneCodeInput from "@/components/forms/FormPhoneCodeInput.vue";
import CartExists from "@/components/cart/CartExists.vue";

const shopStore = useShopStore();

const props = defineProps<{
  department: {
    type?: string;
    id: string;
    name: string;
    icon: string | null;
    sub_department_label: string | null
  }
}>();


// Reactive payload for form data (id => value)
const formPayload = reactive<{ [key: string]: any }>({
  sub_department_id: "",
});

// Define the types
interface FormField {
  id: string;
  default_label?: string | null;
  custom_label?: string | null;
  label?: string;
  type: string;
  is_required: boolean;
}

interface FormSubDepartment {
  id: string;
  name: string;
}

// Initialize the form with the correct type for the structure
const fields = reactive<{
  default: FormField[];
  custom: FormField[];
  sub_departments?: FormSubDepartment[]; // Optional field
}>({
  default: [],
  custom: [],
  sub_departments: [],
});

const subDepartmentsList = computed(() => {
  if (fields.sub_departments && fields.sub_departments.length > 0) {
    return fields.sub_departments.map(sub => ({
      value: sub.id,
      name: sub.name,
    }));
  }
  return [];
});

const formItemsLoop = computed(() => {
  // Map the `default` fields and combine with `custom` fields
  const items = fields.default.map((item) => ({
    ...item, // Include all properties of the original `item`
    label: item.custom_label ?? item.default_label, // Set the `label` dynamically
  }));

  return [...items, ...fields.custom]; // Combine `default` and `custom` fields
});

const itemClass = (type: string) => {
  if (type === 'DIVIDER') {
    return 'w-100'
  }
  if (type === 'TEXTAREA') {
    return 'col-12'
  }
  return 'col-xl-auto col-lg-12';
}

const matchingSubDepartmentName = computed(() => {
  const subDepartmentId = formPayload.sub_department_id;

  if (!subDepartmentId || subDepartmentId.length === 0) {
    return null; // Handle case where sub_department_id is not set
  }

  const match = subDepartmentsList.value.find(
      subDepartment => subDepartment.value === subDepartmentId
  );

  return match ? match.name : null; // Return the name if a match is found, otherwise null
});

const amountValue = computed(() => {
  // Find the field in `fields.default` where type is "AMOUNT"
  const amountField = fields.default.find(field => field.type === "AMOUNT");

  // If the field is found, get its `id` and use it to retrieve the value from `formPayload`
  return amountField ? formPayload[amountField.id] : null;
});

const referenceValue = computed(() => {
  // Find the field in `fields.default` where type is "AMOUNT"
  const amountField = fields.default.find(field => field.type === "REFERENCE");

  // If the field is found, get its `id` and use it to retrieve the value from `formPayload`
  return amountField ? String(formPayload[amountField.id]) : '';
});

const handleSubmit = async () => {
  try {
    shopStore.addToCart({
      department_id: String(props.department?.id),
      department_name: String(props.department?.name),
      department_icon: String(props.department?.icon),
      sub_department_id: formPayload['sub_department_id'],
      sub_department_name: matchingSubDepartmentName.value,
      account_reference: referenceValue.value,
      amount: amountValue.value,
      form_payload: formPayload,
    })

  } catch (error) {
    console.error("Failed Form:", error);
  }
};
const fetchItem = async () => {
  if (props.department?.id) {
    try {
      const response = await get(`merchants/departments/${props.department.id}/hosted`);
      // Merge fetched sub-department data
      responseMerge(fields, response.data);

    } catch (error) {
      console.error("Failed to fetch:", error);
    }
  }
};

onMounted(fetchItem);
</script>