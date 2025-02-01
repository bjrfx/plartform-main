<template>
  <div class="site-loader" v-if="toggleLoading">
    <div></div>
    <div></div>
    <div></div>
    <div></div>
    <div></div>
  </div>
</template>

<script setup lang="ts">
import {useLoadingState} from '@/services/api';
import {computed} from "vue";

// Inject global loading state
const isLoading = useLoadingState();

const toggleLoading = computed(() => {
  if (isLoading.value) {
    return true;
  }
  setTimeout(() => {
    return false;
  }, 50);
})
/*
defineProps<{
  show: boolean;
}>();

 */
</script>

<style scoped>
.site-loader {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px; /* Space between circles */
}

.site-loader div {
  width: 15px;
  height: 15px;
  border-radius: 50%; /* Makes it a circle */
  animation: bounce 1.2s infinite ease-in-out;
}

.site-loader div:nth-child(1) {
  animation-delay: 0s;
}

.site-loader div:nth-child(2) {
  animation-delay: 0.15s;
}

.site-loader div:nth-child(3) {
  animation-delay: 0.3s;
}

.site-loader div:nth-child(4) {
  animation-delay: 0.45s;
}

.site-loader div:nth-child(5) {
  animation-delay: 0.6s;
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}
</style>