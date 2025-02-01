<template>
  <div class="copy-container">
    <span v-if="showText" class="copy-text">{{ textValue }}</span>
    <button type="button" v-if="showButton" @click="copyToClipboard" class="copy-button">
      {{ copied ? 'Copied!' : buttonText }}
    </button>
  </div>
</template>

<script setup>
import {computed, ref, watchEffect} from 'vue';

// Props
const props = defineProps({
  text: {
    type: [String, null],
    required: true,
    default: ''
  },
  buttonText: {
    type: String,
    default: 'Copy',
  },
  showText: {
    type: Boolean,
    default: true, // Whether to display the text to be copied
  }
});

const textValue = ref(props.text || '');  // Initialize with prop or empty string

watchEffect(() => {
  if (props.text === null) {
    textValue.value = '';  // Update local state instead of props
  } else {
    textValue.value = props.text;
  }
});

const showButton = computed(() => {
  return textValue.value && textValue.value.length > 0;
});

const copied = ref(false);

const copyToClipboard = () => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(props.text)
        .then(() => {
          copied.value = true;
          setTimeout(() => copied.value = false, 2000);
        })
        .catch(err => {
          console.error('Failed to copy text:', err);
        });
  } else {
    const textArea = document.createElement('textarea');
    textArea.value = props.text;
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      copied.value = true;
      setTimeout(() => copied.value = false, 2000);
    } catch (err) {
      console.error('Fallback copy failed:', err);
      alert('Failed to copy. Please try manually.');
    }
    document.body.removeChild(textArea);
  }
};
</script>

<style scoped>
.copy-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.copy-text {
  font-size: 1rem;
  color: #333;
}

.copy-button {
  padding: 5px 10px;
  cursor: pointer;
  border: 1px solid #ccc;
  background-color: #f0f0f0;
  transition: background-color 0.2s;
}

.copy-button:hover {
  background-color: #e0e0e0;
}
</style>