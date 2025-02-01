<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1><span v-text="form.merchant_name"></span>-<span v-text="form.name"></span>-Tyler</h1>
    <div>
      <form @submit.prevent="handleSubmit">
        <div>
          <FormCheckBox
              id="is_active"
              label="Is Active"
              v-model="form.is_active"
          />
        </div>
        <div>
          <div>
            <FormTextInput
                id="username"
                label="Username"
                v-model="form.username"
                placeholder="Username"
                :required="true"
            />
          </div>
          <div>
            <FormTextInput
                id="password"
                label="Password"
                v-model="form.password"
                placeholder="Password"
                :required="true"
            />
          </div>
          <div>
            <FormTextInput
                id="jur"
                label="JUR"
                v-model="form.jur"
                placeholder="JUR"
                :required="true"
            />
          </div>
          <div>
            <FormSelect
                id="flag_of_current_due"
                label="Flag of Current Due"
                v-model="form.flag_of_current_due"
                :options="flagsCurrentDue"
                placeholder="Flag of Current Due"
                :required="true"
            />
          </div>
          <div>
            <div>Tax Year Cycles (mm/dd/yyyy)</div>
            <div @click="addCycle()">Add Cycle</div>
            <div v-for="(cycle_due, index) in form.cycle_dues" :key="index">
              {{ void cycle_due }}
              <FormDateInput
                  :id="'cycle_due' + index"
                  :label="'Cycle Due ' + (index + 1)"
                  v-model="form.cycle_dues[index]"
                  placeholder="Cycle Due"
                  :required="true"
              />
              <div @click="removeCycle(index)">X</div>
            </div>
          </div>
          <div>
            <FormTextInput
                id="custom_url"
                label="API URL"
                v-model="form.custom_url"
                placeholder="API URL"
                :required="true"
            />
          </div>
          <div>
            <div>Tyler Restrictions</div>
            <div>When certain information in the system matches specific values, restrictions are applied if the
              ‘Enable Pay’ option is turned off.
              <br>
              In this case, users will see an ‘Info’ button with a message explaining why payment is not available,
              instead of the ‘Pay’ button. If the ‘Enable Pay’ option is
              turned on, the ‘Pay’ button will appear, allowing users to proceed with payment.
            </div>
            <div @click="addRestriction()">+ Add Restriction</div>

            <BasicTable :headers="headers" v-model="form.restriction">
              <template #cell-key="{ index }">
                <td>
                  <FormTextInput
                      :id="'key'+index"
                      label=""
                      v-model="form.restriction[index].key"
                      placeholder="Key"
                      :required="true"
                  />
                </td>
              </template>
              <template #cell-values="{ index }">
                <td>
                  <FormTextArea
                      :id="'values'+index"
                      label=""
                      v-model="form.restriction[index].values"
                      placeholder="Values"
                      :required="true"
                      maxlength="255"
                  />
                </td>
              </template>
              <template #cell-disabled_alert="{ index }">
                <td>
                  <FormTextArea
                      :id="'disabled_alert'+index"
                      label=""
                      v-model="form.restriction[index].disabled_alert  as string | undefined"
                      placeholder="Disabled Alert"
                      maxlength="255"
                  />
                </td>
              </template>
              <template #cell-enabled="{ index }">
                <td>
                  <FormSelect
                      :id="'enabled'+index"
                      label=""
                      v-model="form.restriction[index].enabled as string"
                      placeholder="Select Action"
                      :options="RestrictionsEnable"
                      :required="true"
                  />
                </td>
              </template>
              <template #cell-remove-button="{ index }">
                <div @click="removeRestriction(index)">X</div>
              </template>
            </BasicTable>

          </div>
        </div>
        <FormAlerts :data="getApiError()"/>
        <FormSubmitButton/>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormCheckBox from "@/components/forms/FormCheckBox.vue";
import FormDateInput from "@/components/forms/FormDateInput.vue";

import {computed, onMounted, reactive, ref, watch} from "vue";
import {useRoute} from "vue-router";
import {get, getApiError, post, responseMerge} from '@/services/api';
import FormSelect from "@/components/forms/FormSelect.vue";
import FormAlerts from "@/components/forms/FormAlerts.vue";
import FormTextArea from "@/components/forms/FormTextArea.vue";
import BasicTable from "@/components/general/BasicTable.vue";


const route = useRoute();

// Table headers
const headers = [
  {name: 'Key', key: 'key'},
  {name: 'Values (Comma seperated)', key: 'values'},
  {name: 'Disabled Alert', key: 'disabled_alert'},
  {name: 'Action', key: 'enabled'},
  {name: 'Remove', key: 'remove-button'},
];


const form = reactive({
  merchant_name: null,
  name: null,
  api: "",
  is_active: true,
  username: "",
  password: "",
  custom_url: "",
  jur: "",
  flag_of_current_due: "",
  cycle_dues: [] as (string | null)[],
  restriction: [] as {
    key: string;
    values: string;
    enabled: boolean | null | string;
    disabled_alert: string | null | undefined
  }[],
});


const breadcrumbs = computed(() => {
  return {
    merchantName: form.merchant_name,
    departmentName: form.name,
  }
});

const flagsCurrentDue = ref([
  {value: "FLAG1", name: "FLAG1"},
  {value: "FLAG2", name: "FLAG2"},
  {value: "FLAG3", name: "FLAG3"},
  {value: "FLAG4", name: "FLAG4"},
  {value: "FLAG5", name: "FLAG5"},
  {value: "FLAG6", name: "FLAG6"},
  {value: "FLAG7", name: "FLAG7"},
])

const RestrictionsEnable = ref([
  {value: 1, name: 'Enable Pay'},
  {value: 0, name: 'Prevent Pay'},
]);

const merchantId = route.params.merchantId;
const departmentId = route.params.departmentId;

watch(() => form.cycle_dues, (newVal) => {
  if (newVal.length === 0) {
    //force one cycle
    addCycle();
  }
});

watch(
    () => form.restriction,
    (newVal) => {
      newVal.forEach((item) => {
        // If enabled is true, set it to "1", otherwise set it to "0"
        item.enabled = item.enabled === true ? "1" : "0";
      });
    },
    {immediate: true, deep: true}
);

const fetchItem = async () => {
  try {
    const response = await get(`merchants/${merchantId}/departments/${departmentId}/tyler`);
    responseMerge(form, response.data);

    if (form.cycle_dues.length === 0) {
      addCycle();
    }
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const handleSubmit = async () => {
  try {
    await post(`merchants/${merchantId}/departments/${departmentId}/tyler`, form);
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

const addCycle = () => {
  form.cycle_dues.push(null);
}
const removeCycle = (index: number) => {
  form.cycle_dues.splice(index, 1);
}
const addRestriction = () => {
  form.restriction.push({key: "", values: "", enabled: '0', disabled_alert: ""});
}
const removeRestriction = (index: number) => {
  form.restriction.splice(index, 1);
}


onMounted(fetchItem);
</script>