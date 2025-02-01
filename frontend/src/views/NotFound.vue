<template>
  <div>
    <h1>404 - Page Not Found</h1>
    <p>Sorry, the page you're looking for does not exist.</p>
    <router-link to="/">Go back to the homepage</router-link>
  </div>
</template>
<script lang="ts">
export default {
  name: 'NotFound',
};
</script>