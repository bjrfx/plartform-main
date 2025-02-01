<template>
  <div class="btn">
    <button type="submit" :disabled="disabled">Save</button>
  </div>
</template>

<script setup lang="ts">
// Props definition
defineProps({
  disabled: {
    type: Boolean,
    default: false,
    required: false,
  },
});
</script>