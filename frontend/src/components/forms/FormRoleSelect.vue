<template>
  <div class="form-group">
    <label :for="id">
      {{ label }}
      <span v-if="required" class="form-required">*</span>
    </label>
    <select
        :id="id"
        v-model="formattedValue"
        @change="updateValue"
        class="roles-dropdown"
        :required="required"
        autocomplete="off"
    >
      <option
          v-for="role in filteredRolesList"
          :key="role.value"
          :value="role.value"
          :disabled="isRoleDisabled(role.value)"
      >
        {{ role.label }}
      </option>
    </select>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from "vue";
import {useAuthStore} from "@/stores/auth";
import {useRoleStore} from "@/stores/roles";
import {UserRole} from "@/enums/enums";
import {useSiteDataStore} from "@/stores/siteData";

const rolesStore = useRoleStore();
const siteDataStore = useSiteDataStore();

// Auth Store
const authStore = useAuthStore();
const authRole = computed<UserRole>(() => authStore.getUserRoll() as UserRole || UserRole.MEMBER);

// Define props
const props = defineProps<{
  id: string;
  label: string;
  modelValue: string | null;
  required?: boolean;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", value: string): void;
}>();

// Reactive value for v-model
const formattedValue = ref<string | null>(props.modelValue);

// Filter roles based on authRole
const fullRolesList = computed(() => {
  return rolesStore.getAllRoles();
});

const isMerchantSite = computed(() => {
  return siteDataStore.isMerchantSite();
});

const filteredRolesList = computed(() => {
  //If it's a merchant site and this is a system auth user
  if (isMerchantSite.value && rolesStore.isSystemRole(authRole.value)) {
    //Limit user roles options for the system auth user
    return rolesStore.getRoles(UserRole.SUPPORT);
  }
  return rolesStore.getRoles(authRole.value);
});

const isRoleDisabled = (role: UserRole) => {
  if (!isMerchantSite.value && authRole.value === UserRole.SYSTEM_ADMIN) {
    return false;
  }
  const roles = rolesStore.getRoles(authRole.value);
  return !roles.some((val) => val.value === role);

};

// Watch for modelValue changes and sync with dropdown
watch(
    () => props.modelValue,
    (newValue) => {
      if (newValue && !filteredRolesList.value.some(role => role.value === newValue)) {
        // If modelValue has role that isn't in the list, push it temporarily
        const selectedRole = fullRolesList.value.find((role) => role.value === newValue);
        if (selectedRole) {
          filteredRolesList.value.push(selectedRole);
        }
      }
      formattedValue.value = newValue;
    },
    {immediate: true}
);

// Watch for filtered list changes to sync selected value
watch(
    filteredRolesList,
    () => {
      if (!filteredRolesList.value.some(role => role.value === formattedValue.value)) {
        formattedValue.value = filteredRolesList.value[0]?.value || "member";
      }
    },
    {immediate: true, deep: true}
);

// Emit value on select change
function updateValue(event: Event) {
  const target = event.target as HTMLSelectElement;
  emit("update:modelValue", target.value);
}
</script>