<template>
  <div class="timezone-select">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="required-asterisk">*</span>
    </label>
    <select
        :id="id"
        :value="modelValue"
        @change="updateValue($event.target.value)"
        class="form-select"
        :required="required"
        autocomplete="off"
    >
      <option value="" disabled>{{ placeholder }}</option>
      <option
          v-for="timezone in timezones"
          :key="timezone.value"
          :value="timezone.value"
          v-text="timezone.label"
      ></option>
    </select>
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
  modelValue: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: 'Select a timezone',
  },
});

const emit = defineEmits(['update:modelValue']);

function updateValue(value) {
  emit("update:modelValue", value);
}

// US Time Zones
const timezones = [
  {value: 'America/New_York', label: 'Eastern Time (ET)'},
  {value: 'America/Chicago', label: 'Central Time (CT)'},
  {value: 'America/Denver', label: 'Mountain Time (MT)'},
  {value: 'America/Phoenix', label: 'Mountain Time (MT)'},
  {value: 'America/Los_Angeles', label: 'Pacific Time (PT)'},
  {value: 'America/Anchorage', label: 'Alaska Time'},
  {value: 'Pacific/Honolulu', label: 'Hawaii-Aleutian Time'},
];

</script>

<style scoped>
.timezone-select {
  margin-bottom: 1rem;
}

.timezone-select label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

.required-asterisk {
  color: red;
  margin-left: 4px;
}

.form-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #fff;
}
</style>