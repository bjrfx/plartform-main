<template>
  <div class="svg-wrapper">
    <div v-html="modelValue"></div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue: string;
}>();
</script>

<style scoped>
.svg-wrapper {
  display: inline-block;
}

.svg-wrapper :deep(svg) {
  width: 100px;
  height: auto;
}
</style>