<template>
  <aside class="sidebar sidebar-cart">
    <div class="sidebar-cart-header">Cart Items</div>
    <div class="sidebar-cart-alert" v-if="triggerItemAdded">Bill Added...</div>
    <ul class="sidebar-cart-body">
      <li v-for="(item, index) in sortedCartItems" :key="index">
        <!-- Department Header -->
        <div v-if="showDepartmentLabel(index, item)" class="sidebar-cart-department">
          <ImgSvg v-model="item.department_icon"/>
          <span>{{ item.department_name }}</span>
        </div>
        <!-- Sub-Department -->
        <div v-if="item.sub_department_id" class="sidebar-cart-department-sub">
          <span class="sidebar-cart-department-sub-label">Sub Department:</span>
          <span class="sidebar-cart-department-sub-value">{{ item.sub_department_name }}</span>
        </div>
        <!-- Bill Details -->
        <div class="sidebar-cart-department-bill">
          <span class="sidebar-cart-department-bill-label">Bill:</span>
          <span class="sidebar-cart-department-bill-value">{{ item.account_reference }}</span>
        </div>
        <!-- Amount -->
        <div class="sidebar-cart-department-amount">
          <span class="sidebar-cart-department-amount-label">Amount:</span>
          <span class="sidebar-cart-department-amount-value">${{ formatAmount(item.amount) }}</span>
        </div>
        <!-- Remove Button -->
        <div class="sidebar-cart-department-remove">
          <button @click="removeFromCart(item)">Remove</button>
        </div>
        <!-- Subtotal -->
        <div v-if="showDepartmentLabel(index, item)" class="sidebar-cart-department-total">
          <div class="sidebar-cart-department-fee">
            <span class="sidebar-cart-department-fee-label">ReCo Service Fee:</span>
            <span class="sidebar-cart-department-fee-value" v-if="showServiceFees">
            ${{ calcFee(item.department_id) }}
          </span>
          </div>
          <div class="sidebar-cart-department-total">
            <span class="sidebar-cart-department-total-label">Sub Total:</span>
            <span class="sidebar-cart-department-total-value">${{ calcSubTotal(item.department_id) }}</span>
          </div>
        </div>
      </li>
    </ul>
    <!-- Footer -->
    <div class="sidebar-cart-footer" v-if="sortedCartItems.length > 0">
      <div class="sidebar-cart-footer-total">
        <span class="sidebar-cart-footer-label" v-if="showServiceFees">Total:</span>
        <span class="sidebar-cart-footer-label" v-else>Estimated Total:</span>
        <span class="sidebar-cart-footer-total-value">${{ calcTotal() }}</span>
      </div>
      <div class="sidebar-cart-footer-pay">
        <button type="button" v-if="!checkout" @click="openCheckout()">Go To Checkout</button>
      </div>
    </div>
  </aside>
</template>
<script setup lang="ts">
import {computed, defineEmits, defineProps, onMounted} from "vue";
import {useShopStore} from "@/stores/shop";
import type {Bill} from "@/stores/shop";
import ImgSvg from "@/components/general/ImgSvg.vue";
import {useRouter} from "vue-router";

// Access the store
const shopStore = useShopStore();
const router = useRouter();

const props = defineProps({
  checkout: {
    type: Boolean,
    required: false,
  },
  modelValue: {
    type: Object as () => Bill[] | null,
    default: null,
    required: false,
  },
});

const emit = defineEmits(['update:modelValue']);

// Remove item from the cart
const removeFromCart = async (item: Bill) => {
  await shopStore.removeFromCart(item);
  emit('update:modelValue', shopStore.cart)
};

// Trigger alert when an item is added
const triggerItemAdded = computed(() => shopStore.triggerItemAdded);

// Sort the cart items by department
const sortedCartItems = computed(() =>
    shopStore.cart.slice().sort((a, b) => a.department_id.localeCompare(b.department_id))
);

// Show department label if it's the first item or the department changes
const showDepartmentLabel = (index: number, item: Bill) => {
  return (
      index === 0 ||
      (
          'departmentId' in item &&
          item.departmentId !== sortedCartItems.value[index - 1]?.department_id
      )
  );
};

//If there is fee data
const showServiceFees = computed(() => {
  return props.checkout && shopStore.fees.length > 0;
})

// Calculate the total
const calcTotal = (): string => {
  const total = shopStore.getCartTotal();

  const fees = shopStore.getFeesTotal();

  return formatAmount(total + fees);
};

//Calculate the subtotal for a department
const calcSubTotal = (departmentId: string): string => {
  const total = shopStore.getCartSubTotal(departmentId);

  return formatAmount(total);
};

//Calculate the Fee for a department
const calcFee = (departmentId: string): string | null => {
  const fee = shopStore.getFee(departmentId);
  return fee === null ? null : formatAmount(fee);
}

// Format amount to 2 decimal places
const formatAmount = (val: number | string): string => {
  return parseFloat(val as string).toFixed(2);
};

const openCheckout = () => {
  router.push({
    name: 'Checkout',
    params: {},
  });
};

// Load the cart on mount
onMounted(() => {
  shopStore.loadCart();
  emit('update:modelValue', shopStore?.cart)
});
</script>
