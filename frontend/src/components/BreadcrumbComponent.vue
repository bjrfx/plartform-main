<script setup>
import {computed, watch} from 'vue';
import {useRoute, useRouter} from 'vue-router';

const route = useRoute();
const router = useRouter();

// Props to receive params from parent component
const props = defineProps({
  placeholders: {
    type: Object,
    default: () => ({})
  }
});

// Watch query changes and save them to session storage
watch(
    () => route.query,
    (newQuery) => {
      if (newQuery && typeof newQuery === 'object') {
        sessionStorage.setItem(`pageQuery:${String(route.name)}`, JSON.stringify(newQuery));
      }
    },
    {immediate: true}
);

const routeParams = {...route.params};


const breadcrumbs = computed(() => {
  const trail = [];
  let currentRoute = route;

  // Walk up the parent hierarchy using the meta.parent
  while (currentRoute) {
    const {meta, name, path, params} = currentRoute;

    // Check session storage for the query on mount or navigation
    let storedQuery = sessionStorage.getItem(`pageQuery:${String(name)}`);
    if (storedQuery) {
      storedQuery = JSON.parse(storedQuery);
    }

    if (meta?.breadcrumb) {
      // Pass params only if the route uses them
      const paramsToPass = {};
      for (const key of Object.keys(routeParams)) {
        if (
            (path && path.includes(`:${key}`))
            ||
            (params && key in params)
        ) {
          paramsToPass[key] = routeParams[key];
        }
      }

      // Replace :param with actual value from props or route params
      const label = meta.breadcrumb.replace(/:(\w+)/g, (_, key) => {
        if (key in props.placeholders) {
          return props.placeholders[key];
        }
        return key;
      });

      trail.unshift({
        to: {
          name,
          params: paramsToPass, // Pass relevant params like merchantId
          query: storedQuery ?? {}
        },
        label: label,
      });
    }

    // Resolve the parent route if defined
    const parentName = meta?.parent;
    if (parentName) {
      const resolvedParent = router.resolve({name: parentName});
      currentRoute = resolvedParent.matched.length
          ? resolvedParent.matched[resolvedParent.matched.length - 1]
          : null;
    } else {
      currentRoute = null; // No parent defined, stop the loop
    }
  }

  return trail;
});
</script>

<template>
  <nav>
    <ul class="breadcrumbs">
      <li v-for="(crumb, index) in breadcrumbs" :key="index">
        <router-link :to="crumb.to">
          <span v-text="crumb.label"></span>
        </router-link>
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.breadcrumbs {
  list-style: none;
  display: flex;
  gap: 5px;
}

.breadcrumbs li::after {
  content: '/';
  margin-left: 5px;
}

.breadcrumbs li:last-child::after {
  content: '';
}
</style>