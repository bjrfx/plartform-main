<template>
  <div class="form-group">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>
    <input
        :id="id"
        type="email"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :required="required"
        class="form-input"
        pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$"
        maxlength="255"
        autocomplete="email"
    />
  </div>
</template>

<script setup>
defineProps({
  id: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  required: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
</style>