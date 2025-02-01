<template>
  <div>
    <!-- Success Message -->
    <div v-if="showSuccessDiv">
      <div class="alert alert-success">Saved</div>
    </div>

    <!-- Error Messages -->
    <div v-if="showErrorDiv">
      <div class="alert alert-warning">
        <p><strong>There were some issues:</strong></p>
        <ul v-if="errorList.length">
          <li v-for="(messages, index) in errorList" :key="index">
            {{ messages }}
          </li>
        </ul>
        <p v-else>No specific errors were returned.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, nextTick, ref, watch} from "vue";
import type {ApiError} from "@/services/api";

// Props definition
const props = defineProps<{
  data: ApiError | null;
}>();

const showSuccessDiv = ref(false);
const showErrorDiv = ref(false);

let successTimeout: ReturnType<typeof setTimeout> | null = null;
let errorTimeout: ReturnType<typeof setTimeout> | null = null;

// Compute error list safely
const errorList = computed<string[]>(() => {
  if (!props.data || !props.data.errors) return [];
  const errors: string[] = [];

  Object.values(props.data.errors).forEach((messages) => {
    if (Array.isArray(messages)) {
      errors.push(...messages);
    }
  });

  return errors;
});

// Watch for changes in props.data
watch(
    () => props.data,
    async (newVal) => {
      showSuccessDiv.value = false;
      showErrorDiv.value = false;

      if (newVal?.isSuccess) {
        showSuccessDiv.value = true;
        await nextTick();
        successTimeout = setTimeout(() => {
          showSuccessDiv.value = false;
        }, 5000);
      }

      if (newVal?.errors && Object.keys(newVal.errors).length > 0) {
        showErrorDiv.value = true;
        await nextTick();
        errorTimeout = setTimeout(() => {
          showErrorDiv.value = false;
        }, 5000);
      }
    },
    {deep: true, immediate: true}
);
</script>

<style scoped>
.alert {
  margin: 15px 0;
  padding: 10px;
  border-radius: 5px;
}
</style>