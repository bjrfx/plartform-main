<template>
  <div id="app">
    <div class="app-container">
      <SiteLoader/>
      <!-- Header -->
      <header class="header">
        <h1>
          <span v-text="siteDataStore.siteData.name"></span>
        </h1>
      </header>

      <!-- Main content area with right-side menu -->
      <div class="main-container" :class="mainContainerClass">

        <!-- The content of each route will be rendered here -->
        <router-view></router-view>
      </div>

      <!-- Footer -->
      <footer class="footer">
        <p>Footer Content <span v-text="currentYear"></span></p>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
import {useSiteDataStore} from '@/stores/siteData';
import {computed} from "vue";
import {useRouter} from 'vue-router';
import SiteLoader from "@/components/general/SiteLoader.vue";

// Pinia store instance
const siteDataStore = useSiteDataStore();

const mainContainerClass = computed(() => siteDataStore.appFullWidth ? 'main-container-full' : '');
const currentYear = computed(() => new Date().getFullYear());

// Reset the class before entering any route
const router = useRouter();
router.afterEach((to, from) => {
  if (to.name !== from.name) {
    //if it on the same page keep the page width definition
    siteDataStore.unsetFullWidthClass();
  }
});

</script>