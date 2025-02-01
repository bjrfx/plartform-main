<template>
  <div class="content">
    <BreadcrumbComponent :placeholders="breadcrumbs"/>
    <div>
      <KeepAlive :exclude="['HostedPaymentsComponent']">

        <component
            :is="componentMap[department.type] || 'div'"
            :department="department"
            :key="cartKey"
        />
      </KeepAlive>
    </div>

  </div>
  <SidebarCart :key="cartKey"/>
</template>

<script setup lang="ts">
import {computed, onMounted, ref} from "vue";
import {get, responseMerge} from "@/services/api";
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import {useRoute} from "vue-router";

import HostedPaymentsComponent from '@/components/merchant/departments/payments/HostedPaymentsComponent.vue';
import SidebarCart from "@/components/cart/SidebarCart.vue";


// Map of type values to components
const componentMap: Record<string, any> = {
  HOSTED: HostedPaymentsComponent,
  // Add more mappings here if needed
};

const route = useRoute();

const department = ref({
  id: '',
  name: '',
  sub_department_label: '',
  icon: '',
  type: '',
});

const breadcrumbs = computed(() => ({
  department: department.value.name,
}));

const cartKey = ref(Symbol()); // A Symbol in JavaScript is a unique and immutable value


const fetchItem = async () => {
  try {
    const response = await get(`merchants/departments/${route.params.slug}`);
    responseMerge(department.value, response.data); // Merge the response into `department`
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

onMounted(fetchItem);
</script>