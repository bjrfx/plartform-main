<template>
  <div class="form-group">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>
    <input
        :id="id"
        :type="inputType"
        :value="modelValue"
        @input="handleInput"
        @keydown="checkCapsLock"
        @keyup="checkCapsLock"
        :placeholder="placeholder"
        :required="required"
        class="form-input"
        maxlength="255"
        :minlength="minLength"
        autocomplete="new-password"
    />
    <button type="button" @click="toggleInputType">
      <span v-if="isValueVisible">O</span>
      <span v-else>X</span>
    </button>

    <div v-if="isCapsLockOn" class="caps-warning">
      Caps Lock is ON
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, onMounted, ref} from "vue";
import {useAuthStore} from "@/stores/auth";

const authStore = useAuthStore();

// Props definition
defineProps({
  id: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: false,
    default: "Password"
  },
  required: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: [String, null],
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
});

// Emits definition
const emit = defineEmits(['update:modelValue']);

// Reactive state for input type
const inputType = ref('password');
const isCapsLockOn = ref(false);

// Toggle between 'text' and 'password'
const toggleInputType = () => {
  inputType.value = inputType.value === 'password' ? 'text' : 'password';
};

// Computed property to check if input is visible
const isValueVisible = computed(() => inputType.value === "text");
const minLength = computed(() => (rules.value?.min ?? 0));

// Handle input event and emit value change
const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};

// Check for caps-lock
const checkCapsLock = (event: Event) => {
  // Only proceed if the event is a KeyboardEvent
  if (event instanceof KeyboardEvent) {
    const isShiftPressed = event.getModifierState("Shift");
    isCapsLockOn.value = event.getModifierState("CapsLock") && !isShiftPressed;
  }
};

const rules = ref();
onMounted(async () => {
  try {
    rules.value = await authStore.passwordRules();
  } catch (error) {
    console.error("Failed to load password rules:", error);
    rules.value = {min: 8};  // Set default rules if API call fails
  }
});
</script>

<style scoped>
.caps-warning {
  color: #e74c3c;
  font-size: 0.9em;
  margin-top: 5px;
}
</style>