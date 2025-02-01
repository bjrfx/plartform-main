<template>
  <div class="merchant-gallery">
    <div class="merchant-gallery-logo">
      <img :alt="merchant.name" v-if="merchant.logo" :src="merchant.logo">
    </div>
    <div class="merchant-gallery-name" v-text="merchant.name"></div>
    <div class="merchant-gallery-actions">
      <div @click="redirect()">Visit</div>
      <div v-if="showEdit" @click="edit()">Edit</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, PropType} from 'vue';
import {useAuthStore} from '@/stores/auth.js';
import {useSiteDataStore} from "@/stores/siteData";
import {useRouter} from "vue-router";
import {useRoleStore} from "@/stores/roles";

const authStore = useAuthStore();
const siteDataStore = useSiteDataStore();
const router = useRouter();
const rolesStore = useRoleStore();

interface Merchant {
  id: string;
  name: string;
  subdomain: string;
  logo: string | null;
}


const props = defineProps({
  merchant: {
    type: Object as PropType<Merchant>,
    required: true,
  },
});


const showEdit = computed(() => {
  return !siteDataStore.isMerchantSite() && token.value && token.value.length > 0;
});

const token = computed(() => {
  if (authStore.token && authStore.isAuth()) {
    const role = authStore.getUserRoll();

    if (role && !rolesStore.isSystemRole(role)) {
      return null;
    }
  }
  return authStore.token;
});


const subdomainUrl = computed(() => {
  const domain = siteDataStore.siteData?.platform_domain;
  const subdomain = props.merchant?.subdomain;

  // Validate domain and subdomain
  if (!domain || !subdomain) {
    console.error('Platform domain or merchant subdomain is missing.');
    return null;
  }

  let http = 'https';
  if (import.meta.env.VITE_APP_ENV === 'local') {
    http = 'http';
  }

  // Construct the subdomain URL
  return `${http}://${subdomain}.${domain}`;
});

const edit = async () => {
  await router.push({
    name: 'SystemMerchant',
    params: {merchantId: props.merchant.id},
  });
};

const redirect = async () => {
  if (!subdomainUrl.value) {
    return;
  }

  window.open(subdomainUrl.value + '?token=' + token.value, '_blank');
};


</script>