<template>
  <div class="form-group">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>
    <input
        :id="id"
        type="text"
        :value="formattedValue"
        @input="onInput"
        :placeholder="placeholder"
        :required="required"
        class="form-input"
        pattern="^\(\d{3}\) \d{3}-\d{4}$"
        autocomplete="tel-national"
    />
  </div>
</template>

<script setup>
//pattern tag = ensure the browser maintains its native form validation
//https://www.quackit.com/character_sets/emoji/emoji_v3.0/unicode_emoji_v3.0_characters_flags.cfm
//https://iamstevendao.com/vue-tel-input/
import {ref, watch} from "vue";

const props = defineProps({
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
    type: [String, null],
    default: "",
  },
  placeholder: {
    type: String,
    default: "(___) ___-____",
  },
});

const emit = defineEmits(["update:modelValue"]);
const rawValue = ref(props.modelValue); // Store the raw value (numeric)
const formattedValue = ref("");// Store the display value (formatted)

// Watch for changes to `modelValue` and update the input

watch(
    () => props.modelValue,
    (newValue) => {
      if (newValue === null) {
        newValue = "";
      }
      rawValue.value = newValue.replace(/\D/g, ""); // Keep only digits and limit to 10
      formattedValue.value = formatPhoneNumber(rawValue.value);
    },
    {immediate: true}
);


// Input handler to format the phone number
function onInput(event) {
  const input = event.target.value.replace(/\D/g, ""); // Remove non-numeric characters

  const limitedInput = input.slice(0, 10); // Limit to 10 digits

  event.target.value = formatPhoneNumber(input);
  emit("update:modelValue", limitedInput); // Emit the raw value
}

// Function to format phone numbers
function formatPhoneNumber(value) {
  if (value.length <= 3) return value;
  if (value.length <= 6) return `(${value.slice(0, 3)}) ${value.slice(3)}`;
  return `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
}
</script>

<style scoped>
.form-group {
  margin-bottom: 1em;
}

.form-input {
  width: 100%;
  padding: 0.5em;
  font-size: 1em;
}

.form-required {
  color: red;
}
</style>