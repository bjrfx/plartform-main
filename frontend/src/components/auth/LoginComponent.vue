<template>
  <div class="container login">
    <h1 class="text-center mt-5">Login</h1>
    <form @submit.prevent="handleLogin">
      <div>
        <FormEmailInput
            id="email"
            label="Email"
            v-model="email"
            :required="true"
        />
      </div>
      <div>
        <FormPassword
            id="password"
            label="Password"
            v-model="password"
            :required="true"
        />
      </div>
      <FormAlerts :data="getApiError()"/>
      <!-- Submit -->
      <FormSubmitButton/>
    </form>

  </div>
</template>


<script setup lang="ts">
import {useAuthStore} from '@/stores/auth';
import {useRoute, useRouter} from "vue-router";
import {useSiteDataStore} from "@/stores/siteData";
import FormEmailInput from "@/components/forms/FormEmailInput.vue";
import FormPassword from "@/components/forms/FormPassword.vue";
import {getApiError} from "@/services/interceptors";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import {computed, ref, watch} from "vue";
import {useRoleStore} from "@/stores/roles";


const email = ref('');
const password = ref('');
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const siteDataStore = useSiteDataStore();
const rolesStore = useRoleStore();

const handleLogin = async () => {
  try {
    await authStore.login(email.value, password.value);
    if (authStore.errors.length === 0) {
      const siteDataStore = useSiteDataStore();
      const routeName = siteDataStore.isMerchantSite()
          ? isMerchantRole ? "MerchantDashboard" : "Shop"
          : "SystemDashboard";

      await router.push({name: routeName});
    }
  } catch (err) {
    console.error('Login failed:', err);
  }
};

const isSystemRole = computed(() => {
  const role = siteDataStore.siteData.user?.role
  return role && rolesStore.isSystemRole(role)
});

const isMerchantRole = computed(() => {
  const role = siteDataStore.siteData.user?.role
  return role && rolesStore.isMerchantRole(role)
});


watch(
    () => route.query?.token,
    async (newQuery) => {
      if (isSystemRole && newQuery && newQuery.length > 0) {
        const hasToken = await authStore.fetchToken(String(newQuery));
        if (hasToken) {
          //Reload the site data to make sure it has the user
          await siteDataStore.fetchSiteData();
          //Replace in order to remove the query token from history
          await router.replace({name: "MerchantDashboard", query: {}});
        }
      }
    },
    {immediate: true}
);


</script>