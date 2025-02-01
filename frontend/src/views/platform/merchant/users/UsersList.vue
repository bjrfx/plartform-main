<template>
  <div class="content">
    <BreadcrumbComponent/>
    <h1>Users</h1>
    <div @click="openUser()">+ Add User</div>
    <div>
      <div>Search Data</div>
      <form @submit.prevent>
        <FormSelect
            id="search-types"
            :options="searchTypes"
            v-model="searchType"
            label=""
        />
        <input type="text" name="filter-search" v-model="searchText" placeholder="Search" maxlength="255">
        <button type="button" @click="initiateClearSearch()">X</button>

        <div>
          <button type="button" @click="initiateFilter()">Filter</button>
        </div>
      </form>
    </div>
    <div>
      <PaginatedTable
          :headers="headers"
          getUrl="merchants/users"
          :query="query"
      >

        <template #cell-formatted_phone_number="{ item }">
          <div>
            <a :href="'tel:' + item.formatted_phone_number?.tel" v-text="item.formatted_phone_number?.phone"></a>
          </div>
        </template>
        <template #cell-is_enabled="{ item }">
          <div v-if="item.is_enabled">Yes</div>
          <div v-else>No</div>
        </template>
        <template #cell-is_ebilling_enabled="{ item }">
          <div v-if="item.is_ebilling_enabled">Yes</div>
          <div v-else>No</div>
        </template>
        <template #cell-is_card_payment_only="{ item }">
          <div v-if="item.is_card_payment_only">Limited</div>
          <div v-else></div>
        </template>
        <template #cell-address="{ item }">
          <div v-text="userFullAddress(item)"></div>
        </template>
        <template #cell-edit-button="{ item }">
          <div @click="openUser(item.id)">Edit</div>
        </template>
      </PaginatedTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import BreadcrumbComponent from "@/components/BreadcrumbComponent.vue";
import PaginatedTable from '@/components/general/PaginatedTable.vue';
import {onMounted, reactive, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";
import FormSelect from "@/components/forms/FormSelect.vue";
import {useSiteDataStore} from "@/stores/siteData";

const route = useRoute();
const router = useRouter();

const siteDataStore = useSiteDataStore();

interface QueryParams {
  search?: string | null;
  search_type?: string;
}

// Table headers
const headers = [
  {name: 'Name', key: 'name', sortable: true},
  {name: 'Email', key: 'email', sortable: true},
  {name: 'Phone', key: 'formatted_phone_number', sortable: false},
  {name: 'Address', key: 'address', sortable: false},
  {name: 'Role', key: 'role', sortable: true},
  {name: 'Enabled', key: 'is_enabled', sortable: false},
  {name: 'Profile Updated At', key: 'profile_updated_at', sortable: true},
  {name: 'eBilling Enabled', key: 'is_ebilling_enabled', sortable: false},
  {name: 'eBilling Opt At', key: 'ebilling_opt_at_tz', sortable: true},
  {name: 'Notifications Enabled', key: 'is_notifications_enabled', sortable: false},
  {name: 'Card Payment Only', key: 'is_card_payment_only', sortable: false},
  {name: 'Card Payment Updated At', key: 'only_card_payment_updated_at_tz', sortable: true},
  {name: 'Edit', key: 'edit-button', sortable: false},
];

const searchTypes = [
  {value: "name", name: "User Name"},
  {value: "email", name: "User Email"},
];


//const query = reactive<QueryParams>({});
const searchType = ref<string | null>(
    route.query?.search_type && route.query.search_type !== "null"
        ? String(route.query.search_type)
        : searchTypes[0].value
);
const searchText = ref<string | null>(route.query?.search ? String(route.query.search) : null);


watch(
    () => searchType,
    (newVal) => {
      if (!newVal || String(newVal).length === 0) {
        searchType.value = 'name';
      }
    },
    {immediate: true}
);
// Initiate search and update query
const query = reactive<QueryParams>({});
const initiateFilter = () => {
  if (searchText.value && searchText.value.length > 0) {
    query.search = searchText.value
    query.search_type = String(searchType.value);
  }

  router.push({
    query: query,
  });
};


const initiateClearSearch = () => {
  searchText.value = null;
  initiateFilter();
};

const openUser = async (id: string | null = null) => {
  await router.push({
    name: 'UserEdit',
    params: {userId: id},
  });
};

const userFullAddress = (item: Record<string, any>) => {
  let address = item.street;
  address += ", " + item.city;
  address += ", " + item.state;
  address += " " + item.zip_code;

  return address;
};


onMounted(() => {
  siteDataStore.setFullWidthClass();  // Add the full-width class when the app is loaded
});

</script>