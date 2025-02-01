<template>
  <div class="form-group">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>
    <input
        :id="id"
        type="text"
        :value="modelValue"
        @input="onInput"
        :placeholder="placeholder"
        :required="required"
        class="form-input"
        maxlength="5"
        pattern="\d{5}"
        autocomplete="postal-code"
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
    default: '#####',
  },
});

const emit = defineEmits(['update:modelValue']);

function onInput(event) {
  const input = event.target.value.replace(/\D/g, ""); // Remove non-numeric characters

  const limitedInput = input.slice(0, 5);

  event.target.value = limitedInput;
  emit("update:modelValue", limitedInput); // Emit the raw value
}
</script>

<style scoped>
</style>