<template>
  <div class="form-group">
    <div>
      <div>Placeholders:</div>
      <ul class="list-group list-group-horizontal placeholder-item">
        <li class="list-group-item" v-for="placeholder in placeholders" :key="placeholder.key"
            @click="addPlaceholder(placeholder.code)">
          <div class="btn btn-link" v-text="placeholder.title"></div>
        </li>
      </ul>
    </div>
    <div>Message Body:</div>
    <div class="editor-container">
      <QuillEditor v-model="formattedValue" :disabled="false" :append="appendPlaceholder"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, watch} from "vue";
import QuillEditor from "@/components/forms/QuillEditor.vue";

// Define props with correct typing and default values
const props = defineProps<{
  modelValue: string | null;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void;
}>();

// Initialize ref for the editor's content
const formattedValue = ref<string>(props.modelValue ?? "");
const appendPlaceholder = ref<string | null>(null);

// Placeholder data for dropdown insertion
const placeholders = ref([
  {key: "userName", code: "%_USER_NAME_%", title: "User Name"},
  {key: "referenceNumber", code: "%_REFERENCE_NUMBER_%", title: "Reference Number"},
  {key: "billDate", code: "%_BILL_DATE_%", title: "Bill Date"},
  {key: "amountDue", code: "%_AMOUNT_DUE_%", title: "Amount Due"},
  {key: "dueDate", code: "%_DUE_DATE_%", title: "Due Date"},
  {key: "merchantName", code: "%_MERCHANT_NAME_%", title: "Merchant Name"},
  {key: "merchantPhone", code: "%_MERCHANT_PHONE_%", title: "Merchant Phone"},
  {key: "merchantAddress", code: "%_MERCHANT_ADDRESS_%", title: "Merchant Address"},
  {key: "departmentName", code: "%_DEPARTMENT_NAME_%", title: "Department Name"},
  {key: "departmentPaymentUrl", code: "%_DEPARTMENT_PAYMENT_URL_%", title: "Department Payment URL"},
]);

// Function to add placeholder text into the editor
const addPlaceholder = (code: string) => {
  appendPlaceholder.value = code;
};

// Watch for changes in props to sync with ref value
watch(
    () => props.modelValue,
    (newValue) => {
      formattedValue.value = newValue ?? "";
    },
    {immediate: true}
);

// Emit updated value when the editor content changes
watch(formattedValue, (newValue) => {
  emit("update:modelValue", newValue);
});
</script>

<style scoped>
.form-group {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.placeholder-item {
  cursor: pointer;
  padding: 0.25rem 0;
}

.editor-container {
  margin-top: 1rem;
}
</style>