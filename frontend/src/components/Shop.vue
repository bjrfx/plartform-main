<template>
  <div class="shop">
    <h1>Shop</h1>
    <div v-if="error" class="error">{{ error }}</div>
    <div v-else>
      <div v-for="product in products" :key="product.id" class="product">
        <h3>{{ product.name }}</h3>
        <p>{{ product.description }}</p>
        <p>{{ product.price }}</p>
        <button @click="addToCart(product)">Add to Cart</button>
      </div>
    </div>
    <div class="cart">
      <h2>Cart</h2>
    </div>
  </div>
</template>

<script setup>
import {useShopStore} from '@/stores/shop';
import {computed} from "vue";

const products = computed(() => {
  return [];
});

const error = computed(() => {
  return useShopStore().error;
});

const addToCart = (item) => {
  useShopStore().addToCart(item);
};
</script>

<style>
/* Add styles here */
</style>