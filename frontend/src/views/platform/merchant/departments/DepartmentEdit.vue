<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1>Platform Merchant: <span v-text="form.merchant_name"></span> Department</h1>
    <div>
      <form @submit.prevent="handleSubmit">
        <div>
          <FormCheckBox
              id="is_enabled"
              label="Is Enabled"
              v-model="form.is_enabled"
          />
        </div>
        <div v-if="!form.has_assessments">
          <FormCheckBox
              id="is_public"
              label="Can Guest Access"
              v-model="form.is_public"
          />
        </div>
        <div v-if="!form.has_assessments">
          <FormCheckBox
              id="is_assessment"
              label="Turn this department into an assessment"
              v-model="isAssessment"
          />
        </div>
        <div v-if="isAssessment && !form.has_assessments">
          <div>Special Assessments:</div>
          <div>
            <FormTextArea
                id="Description"
                label="Description"
                v-model="form.description"
                :required="true"
                max-length="500"
            />
          </div>
          <div>
            <FormAmountInput
                id="amount"
                label="Amount"
                v-model="form.amount"
                :required="true"
            />
          </div>
          <div>
            <FormSelect
                id="parent_id"
                :options="departmentsList"
                label="Paid To Department"
                placeholder="Choose department to pay to"
                v-model="form.parent_id"
                :required="true"
            />
          </div>
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
              id="slug"
              label="Slug - URL identifier for department."
              v-model="form.slug"
              placeholder="Slug"
              :required="true"
          />
        </div>
        <div>
          <div v-if="selectedIconSvg">
            <ImgSvg v-model="selectedIconSvg"/>
          </div>
          <div>
            <div @click="setIcon = !setIcon">Set Icon</div>
            <div class="row" v-if="setIcon">
              <div class="col-2" v-for="icon in iconsList" :key="icon.id"
                   :class="{'icon-selected': icon.id === form.icon_id}">
                <div @click="form.icon_id = icon.id" v-bind:title="icon.name">
                  <ImgSvg v-model="icon.svg_code"/>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <FormEmailInput
              id="email"
              label="Email For nightly transaction report"
              v-model="form.email"
              placeholder="Email"
          />
        </div>
        <div>
          <FormTextInput
              id="person_name"
              label="Person Name"
              v-model="form.person_name"
              placeholder="Person Name"
          />
        </div>

        <div v-if="!isAssessment">
          <FormCheckBox
              id="is_visible"
              label="Is Visible To Payers"
              v-model="form.is_visible"
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
      v-if="!isAssessment"
  >
    <template #cell-card_connect="{ item }">
      <div @click="openPage('CardConnectEdit')" v-text="item.title"></div>
    </template>
    <template #cell-paya="{ item }">
      <div @click="openPage('PayaEdit')" v-text="item.title"></div>
    </template>
    <template #cell-tyler_legacy="{ item }">
      <div @click="openPage('TylerEdit')" v-text="item.title"></div>
    </template>
    <template #cell-url_query_string="{ item }">
      <div @click="openPage('UrlQueryPayEdit')" v-text="item.title"></div>
    </template>
    <template #cell-smart_pay="{ item }">
      <div @click="openPage('SmartPayEdit')" v-text="item.title"></div>
    </template>
    <template #cell-billings_sub_departments="{ item }">
      <div @click="openPage('SubDepartmentsEdit')" v-text="item.title"></div>
    </template>
    <template #cell-billings_hosted_payments="{ item }">
      <div @click="openPage('HostedPaymentsDepartmentEdit')" v-text="item.title"></div>
    </template>
  </SidebarMenu>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormEmailInput from "@/components/forms/FormEmailInput.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";
import {useRoute, useRouter} from "vue-router";
import {computed, onMounted, reactive, ref, watch} from "vue";
import {get, getApiError, post, responseMerge} from '@/services/api';
import FormTextArea from "@/components/forms/FormTextArea.vue";
import FormAmountInput from "@/components/forms/FormAmountInput.vue";
import FormSelect from "@/components/forms/FormSelect.vue";
import ImgSvg from "@/components/general/ImgSvg.vue";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import SidebarMenu from "@/components/SidebarMenu.vue";

