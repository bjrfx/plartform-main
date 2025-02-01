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
      <option value="" disabled>{{ placeholder }}</option>
      <option
          v-for="state in statesList"
          :key="state.value"
          :value="String(state.value)"
      >
        {{ state.name }}
      </option>
    </select>
  </div>
</template>

<script setup lang="ts">
import {ref, computed, watch} from "vue";
import {useStateStore} from "@/stores/states";

const stateStore = useStateStore();
const statesList = computed(() => stateStore.states);

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
    type: [String, Number, null],
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Select your state',
  },
});

const emit = defineEmits(['update:modelValue']);
const formattedValue = ref(props.modelValue);

// Watch for parent value changes and update local state
watch(
    () => props.modelValue,
    (newValue) => {
      formattedValue.value = newValue ? String(newValue) : '';
    },
    {immediate: true}
);

// Emit value changes to the parent
watch(
    formattedValue,
    (newValue) => {
      emit("update:modelValue", newValue);
    }
);

// Handle state list loading without overwriting modelValue unnecessarily
watch(
    statesList,
    (newStates) => {
      if (newStates.length > 0 && formattedValue.value) {
        const exists = newStates.some(
            state => String(state.value) === String(formattedValue.value)
        );
        if (!exists) {
          // Only reset if current value is invalid and list is loaded
          formattedValue.value = '';
        }
      }
    },
    {immediate: true}
);
</script>