<template>
  <div class="checkbox-container">
    <input
        :id="id"
        type="checkbox"
        :checked="modelValue"
        @change="updateValue($event.target.checked)"
        :required="required"
        :disabled="disabled"
        class="form-checkbox"
    />
    <label :for="id">{{ label }}</label>
  </div>
</template>

<script setup>
import {watchEffect} from 'vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  modelValue: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
});

// Declare the event
const emit = defineEmits(['update:modelValue']);

// Automatically set to false if modelValue is null
watchEffect(() => {
  if (props.modelValue === null) {
    updateValue(false);
  }
});

// Emit the new value when the checkbox state changes
function updateValue(value) {
  emit('update:modelValue', Boolean(value)); // Emit the event
}
</script>

<style scoped>
.checkbox-container {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.form-checkbox {
  margin-right: 0.5rem;
}

label {
  font-weight: normal;
  font-size: 1rem;
}
</style>