<template>
  <div class="select-searchable">
    <label v-if="label" v-text="label"></label>
    <div>
      <div class="select-searchable-search">
        <!-- Search Input -->
        <input
            type="text"
            v-model="searchQuery"
            @focus="dropdownOpen = true"
            @blur="closeDropdown"
            placeholder="Search..."
        />
        <button type="button" @click="clearSearch" v-if="searchQuery">Clear</button>
      </div>
      <div class="select-searchable-list" @mousedown.prevent>
        <!-- Dropdown List -->
        <div class="select-searchable-wrapper" v-if="dropdownOpen">
          <ul class="list-group">
            <li
                v-for="option in filteredOptions"
                :key="option.id"
                class="list-group-item"
            >
              <label :for="'checkbox-' + option.id">
                <input
                    type="checkbox"
                    :id="'checkbox-' + option.id"
                    :checked="option.selected"
                    @input="toggleSelection(option)"
                />
                <span v-text="option.label" @click="toggleSelection(option)"></span>
              </label>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="select-searchable-selected">
      <ul class="list-group list-group-horizontal">
        <li class="list-group-item" v-for="selOption in selectedList" :key="selOption.id">
          <div @click="toggleSelection(selOption)">
            <span>[X]</span>
            <span v-text="selOption.label"></span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts" setup>
/**
 * @example:
 * <SearchableSelect
 *       :options="selectOptions"
 *       v-model="selectedValues"
 *     />
 *
 * const selectOptions = ref([
 *   { id: 1, label: 'Option 1', selected: false },
 *   { id: 2, label: 'Option 2', selected: false },
 *   { id: 3, label: 'Option 3', selected: false },
 * ]);
 *
 * const selectedValues = ref<string[]>([]);
 */
import {ref, computed, defineProps, watch, onMounted} from 'vue';

// Props
const props = defineProps({
  options: {
    type: Array as () => { id: string | number; label: string; selected: boolean }[],
    required: true,
  },
  modelValue: {
    type: Array as () => (string | number)[],
    required: true,
  },
  label: {
    type: String,
    default: null,
  },
});

// Emit events
const emit = defineEmits(['update:modelValue']);

// Local state
const searchQuery = ref('');
const dropdownOpen = ref(false);

// Compute filtered options based on the search query
const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;
  return props.options.filter(option =>
      option.label.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

// Function to toggle selection
const toggleSelection = (option: { id: string | number; label: string; selected: boolean }) => {
  const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : [props.modelValue];
  if (currentValue.includes(option.id)) {
    const index = currentValue.indexOf(option.id);
    currentValue.splice(index, 1); // Remove the selected option
    option.selected = false; // Update selected state
  } else {
    currentValue.push(option.id); // Add the new selection
    option.selected = true; // Update selected state
  }
  emit('update:modelValue', currentValue); // Notify the parent
};

// Clear search input
const clearSearch = () => {
  searchQuery.value = '';
};

// Close the dropdown
const closeDropdown = () => {
  dropdownOpen.value = false;
  searchQuery.value = ''; // Reset search query
};

// Sync options' `selected` state with `modelValue`
const syncSelectedState = () => {
  const normalizedValue = Array.isArray(props.modelValue) ? props.modelValue : [props.modelValue];
  props.options.forEach(option => {
    option.selected = normalizedValue.includes(option.id);
  });
};

const selectedList = computed(() => {
  return props.options.filter(merchant =>
      props.modelValue.includes(merchant.id)
  );
});

watch(
    () => props.modelValue,
    syncSelectedState,
    {deep: true, immediate: true}
);
// Initialize and watch for changes
onMounted(syncSelectedState);
</script>

<style scoped>
.select-searchable-list {
  position: relative;
}

.select-searchable-list .select-searchable-wrapper {
  position: absolute;
  z-index: 10;
  max-height: 400px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #000000;
}

.select-searchable-list .select-searchable-wrapper label span {
  display: inline-block;
}
</style>

