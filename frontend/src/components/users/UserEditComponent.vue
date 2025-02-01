<template>
  <div class="container registration">
    <form @submit.prevent="handleSubmit">
      <!-- User Information -->
      <div v-if="!isMember">
        <FormCheckBox
            id="is_enabled"
            label="Enabled"
            v-model="form.is_enabled"
        />
      </div>
      <div v-if="isSystemForm">
        <FormSelect
            id="merchant_id"
            :options="merchantsList"
            v-model="form.merchant_id"
            label="Merchant"
            :required="true"
        />
      </div>
      <div v-if="!isMember">
        <FormRoleSelect
            id="role"
            label="Role"
            v-model="form.role"/>
      </div>

      <div v-if="departmentsList.length > 0" class="form-group">
        <label>
          Select a Department to Allow Access:
        </label>
        <ul>
          <li v-for="department in departmentsList" :key="department.id">
            <label>
              <input
                  :id="'department' + department.id"
                  type="checkbox"
                  v-model="form.department_ids"
                  :value="department.id"
              >
              <span v-text="department.name"></span>
            </label>
          </li>
        </ul>
      </div>

      <div>
        <FormTextInput
            id="first_name"
            label="First Name"
            v-model="form.first_name"
            :required="true"
        />
      </div>
      <div>
        <FormTextInput
            id="middle_name"
            label="Middle Name"
            v-model="form.middle_name"
        />
      </div>
      <div>
        <FormTextInput
            id="last_name"
            label="Last Name"
            v-model="form.last_name"
            :required="true"
        />
      </div>

      <!-- Contact Information -->
      <div>
        <FormEmailInput
            id="email"
            label="Email"
            v-model="form.email"
            :required="true"
        />
      </div>
      <div>
        <FormPhoneCodeInput
            id="phone_country_code"
            v-model="form.phone_country_code"
            :required="isRequired"
        />
        <FormPhoneInput
            id="phone"
            label="Phone"
            v-model="form.phone"
            :required="isRequired"
        />
      </div>

      <div>
        <FormTextInput
            id="street"
            label="Street"
            v-model="form.street"
            :required="isRequired"
        />
      </div>
      <div>
        <FormTextInput
            id="city"
            label="City"
            v-model="form.city"
            :required="isRequired"
        />
      </div>
      <div>
        <FormStateSelect
            id="state"
            v-model="form.state"
            label="State"
            placeholder="Select State"
            :required="isRequired"
        />
      </div>
      <div>
        <FormZipCodeInput
            id="zip_code"
            label="Zip Code"
            v-model="form.zip_code"
            :required="isRequired"
        />
      </div>
      <div v-if="isBillingEnabledVisible">
        <FormCheckBox
            id="is_ebilling_enabled"
            label="Billing Notifications enabled"
            v-model="form.is_ebilling_enabled"
        />
      </div>
      <div v-if="!isMember">
        <FormCheckBox
            id="is_card_payment_only"
            label="Restricted to Card Payments only"
            v-model="form.is_card_payment_only"
        />
      </div>

      <!-- Authentication -->
      <div v-if="!isMember">
        <FormPassword
            label="Password"
            id="password"
            v-model="form.password"
        />
      </div>
      <div v-if="showPasswordRules">
        <FormPasswordRule
            :password="form.password"
            :requireConfirmation="false"
            v-model="isPasswordValid"
        />
      </div>

      <FormAlerts :data="getApiError()"/>
      <!-- Submit -->
      <FormSubmitButton
          :disabled="disableSubmit"
      />
    </form>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from 'vue';
import {useAuthStore} from '@/stores/auth';
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormEmailInput from "@/components/forms/FormEmailInput.vue";
import FormSelect from "@/components/forms/FormSelect.vue";
import FormZipCodeInput from "@/components/forms/FormZipCodeInput.vue";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";
import FormPhoneCodeInput from "@/components/forms/FormPhoneCodeInput.vue";
import FormPhoneInput from "@/components/forms/FormPhoneInput.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormRoleSelect from "@/components/forms/FormRoleSelect.vue";
import FormPassword from "@/components/forms/FormPassword.vue";
import {get, getApiError} from "@/services/api";
import {useSiteDataStore} from "@/stores/siteData";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import FormStateSelect from "@/components/forms/FormStateSelect.vue";
import FormPasswordRule from "@/components/forms/FormPasswordRule.vue";
import {UserRole} from "@/enums/enums";

