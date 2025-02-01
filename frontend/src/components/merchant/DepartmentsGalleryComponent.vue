<template>
  <div class="departments-gallery">
    <div class="department-gallery"
         v-for="department in departments"
         :key="department.id"
         :class="{disabled: department.is_locked}"
         @click="openDepartment(department)"
         :title="department.name"
    >
      <div class="department-gallery-lock" v-if="department.is_locked">Lock</div>
      <ImgSvg v-if="department.icon" v-model="department.icon"/>
      <div class="department-gallery-name" v-text="department.name"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {PropType} from 'vue';
import {useRouter} from "vue-router";
import ImgSvg from "@/components/general/ImgSvg.vue";

const router = useRouter();

interface Department {
  id: string;
  name: string;
  slug: string;
  icon: string | null;
  is_locked: boolean;
}

defineProps({
  departments: {
    type: Array as PropType<Department[]>,
    required: true,
  },
});


const openDepartment = async (department: Department) => {
  if (!department.is_locked) {
    await router.push({
      name: 'DepartmentPayment',
      params: {slug: department.slug},
    });
  }
};


</script>