const route = useRoute();
const router = useRouter();

const breadcrumbs = computed(() => {
  return {
    merchantName: form.merchant_name,
    departmentName: form.name,
  }
});

const merchantId = route.params.merchantId;

const isSuccess = ref(false);
const isAssessment = ref(false);
const setIcon = ref(false);

const form = reactive({
  merchant_name: null,
  id: null,
  merchant_id: null,
  name: "",
  email: "",
  slug: "",
  icon_id: "",
  icon: {},
  logo: "",
  person_name: "",
  is_enabled: false,
  is_visible: false,
  is_public: false,
  display_order: 0,
  parent_id: null,
  description: '',
  amount: null,
  has_assessments: false,
});

const departments = ref<{ id: string, name: string }[]>([]);
const icons = ref<{ name: string; svg_code: string; id: string }[]>([]);

const departmentsList = computed(() => {
  return departments.value.map(item => ({
    value: item.id,
    name: item.name
  }));
});
const iconsList = computed(() => {
  return icons.value;
});

const selectedIconSvg = computed(() => {
  const icon = icons.value.find(item => item.id === form.icon_id);
  return icon ? icon.svg_code : null;
});

const fetchedParents = ref(false);
watch(() => isAssessment.value, (newVal) => {
  if (newVal && !fetchedParents.value) {
    fetchParents();
  }
});

const sidebarMenuItems = computed(() => [
  {
    title: 'Payments Gateways',
    children: [
      {key: 'card_connect', title: 'Manage CardConnect'},
      {key: 'paya', title: 'Manage Paya'},
    ]
  },
  {
    title: 'Billings Data Source',
    children: [
      {key: 'tyler_legacy', title: 'Manage Tyler Legacy'},
      {key: 'tyler_at_ent', title: 'Manage Tyler atEnt'},
      {key: 'data_import', title: 'Manage Data Import'},
      {key: 'url_query_string', title: 'Manage URL Query String'},
    ]
  },
  {
    title: 'Invoices Gateways',
    children: [
      {key: 'smart_pay', title: 'Manage SmartPay (DirectStatement)'},
    ]
  },
  {
    title: 'Billings Forms',
    children: [
      {key: 'billings_sub_departments', title: 'Manage Sub Departments'},
      {key: 'billings_hosted_payments', title: 'Manage Hosted Payment Page'},
    ]
  },
]);

// Fetch merchant details when the component is mounted
const fetchItem = async () => {
  const departmentId = route.params.departmentId; // Get the ID from the route
  if (departmentId) {
    try {
      const response = await get(`merchants/${merchantId}/departments/${departmentId}`);
      responseMerge(form, response.data);
      icons.value = response.icons ?? [];
      if (form.parent_id) {
        isAssessment.value = true;
      }
    } catch (error) {
      console.error("Failed to fetch:", error);
    }
  } else {
    setIcon.value = true;
  }
};
const fetchParents = async () => {
  const departmentId = route.params.departmentId; // Get the ID from the route
  if (departmentId) {
    try {
      const response = await get(`merchants/${merchantId}/departments/parents/${departmentId}`);
      departments.value = response.data ?? [];
      fetchedParents.value = true;
    } catch (error) {
      console.error("Failed to fetch:", error);
    }
  }
};


const handleSubmit = async () => {
  isSuccess.value = false;
  try {
    const id = form.id;
    const response = await post(`merchants/${merchantId}/departments/${id || ""}`, form);
    if (!id && response.data.id) {
      await router.replace({
        name: "SystemMerchant",
        params: {merchantId: merchantId},
      });
    } else if (response.isSuccess) {
      isSuccess.value = true;
      if (!form.parent_id) {
        isAssessment.value = false;
      }
    }
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

const openPage = async (name: string) => {
  await router.push({
    name: name,
    params: {merchantId: merchantId}
  });
}

onMounted(fetchItem);
</script>

