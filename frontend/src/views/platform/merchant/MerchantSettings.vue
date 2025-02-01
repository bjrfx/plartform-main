<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1>Platform Merchant <span v-text="form.name"></span></h1>

    <div>
      <form @submit.prevent="handleSubmit">
        <div>
          <FormCheckBox
              id="is_enabled"
              label="is_enabled"
              v-model="form.is_enabled"
          />
        </div>
        <div>
          <FormTextInput
              id="name"
              label="Name"
              v-model="form.name"
              placeholder="Name"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="subdomain"
              label="subdomain"
              v-model="form.subdomain"
              placeholder="subdomain"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="address"
              label="address"
              v-model="form.address"
              placeholder="address"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="city"
              label="city"
              v-model="form.city"
              placeholder="city"
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
              id="zip"
              label="zip code"
              v-model="form.zip"
              :required="true"
          />
        </div>
        <div>
          <FormPhoneCodeInput
              id="phone_code"
              label="Phone code"
              v-model="form.phone_code"
              :required="true"
          />
          <FormPhoneInput
              id="phone"
              label="Phone"
              v-model="form.phone"
              :required="true"
          />
        </div>
        <div>
          <FormPhoneCodeInput
              id="fax_code"
              label="fax code"
              v-model="form.fax_code"
              :required="isFaxCodeRequired"
          />
          <FormPhoneInput
              id="fax"
              label="fax Number"
              v-model="form.fax"
              :required="isFaxRequired"
          />
        </div>
        <div>
          <FormTimeZoneSelect
              id="time_zone"
              label="time zone"
              v-model="form.time_zone"
              :required="true"
          />
        </div>
        <div>
          <FormCheckBox
              id="is_bulk_notifications_enabled"
              label="is_bulk_notifications_enabled"
              v-model="form.is_bulk_notifications_enabled"
          />
        </div>
        <div>
          <FormCheckBox
              id="is_payment_service_disabled"
              label="is_payment_service_disabled"
              v-model="form.is_payment_service_disabled"
          />
        </div>
        <FormAlerts :data="getApiError()"/>
        <FormSubmitButton/>
      </form>
    </div>

  </div>


  <SidebarMenu
      title=""
      description=""
      :items="sidebarMenuItems"
  >
    <template #cell-new_department="{ item }">
      <div @click="openDepartment()" v-text="item.title"></div>
    </template>

    <template v-for="department in departments" :key="department.id" v-slot:[`cell-${department.id}`]="{ item }">
      <div @click="openDepartment(department.id)">
        <ImgSvg v-if="item.icon" v-model="item.icon"/>
        <span v-text="item.title"></span>
      </div>
    </template>

  </SidebarMenu>
</template>

<script setup lang="ts">
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {get, getApiError, post, responseMerge} from '@/services/api';
import {useRoute, useRouter} from "vue-router";
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormPhoneCodeInput from "@/components/forms/FormPhoneCodeInput.vue";
import FormZipCodeInput from "@/components/forms/FormZipCodeInput.vue";
import FormStateSelect from "@/components/forms/FormStateSelect.vue";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";
import FormPhoneInput from "@/components/forms/FormPhoneInput.vue";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import FormTimeZoneSelect from "@/components/forms/FormTimeZoneSelect.vue";
import type {SidebarChildItem} from "@/components/SidebarMenu.vue";
import SidebarMenu from "@/components/SidebarMenu.vue";
import ImgSvg from "@/components/general/ImgSvg.vue";

const route = useRoute();
const router = useRouter();

const merchantId = route.params.merchantId; // Get the ID from the route

const form = reactive({
  id: null as string | null,
  name: "",
  subdomain: "",
  address: "",
  city: "",
  state: "",
  zip: "",
  phone: "",
  phone_code: "",
  fax: "",
  fax_code: "",
  logo: "",
  time_zone: "",
  is_enabled: false,
  is_bulk_notifications_enabled: false,
  is_payment_service_disabled: false,
  departments: [] as { id: string; name: string, icon: string | null }[],  // Explicitly type the departments array
});

const breadcrumbs = computed(() => {
  return {
    merchantName: form.name,
  }
});


const sideMenu = ref<SidebarChildItem[]>([
  {key: 'new_department', title: 'Add New Department', icon: null}
]);


// Initialize departments after fetching
// Initialize departments after fetching
watch(
    () => form.departments,
    (newDepartments) => {
      if (newDepartments?.length) {
        newDepartments.forEach((item) => {
          sideMenu.value.push({
            key: item.id,
            title: item.name,
            icon: item.icon,
          });
        });
      }
    },
    {immediate: true}
);

const departments = computed(() => form.departments ?? [])
const sidebarMenuItems = computed(() => [
  {
    title: '',
    children: sideMenu.value
  },
]);

const openDepartment = async (id: string | null = null) => {
  await router.push({
    name: "DepartmentEdit",
    params: {merchantId: merchantId, departmentId: id}
  });
}


const fetchMerchant = async () => {
  if (merchantId) {
    try {
      const response = await get(`merchants/${merchantId}`);
      responseMerge(form, response.data);
    } catch (error) {
      console.error("Failed to fetch:", error);
    }
  }
};


const handleSubmit = async () => {
  try {
    const id = form.id;
    const response = await post(`merchants/${id || ""}`, form);
    if (!id && response.data.id) {
      await router.replace({
        name: "SystemMerchant",
        params: {merchantId: response.data.id},
      });
    }
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

const isFaxRequired = computed(() => {
  if (form.fax_code) {
    return String(form.fax_code).length > 0;
  }
  return false;
});
const isFaxCodeRequired = computed(() => {
  if (form.fax) {
    return String(form.fax).length > 0;
  }
  return false;
});

onMounted(fetchMerchant);
</script>