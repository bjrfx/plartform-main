<template>
  <div class="content">
    <BreadcrumbComponent
        :placeholders="breadcrumbs"
    />
    <h1>Gateway - CardConnect API url-<span v-text="form.name"></span></h1>
    <div>
      <form @submit.prevent="handleSubmit">
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
              id="base_url"
              label="API URL"
              v-model="form.base_url"
              placeholder="API URL"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="base_url"
              label="iFrame URL"
              v-model="form.alternate_url"
              placeholder="iFrame URL"
              :required="true"
          />
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
import {useRoute, useRouter} from "vue-router";
import {computed, onMounted, reactive} from "vue";
import {get, post, responseMerge, getApiError} from '@/services/api';
import FormAlerts from "@/components/forms/FormAlerts.vue";


const route = useRoute();
const router = useRouter();

const form = reactive<{ id: string | null, name: string, base_url: string, alternate_url: string | null }>({
  id: null,
  name: "",
  base_url: "",
  alternate_url: null,
});

const breadcrumbs = computed(() => {
  return {
    name: form.name,
  }
});

// Fetch merchant details when the component is mounted
const fetchItem = async () => {
  const gatewayId = route.params.gatewayId;
  if (gatewayId) {
    try {
      const response = await get(`gateways/edit/${gatewayId}`);
      responseMerge(form, response.data);
    } catch (error) {
      console.error("Failed to fetch:", error);
    }
  }
};


const handleSubmit = async () => {
  try {
    const id = form.id;
    const response = await post(`gateways/card-connect/${id || ""}`, form);
    if (!id && response.data.id) {
      await router.replace({
        name: "SystemCardConnectEdit",
        params: {gatewayId: response.data.id},
      });
    }
  } catch (error) {
    console.error("Failed to save:", error);
  }
};

onMounted(fetchItem);
</script>

