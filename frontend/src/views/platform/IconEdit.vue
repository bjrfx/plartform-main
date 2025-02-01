<template>
  <div class="content">
    <BreadcrumbComponent/>
    <h1>
      Icon
      <span v-if="form.svg_code">
          <ImgSvg v-model="form.svg_code"/>
        </span>
    </h1>
    <form @submit.prevent="handleSubmit" class="form-container">
      <FormTextInput
          id="name"
          label="Name"
          v-model="form.name"
          placeholder="Enter icon name"
          :required="true"
      />

      <FormTextArea
          id="svg_code"
          label="SVG Code"
          v-model="form.svg_code"
          placeholder="Paste SVG code here"
          :required="true"
          max-length="15000"
      />

      <FormAlerts :data="getApiError()"/>

      <FormSubmitButton/>
    </form>
  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import FormSubmitButton from "@/components/forms/FormSubmitButton.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormTextArea from "@/components/forms/FormTextArea.vue";
import FormAlerts from "@/components/forms/FormAlerts.vue";

import {onMounted, reactive} from "vue";
import {get, post, responseMerge, getApiError} from "@/services/api";
import {useRoute} from "vue-router";
import ImgSvg from "@/components/general/ImgSvg.vue";

const route = useRoute();

// Reactive form state
const form = reactive<{ name: string | null; svg_code: string; id: string | null }>({
  id: null,
  svg_code: '',
  name: null,
});

// Fetch icon data if editing an existing record
const fetchItem = async () => {
  const iconId = route.params.iconId;
  if (iconId) {
    try {
      const response = await get(`icons/${iconId}`);
      responseMerge(form, response.data);
    } catch (error) {
      console.error("Failed to fetch icon:", error);
    }
  }
};

// Handle form submission (create or update icon)
const handleSubmit = async () => {
  try {
    const endpoint = form.id ? `icons/icon/${form.id}` : `icons/icon`;
    await post(endpoint, form);
  } catch (error) {
    console.error("Failed to save icon:", error);
  }
};

// Fetch item on mount
onMounted(fetchItem);
</script>

<style scoped>
</style>