const siteDataStore = useSiteDataStore();
const authStore = useAuthStore();

// Define form structure
export interface FormUser {
  merchant_id: string | null;
  first_name: string;
  middle_name: string | null;
  last_name: string;
  id: string | null;
  email: string;
  name: string;
  city: string | null;
  street: string | null;
  state: string;
  zip_code: string | null;
  phone: string | null;
  phone_country_code: string | null;
  is_ebilling_enabled: boolean | null;
  is_card_payment_only: boolean | null;
  is_enabled: boolean | null;
  password: string | null;
  role: string | null;
  department_ids?: string[];
}

// Define props
const props = defineProps<{
  form: FormUser;
}>();

// Emit submit event
const emit = defineEmits<{
  (e: 'submit', form: FormUser): void;
}>();

const isPasswordValid = ref(false);

const handleSubmit = () => {
  emit('submit', {...props.form});
};

const disableSubmit = computed(() => String(props.form.password).length > 0 && !isPasswordValid);

const isMember = computed(() => authStore.getUserRoll() === UserRole.SUPPORT);
const isRequired = computed(() => props.form.role === "member");
const isSystemForm = computed(() => !siteDataStore.siteData.id);
const showPasswordRules = computed(() => {
  return !isMember.value && props.form.password ? props.form.password.length : 0;
});

// Declare getMerchants function before the watch
const merchants = ref<{ id: string; name: string; is_bulk_notifications_enabled: boolean }[]>([]);
const departments = ref<{ id: string; name: string; }[]>([]);

const getMerchants = async () => {
  try {
    const response = await get(`merchants`);
    merchants.value = response.data || [];
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};
const getDepartments = async (merchantId: string) => {
  if (props.form.role === 'MERCHANT_STAFF') {
    try {
      const response = await get(`merchants/${merchantId}/departments`);
      departments.value = response.data || [];
    } catch (error) {
      console.error("Failed to fetch:", error);
    }
  }
};

// Watch for changes to isSystemForm
watch(
    () => isSystemForm,
    (newValue) => {
      if (newValue) {
        getMerchants(); // Now this will work because getMerchants is declared before the watch
      }
    },
    {immediate: true}
);

const systemRoles = ["SYSTEM_ADMIN", "ADMIN", "SUPPORT"];

// Define merchantsList as a computed property based on merchants
const merchantsList = computed(() => {
  let list = [];
  if (props.form.role && systemRoles.includes(props.form.role)) {
    list.push({
      value: "null",
      name: "*System*",
    })
  }
  merchants.value.map(merchant => (list.push({
    value: merchant.id,
    name: String(merchant.name),
  })));

  return list;
});

const departmentsList = computed(() => {
  return departments.value;
});

// Watch for changes in the merchant_id from props and when merchants are loaded
const formIsBulkNotificationsEnabled = ref(true); // Default to true

watch(
    () => props.form.merchant_id,
    (newMerchantId) => {
      if (newMerchantId && merchants.value.length > 0 && newMerchantId !== "null") {
        // Find the selected merchant and update visibility
        const selectedMerchant = merchants.value.find(merchant => merchant.id === newMerchantId);
        if (selectedMerchant && "is_bulk_notifications_enabled" in selectedMerchant) {
          formIsBulkNotificationsEnabled.value = Boolean(selectedMerchant.is_bulk_notifications_enabled);
        }
        getDepartments(newMerchantId);
      }
    },
    {immediate: true}
);


watch(
    () => props.form.role,
    () => {
      const valMerchantId = props.form.merchant_id;
      if (valMerchantId && valMerchantId.length > 0 && valMerchantId !== "null") {
        getDepartments(valMerchantId);
      }
    },
    {immediate: true, deep: true}
);

// Computed to determine whether the checkbox should be visible
const isBillingEnabledVisible = computed(() => formIsBulkNotificationsEnabled.value);
</script>
<style>
</style>