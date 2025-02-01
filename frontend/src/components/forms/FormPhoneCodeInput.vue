<template>
  <div class="phone-input">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="required-asterisk">*</span>
    </label>
    <div class="phone-input-group">
      <select
          :id="id"
          :value="modelValue || defaultOption"
          @change="updateValue"
          class="phone-code-dropdown"
          :required="required"
          autocomplete="off"
      >
        <option v-if="!required" value=""></option>
        <option
            v-for="ext in extensions"
            :key="ext.value"
            :value="ext.value"
        >
          {{ ext.label }}
        </option>
      </select>
    </div>
  </div>
</template>

<script setup>
import {computed, watch} from "vue";

// Props
const props = defineProps({
  id: {type: String, required: true},
  label: {type: String, default: "Phone Country Code"},
  modelValue: {type: [String, null], default: null},
  extensions: {
    type: Array,
    default: () => [
      {value: "+1", label: "+1 (USA)"},
      {value: "+44", label: "+44 (UK)"},
    ],
  },
  required: {type: Boolean, default: false},
});

const emit = defineEmits(["update:modelValue"]);

// Default option (computed to dynamically select the first option if needed)
const defaultOption = computed(() => {
  if (!props.required) {
    return ""
  }
  return props.extensions.length ? props.extensions[0].value : "";
});

// Emit change event to update modelValue
const updateValue = (event) => {
  emit("update:modelValue", event.target.value);
};

// Watch modelValue AND required to ensure default is emitted correctly
watch(
    [() => props.modelValue, () => props.required],
    ([newValue, required]) => {
      if (!newValue && required) {
        emit("update:modelValue", defaultOption.value);
      }
    },
    {immediate: true}
);
</script>

<style scoped>
.phone-input {
  margin-bottom: 1rem;
}

.phone-input label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

.required-asterisk {
  color: red;
  margin-left: 4px;
}

.phone-input-group {
  display: flex;
  align-items: center;
}

.phone-dropdown {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px 0 0 4px;
  background-color: #fff;
}
</style>