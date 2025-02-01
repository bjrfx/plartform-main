<template>
  <div id="paya-form">
    <div>
      <FormTextInput
          id="check_name"
          label="Check Name"
          placeholder="Check Name"
          v-model="form.check_name"
          :required="true"
      />
    </div>
    <div>
      490000018
      <FormTextInput
          id="check_aba"
          label="Check ABA (Routing #)"
          placeholder="#########"
          maxlength="9"
          pattern="\d{9}"
          v-model="form.check_aba"
          :required="true"
      />
    </div>
    <div>
      5007090255
      <FormTextInput
          id="check_account"
          label="Check Account"
          placeholder="#################"
          minlength="4"
          maxlength="17"
          pattern="\d{4,17}"
          v-model="form.check_account"
          :required="true"
      />
    </div>
    <div>
      <FormTextInput
          id="check_account_confirmation"
          label="Repeat Check Account"
          placeholder="#################"
          minlength="4"
          maxlength="17"
          pattern="\d{4,17}"
          v-model="form.check_account_confirmation"
          :required="true"
      />
    </div>
    <div>
      <FormSelect
          id="account_type"
          label="Account Type"
          placeholder="Select Account Type"
          v-model="form.account_type"
          :options="accountTypeList"
          :required="true"
      />
    </div>
    <div>
      <FormSelect
          id="account_holder_type"
          label="Account Holder Type"
          placeholder="Select Holder Type"
          v-model="form.account_holder_type"
          :options="accountHolderTypeList"
          :required="true"
      />
    </div>

    <div v-if="isFormFailed" class="paya-error">
      <span>Please ensure all details are entered correctly.</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import {defineEmits, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormSelect from "@/components/forms/FormSelect.vue";

export interface PayaForm {
  check_name: string,
  check_aba: string,
  check_account: string,
  check_account_confirmation: string,
  account_type: string,
  account_holder_type: string,
}

const emit = defineEmits(['update:modelValue']);

const isFormFailed = ref(false); // Controls error visibility
const hasUserInteracted = ref(false); // Tracks user interaction
let debounceTimeout: NodeJS.Timeout | null = null; // Timeout for debounced validation
const debounceTimerTime = 5000;

const form = ref<PayaForm>({
  check_name: '',
  check_aba: '',
  check_account: '',
  check_account_confirmation: '',
  account_type: '',
  account_holder_type: '',
});

const accountTypeList = ref([
  {value: "CHECKING", name: "Checking", disabled: false},
  {value: "SAVING", name: "Savings", disabled: false},
]);

const accountHolderTypeList = ref([
  {value: "BUSINESS", name: "Business", disabled: false},
  {value: "PERSONAL", name: "Personal", disabled: false},
]);

const validateForm = (): boolean => {
  // If user hasn't interacted yet, do not mark as failed
  //if (!hasUserInteracted.value) return true;

  if (!form.value.check_name.trim()) return false;
  if (!/^\d{9}$/.test(form.value.check_aba)) return false;
  if (!/^\d{4,17}$/.test(form.value.check_account)) return false;
  if (form.value.check_account !== form.value.check_account_confirmation) return false;
  if (!form.value.account_type) return false;
  return !!form.value.account_holder_type;
};

const totalFields = ref<Number>(0);
const countEmptyFields = (): number => {
  return Object.values(form.value).filter((field) => field.trim().length === 0).length;
};
const countTotalFields = (): number => {
  return totalFields.value = Object.keys(form.value).length;
};


// Watch form changes
watch(
    form,
    () => {
      // Check if the user has interacted with the form (any field has a value)
      if (debounceTimeout) {
        clearTimeout(debounceTimeout);
      }
      hasUserInteracted.value = countEmptyFields() > 0;

      if (countEmptyFields().toFixed(0) > totalFields.value.toFixed(0)) {
        totalFields.value = countTotalFields();
      }
      if (countEmptyFields().toFixed(0) < totalFields.value.toFixed(0)) {
        totalFields.value = countTotalFields();
        // Debounce validation to avoid triggering
        debounceTimeout = setInterval(debounceTimer, debounceTimerTime);
      }

      if (validateForm()) {
        emit("update:modelValue", form.value);
      } else {
        emit("update:modelValue", null);
      }
    },
    {deep: true}
);

const debounceTimer = (): void => {
  const currentEmptyCount = countEmptyFields();
  if (currentEmptyCount.toFixed(0) < totalFields.value.toFixed(0)) {
    totalFields.value = currentEmptyCount;
    showError();
  }
};

const showError = (): void => {
  isFormFailed.value = true;
  setTimeout(() => {
    isFormFailed.value = false;
  }, 8000);
};

onMounted(() => {
  totalFields.value = countTotalFields();
});
onBeforeUnmount(() => {
  if (debounceTimeout) {
    clearTimeout(debounceTimeout);
  }
});
</script>