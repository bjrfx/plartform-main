<template>
  <div class="container registration">
    <h1 class="text-center mt-5">Register</h1>
    <form @submit.prevent="handleSubmit">
      <!-- User Information -->
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
            :required="true"
        />
        <FormPhoneInput
            id="phone"
            label="Phone"
            v-model="form.phone"
            :required="true"
        />
      </div>

      <!-- Address -->
      <div>
        <FormTextInput
            id="street"
            label="Street"
            v-model="form.street"
            :required="true"
        />
      </div>
      <div>
        <FormTextInput
            id="city"
            label="City"
            v-model="form.city"
            :required="true"
        />
      </div>
      <div>
        <FormStateSelect
            id="state"
            label="State"
            v-model="form.state"
            :required="true"
        />
      </div>
      <div>
        <FormZipCodeInput
            id="zip_code"
            label="Zip Code"
            v-model="form.zip_code"
            :required="true"
        />
      </div>
      <div v-if="isBillingEnabledVisible">
        <FormCheckBox
            id="is_ebilling_enabled"
            label="Billing Notifications enabled"
            v-model="form.is_ebilling_enabled"
        />
      </div>

      <!-- Authentication -->
      <div>
        <FormPassword
            label="Password"
            id="password"
            v-model="form.password"
            :required="true"
        />
      </div>
      <div>
        <FormPassword
            label="Confirm Password"
            id="password_confirmation"
            v-model="form.password_confirmation"
            :required="true"
        />
      </div>
      <div>
        <FormPasswordRule
            :password="form.password"
            :confirmPassword="form.password_confirmation"
            :requireConfirmation="true"
            v-model="isPasswordValid"
        />
      </div>

      <FormAlerts :data="getApiError()"/>
      <!-- Submit -->
      <FormSubmitButton
          :disabled="!isPasswordValid"
      />
    </form>
    <div v-if="isSuccess">
      Success registered
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, computed} from 'vue';
import type {RegisterUserData} from '@/stores/auth';
import {useAuthStore} from '@/stores/auth';
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormEmailInput from "@/components/forms/FormEmailInput.vue";
import FormZipCodeInput from "@/components/forms/FormZipCodeInput.vue";
import FormPhoneInput from "@/components/forms/FormPhoneInput.vue";
import FormPhoneCodeInput from "@/components/forms/FormPhoneCodeInput.vue";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";
import FormPassword from "@/components/forms/FormPassword.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import {getApiError} from "@/services/interceptors.js";
import {useSiteDataStore} from "@/stores/siteData";
import FormStateSelect from "@/components/forms/FormStateSelect.vue";
import FormPasswordRule from "@/components/forms/FormPasswordRule.vue";

const authStore = useAuthStore();
const siteDataStore = useSiteDataStore();

const form = ref<RegisterUserData>({
  first_name: '',
  middle_name: null,
  last_name: '',
  email: '',
  city: '',
  street: '',
  state: '',
  zip_code: '',
  phone: '',
  phone_country_code: '',
  is_ebilling_enabled: '',
  password: '',
  password_confirmation: '',
});

const isPasswordValid = ref(false);
const isSuccess = ref(false);

const isBillingEnabledVisible = computed(() => siteDataStore.siteData.is_bulk_notifications_enabled);

const handleSubmit = async () => {
  try {
    const response = await authStore.register(form.value);
    isSuccess.value = response.data?.success;

  } catch (err) {
    console.error('Login failed:', err);
  }
};
</script>
