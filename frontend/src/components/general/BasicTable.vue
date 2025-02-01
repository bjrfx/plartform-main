<script lang="ts" setup>
import {computed, defineProps, defineEmits, PropType} from 'vue';

// Table Header Interface
interface TableHeader {
  name: string;
  key: string;
}

// Props
const props = defineProps({
  modelValue: {
    type: Array as PropType<Record<string, any>[]>,
    required: true,
  },
  headers: {
    type: Array as PropType<TableHeader[]>,
    required: true,
  },
  loading: {
    type: Boolean,
    required: false,
    default: false,
  },
  reorder: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

// Computed States
const isReorder = computed(() => props.reorder);
const isLoading = computed(() => props.loading);
const hasData = computed(() => props.modelValue.length > 0);

// Helper: Format Cell Values
const cellValue = (item: Record<string, any>, key: string) => {
  return key in item ? item[key] ?? '' : '';
};

// Move item up
const moveUp = (index: number) => {
  if (index > 0) {
    const items = [...props.modelValue];
    [items[index - 1], items[index]] = [items[index], items[index - 1]];
    emit('update:modelValue', items);
  }
};

// Move item down
const moveDown = (index: number) => {
  if (index < props.modelValue.length - 1) {
    const items = [...props.modelValue];
    [items[index], items[index + 1]] = [items[index + 1], items[index]];
    emit('update:modelValue', items);
  }
};
</script>

<template>
  <div class="table-responsive-container">
    <!-- Loading Indicator -->
    <div v-if="isLoading" class="loading-state" aria-live="polite">
      Loading data, please wait...
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasData" class="empty-state" aria-live="polite">
      No records found. Please check back later.
    </div>

    <!-- Data Table -->
    <div v-else class="table-responsive">
      <table class="table">
        <thead>
        <tr>
          <th v-if="isReorder">Order</th>
          <th v-for="header in headers" :key="header.key" scope="col">
            {{ header.name }}
          </th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(item, index) in props.modelValue" :key="index">
          <td v-if="isReorder" class="rows-reorder">
            <div class="actions">
              <button
                  type="button"
                  @click="moveUp(index)"
                  :disabled="index === 0"
                  aria-label="Move item up"
                  class="btn btn-reorder-up"
              >
                ↑
              </button>
              <span>{{ index + 1 }}</span>
              <button
                  type="button"
                  @click="moveDown(index)"
                  :disabled="index === props.modelValue.length - 1"
                  aria-label="Move item down"
                  class="btn btn-reorder-down"
              >
                ↓
              </button>
            </div>
          </td>
          <td
              v-for="header in headers"
              :key="header.key + index"
              :data-name="header.name"
          >
            <slot
                :name="`cell-${header.key}`"
                :item="item"
                :index="index"
                :move-up="moveUp"
                :move-down="moveDown"
            >
              <span>{{ cellValue(item, header.key) }}</span>
            </slot>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>