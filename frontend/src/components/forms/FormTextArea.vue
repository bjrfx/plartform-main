<template>
  <div class="form-group component-textarea">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>
    <textarea
        :id="id"
        :value="modelValue"
        @input="handleInput"
        :placeholder="placeholder ?? ''"
        :required="required"
        :maxlength="maxLength"
        class="form-input"
        ref="textarea"
        autocomplete="off"
    />
  </div>
</template>

<script setup lang="ts">
import {ref, watch, onMounted} from 'vue';

// Props definition
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  label: {
    type: [String, null],
    required: false,
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
    type: [String, null],
    required: false,
    default: '',
  },
  maxLength: {
    type: [String],
    default: '255',
  },
});

// Emits for v-model binding
const emit = defineEmits(['update:modelValue']);
const textarea = ref<HTMLTextAreaElement | null>(null);

// Adjust the textarea height dynamically
const adjustHeight = () => {
  if (textarea.value) {
    textarea.value.style.height = 'auto';  // Reset to auto to shrink if needed
    textarea.value.style.height = `${textarea.value.scrollHeight}px`;

    // Enable scrolling if scrollHeight exceeds max-height
    if (textarea.value.scrollHeight > 300) {
      textarea.value.classList.add('scrollable');
    } else {
      textarea.value.classList.remove('scrollable');
    }
  }
};

// Handle input and emit changes with auto-adjust
const handleInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement;
  let inputValue = String(target.value);

  if (props.maxLength && inputValue.length > Number(props.maxLength)) {
    inputValue = inputValue.slice(0, Number(props.maxLength));
  }

  emit('update:modelValue', inputValue);
  adjustHeight();
};

// Watch for changes to modelValue (external updates)
watch(
    () => props.modelValue,
    () => {
      if (textarea.value && textarea.value.value !== props.modelValue) {
        textarea.value.value = props.modelValue;
        adjustHeight();
      }
    }
);

// Adjust height on mount
onMounted(() => {
  adjustHeight();
});
</script>


<!--suppress CssUnusedSymbol -->
<style scoped>
textarea {
  overflow-y: hidden; /* No scroll by default */
  resize: none;
  min-height: 40px;
  max-height: 300px; /* Limit height */
  width: 100%;
}


textarea.scrollable {
  overflow-y: auto; /* Allow scrolling when max height is hit */
}
</style>