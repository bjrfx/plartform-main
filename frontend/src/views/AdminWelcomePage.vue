<template>
  <div class="content">
    <h1>Welcome to Our Site</h1>
    <p>This is the main content area.</p>
  </div>
  <div class="sidebar">
    <LoginComponent/>
  </div>
</template>
<script setup lang="ts">
import LoginComponent from "@/components/auth/LoginComponent.vue";

</script>