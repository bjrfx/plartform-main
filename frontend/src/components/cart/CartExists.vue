<template>
  <div>
    <!-- Error Messages -->
    <div v-if="show">
      <div class="alert alert-warning">
        This bill is already in your cart and cannot be added again.
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

defineProps({
  show: {
    type: Boolean,
    required: true,
  },
});

</script>

<style scoped>
.alert {
  margin: 15px 0;
  padding: 10px;
  border-radius: 5px;
}
</style>