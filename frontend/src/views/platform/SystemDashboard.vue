<template>
  <div class="content">
    <BreadcrumbComponent/>
    <h1>Platform Dashboard</h1>
    <p>Merchants</p>
    <div v-for="merchant in merchants"
         :key="merchant.id"
    >

      <MerchantGalleryComponent :merchant="merchant"/>

    </div>
  </div>

  <SidebarMenu
      title=""
      description=""
      :items="sidebarMenu"
  >
    <template #cell-merchant="{ item }">
      <div @click="openMerchant()" v-text="item.title"></div>
    </template>
    <template #cell-users="{ item }">
      <div @click="openPage('AllUsersList')" v-text="item.title"></div>
    </template>
    <template #cell-hosted_payments_default="{ item }">
      <div @click="openPage('HostedPaymentsEdit')" v-text="item.title"></div>
    </template>
    <template #cell-billing_notification_default="{ item }">
      <div @click="openPage('SystemNotificationBillingEdit')" v-text="item.title"></div>
    </template>
    <template #cell-card_connect="{ item }">
      <div @click="openPage('SystemCardConnectList')" v-text="item.title"></div>
    </template>
    <template #cell-paya_connect="{ item }">
      <div @click="openPage('SystemPayaList')" v-text="item.title"></div>
    </template>
    <template #cell-icons="{ item }">
      <div @click="openPage('AllIconsList')" v-text="item.title"></div>
    </template>
  </SidebarMenu>

</template>

<script setup lang="ts">
import {computed, onMounted, ref} from 'vue';
import {get} from '@/services/api';
import {useRouter} from 'vue-router';
import BreadcrumbComponent from '@/components/BreadcrumbComponent.vue';
import SidebarMenu from "@/components/SidebarMenu.vue";
import MerchantGalleryComponent from "@/components/system/merchants/MerchantGalleryComponent.vue";

// Router instance
const router = useRouter();

// Reactive merchants list with explicit typing
const merchants = ref<{ id: string; name: string, subdomain: string, logo: string | null }[]>([]);

const sidebarMenu = computed(() => [
  {
    title: '',
    children: [{key: "merchant", title: "Add New Merchant"}]
  },
  {
    title: 'All Users',
    children: [{key: "users", title: "Users List"}]
  },
  {
    title: 'Hosted Payments',
    children: [{key: "hosted_payments_default", title: "Hosted Payments Form Default Fields"}],
  },
  {
    title: 'Notifications',
    children: [{key: "billing_notification_default", title: "Billing Notification Default Content"}],
  },
  {
    title: 'Gateways Defaults',
    children: [
      {key: "card_connect", title: "CardConnect API urls"},
      {key: "paya_connect", title: "Paya API urls"}
    ],
  },
  {
    title: 'Icons For Departments',
    children: [{key: "icons", title: "Icons"}],
  },
]);

// Fetch merchants from API
const getMerchants = async () => {
  try {
    const response = await get("merchants");
    merchants.value = response.data || [];
  } catch (error) {
    console.error("Failed to fetch merchants:", error);
  }
};

// Navigate to merchant edit page (new or existing)
const openMerchant = async (id: string | null = null) => {
  await router.push({
    name: 'SystemMerchant',
    params: {merchantId: id},
  });
};

// Navigate to generic page
const openPage = async (name: string) => {
  await router.push({name});
};

// Fetch merchants on component mount
onMounted(getMerchants);
</script>
