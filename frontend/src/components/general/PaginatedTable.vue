<script lang="ts" setup>
import {defineProps, ref, computed, watch, watchEffect, onMounted} from 'vue';
import {get} from "@/services/api";
import {useRoute, useRouter} from "vue-router";

// Router
const route = useRoute();
const router = useRouter();

// Pagination Interface
interface Pagination {
  current_page: number;
  from: number;
  per_page: number;
  has_next_page: boolean;
  last_page?: number;
  total?: number;
}

// Generic API Response Type
interface ApiResponse<T = Record<string, any>> {
  data: T[];
  pagination: Pagination;
}

// Table Header Interface
interface TableHeader {
  name: string;
  key: string;
  sortable?: boolean;
}

// Props
const props = defineProps({
  getUrl: {
    type: String,
    required: true,
  },
  headers: {
    type: Array as () => TableHeader[],
    required: true,
  },
  query: {
    type: Object as () => Record<string, any>,
    required: false,
    default: () => ({}),
  },
});

// State
const sortKey = ref<string | null>(route.query.sort ? String(route.query.sort) : null);
const sortOrder = ref<'asc' | 'desc'>(route.query.order === 'desc' ? 'desc' : 'asc');
const data = ref<Record<string, any>[]>([]);
const pagination = ref<Pagination>({
  current_page: 1,
  from: 1,
  per_page: 20,
  has_next_page: false,
  last_page: 1,
  total: 1,
});
const loading = ref<boolean>(false);  // Loading state
const error = ref<string | null>(null);  // Error message for better UX

// Computed Values
const currentPage = computed(() => pagination.value.current_page);
const hasNextPage = computed(() => pagination.value.has_next_page);
const totalPages = computed(() => pagination.value?.total ?? 0);
const hasData = computed(() => data.value.length > 0);

// Track Initial Fetch
let initialFetchDone = false;

// Fetch Items
const fetchItem = async (queryParams: Record<string, any> = {}) => {
  loading.value = true;
  error.value = null;
  try {
    const response = await get<ApiResponse>(props.getUrl, {params: queryParams});
    if (response.pagination) pagination.value = response.pagination;
    data.value = response.data || [];
  } catch (err) {
    error.value = "Failed to load data. Please try again.";
    console.error("Failed to fetch:", err);
  } finally {
    loading.value = false;
  }
};

// Set Page (for Pagination)
const setPage = (page: number) => {
  router.push({query: {...route.query, page}});
};

// Handle Sorting
const handleSort = (key: string) => {
  sortKey.value = key;
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';

  router.push({
    query: {
      ...route.query,
      page: 1,
      sort: sortKey.value,
      order: sortOrder.value,
    },
  });
};

// Watch for route.query changes and fetch data
watch(
    () => route.query,
    (newQuery: Record<string, any>) => {
      initialFetchDone = true;
      fetchItem(newQuery);
    },
    {immediate: true, deep: true}
);

// Sync props.query with route.query (initial load and when props change)
watchEffect(() => {
  if (Object.keys(props.query).length > 0) {
    router.push({
      query: {
        ...route.query,
        ...props.query,
      },
    });
  }
});

// Fallback Fetch on Component Mount
onMounted(() => {
  if (!initialFetchDone) {
    fetchItem(route.query);
  }
});

// Format Cell Values
const cellValue = (item: Record<string, any>, key: string) => {
  return key in item ? item[key] ?? '' : '';
};
</script>

<template>
  <div class="table-responsive-container">
    <!-- Loading Indicator -->
    <div v-if="loading" class="loading-state">
      Loading data, please wait...
    </div>

    <!-- Error State -->
    <div v-if="error && !loading" class="error-state">
      {{ error }}
    </div>

    <!-- Empty State (No Records Found) -->
    <div v-if="!loading && !hasData && !error" class="empty-state">
      No records found. Try adjusting the filters or check back later.
    </div>

    <div class="table-responsive">
      <!-- Data Table (Show when data is available) -->
      <table v-if="hasData" class="table">
        <thead>
        <tr>
          <th
              v-for="header in headers"
              :key="header.key"
              @click="header.sortable ? handleSort(header.key) : undefined"
              :class="{ sortable: header.sortable, sorted: sortKey === header.key }"
          >
            {{ header.name }}
            <span v-if="header.sortable" class="sort-icon">
              <template v-if="sortKey === header.key">
                {{ sortOrder === 'asc' ? '▲' : '▼' }}
              </template>
              <template v-else>⇅</template>
            </span>
          </th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="(item, index) in data" :key="index">
          <td v-for="header in headers" :key="header.key + index" :data-name="header.name">
            <slot :name="`cell-${header.key}`" :item="item" :index="index">
              <span v-text="cellValue(item, header.key)"></span>
            </slot>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Section -->
    <div v-if="!loading && hasData" class="pagination">
      <button @click="setPage(currentPage - 1)" :disabled="currentPage === 1">
        Previous
      </button>
      <span>Page {{ currentPage }} <span v-if="totalPages > 0">of {{ totalPages }}</span>
      </span>
      <button @click="setPage(currentPage + 1)" :disabled="!hasNextPage">
        Next
      </button>
    </div>
  </div>
</template>