<template>
  <div class="form-group">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>
    <select
        :id="id"
        v-model="formattedValue"
        :required="required"
        class="form-select"
        autocomplete="off"
    >
      <option
          v-if="placeholder"
          value=""
          disabled
      >
        {{ placeholder }}
      </option>
      <option
          v-for="(option, index) in availableOptions"
          :key="index"
          :value="option.value"
          :disabled="option.disabled"
      >
        {{ option.name }}
      </option>
    </select>
  </div>
</template>

<script setup lang="ts">
import {ref, watch, computed} from "vue";

// Props definition
const props = defineProps<{
  id: string;
  label: string;
  required?: boolean;
  modelValue: string | null;
  options: { value: string | number | boolean; name: string, disabled?: boolean }[];
  placeholder?: string | null;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

// Reactive value to track selected option
const formattedValue = ref(props.modelValue);

// Computed to dynamically check if modelValue exists in options
const availableOptions = computed(() => {
  const exists = props.options.some(option => option.value === formattedValue.value);

  // If modelValue is missing in options, temporarily add it
  if (formattedValue.value && !exists) {
    return [
      ...props.options,
      {
        value: formattedValue.value,
        name: `Selected (${formattedValue.value})`,
        disabled: false
      },
    ];
  }
  return props.options;
});

// Watch for modelValue changes
watch(
    () => props.modelValue,
    (newValue) => {
      formattedValue.value = (newValue && newValue !== "null") ? newValue : '';
    },
    {immediate: true}
);

// Emit when dropdown value changes
watch(
    formattedValue,
    (newValue) => {
      emit("update:modelValue", String(newValue));
    }
);
</script>

<style scoped>
.form-select {
  width: auto;
  display: inline-block;
}
</style>