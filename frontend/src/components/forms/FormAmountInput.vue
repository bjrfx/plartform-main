<template>
  <div class="form-group component-input">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>

    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text" :id="'basic-addon' + id">$</span>
      </div>
      <input
          :id="id"
          type="text"
          :value="modelValue"
          @input="validateInput"
          :placeholder="placeholder??''"
          :required="required"
          class="form-control"
          maxlength="255"
          autocomplete="off"
          v-bind:aria-label="label??''"
          v-bind:aria-describedby="'basic-addon' + id"
          @focusin="adjustNumber"
          @focusout="fixNumber"
      />
    </div>

  </div>
</template>

<script setup lang="ts">
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
    type: [String, null],
    default: null,
  },
  placeholder: {
    type: [String, null],
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const validateInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  let value = target.value;

  // Remove non-numeric and non-decimal characters
  value = value.replace(/[^0-9.]/g, '');

  // Prevent multiple decimals
  const decimalCount = (value.match(/\./g) || []).length;
  if (decimalCount > 1) {
    value = value.substring(0, value.lastIndexOf('.'));
  }

  // Prevent starting with a decimal point
  if (value.startsWith('.')) {
    value = '0' + value;
  }

  // Limit to two decimal places
  const parts = value.split('.');
  if (parts[1] && parts[1].length > 2) {
    parts[1] = parts[1].substring(0, 2);
  }
  value = parts.join('.');

  // **Update the input directly to reflect the value in the UI**
  target.value = value;

  // Emit the formatted value to sync with v-model
  emit('update:modelValue', value);

  // Trigger required validation
  if (props.required && (!value || value.trim() === '' || parseFloat(value) === 0)) {
    let text = 'Value';
    if (props.label && props.label.length > 0) {
      text = props.label;
    }
    target.setCustomValidity('Required: ' + text + ' must be greater than 0.');
  } else {
    target.setCustomValidity('');
  }
}

const adjustNumber = (event: Event) => {
  const target = event.target as HTMLInputElement;
  let value = target.value;

  if (parseFloat(value) === parseInt(value)) {
    //If its .00
    emit('update:modelValue', parseInt(value));
  }
}

const fixNumber = (event: Event) => {
  const target = event.target as HTMLInputElement;
  let value = target.value;

  emit('update:modelValue', Number(value).toFixed(2));

}
</script>

<style scoped>
</style>