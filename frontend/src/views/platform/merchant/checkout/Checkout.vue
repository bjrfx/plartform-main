<template>
  <div class="content">
    <h1>Checkout</h1>
    <!-- Payer info -->
    <form @submit.prevent="submitCart">
      <div>

        <!-- User Information -->
        <div>
          <FormTextInput
              id="first_name"
              label="First Name"
              v-model="payer.first_name"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="middle_name"
              label="Middle Name"
              v-model="payer.middle_name"
          />
        </div>
        <div>
          <FormTextInput
              id="last_name"
              label="Last Name"
              v-model="payer.last_name"
              :required="true"
          />
        </div>

        <!-- Contact Information -->
        <div>
          <FormEmailInput
              id="email"
              label="Email"
              v-model="payer.email"
              :required="true"
          />
        </div>
        <div>
          <FormPhoneCodeInput
              id="phone_country_code"
              v-model="payer.phone_country_code"
              :required="true"
          />
          <FormPhoneInput
              id="phone"
              label="Phone"
              v-model="payer.phone"
              :required="true"
          />
        </div>

        <!-- Address -->
        <div>
          <FormTextInput
              id="address"
              label="Address"
              v-model="payer.address"
              :required="true"
          />
        </div>

        <!-- Address -->
        <div>
          <FormTextInput
              id="address2"
              label="Address 2"
              v-model="payer.address2"
              :required="true"
          />
        </div>
        <div>
          <FormTextInput
              id="city"
              label="City"
              v-model="payer.city"
              :required="true"
          />
        </div>
        <div>
          <FormStateSelect
              id="state"
              label="State"
              v-model="payer.state"
              :required="true"
          />
        </div>
        <div>
          <FormZipCodeInput
              id="zip_code"
              label="Zip Code"
              v-model="payer.zip_code"
              :required="true"
          />
        </div>

      </div>
      <!-- payment -->
      <div>
        <div @click="setSelMethod( 'CARD')" :class="{active: selMethod === 'CARD'}">Credit/Debit</div>
        <div @click="setSelMethod('CHECK')" :class="{active: selMethod === 'CHECK'}">eCheck</div>
        {{ selMethod }}
      </div>
      <!-- card -->
      <div v-if="selMethod === 'CARD'">
        <div>
          <CardConnect :src="iFrameSrc" v-model="cardMessage" :key="cardKey"/>
          <div v-if="showCardOwner">
            <FormTextInput
                id="card_owner"
                label="Name (as shown on card)"
                placeholder="Name (as shown on card)"
                v-model="payer.card_owner"
                :required="true"
            />
          </div>
        </div>
      </div>
      <!-- e-check -->
      <div v-if="selMethod === 'CHECK'">
        <Paya v-model="checkForm" :key="checkKey"/>
      </div>
      <!-- terms_and_conditions -->
      <div>
        <label>
          <input type="checkbox" v-model="payer.terms_and_conditions" value="1">
          <span>I authorized </span>
        </label>
      </div>
      <!-- pay -->
      <div v-if="canProcessPayment && payer.terms_and_conditions">
        <button type="submit">Pay</button>
      </div>
    </form>
  </div>

  <SidebarCart :checkout="true" :key="cartKey" v-model="cart"/>
</template>

<script setup lang="ts">
import {computed, onMounted, ref, watch} from 'vue';
import {post} from "@/services/api";
import SidebarCart from "@/components/cart/SidebarCart.vue";
import {useShopStore} from "@/stores/shop";
import CardConnect from "@/components/cart/CardConnect.vue";
import {useRouter} from "vue-router";
import FormStateSelect from "@/components/forms/FormStateSelect.vue";
import FormPhoneInput from "@/components/forms/FormPhoneInput.vue";
import FormPhoneCodeInput from "@/components/forms/FormPhoneCodeInput.vue";
import FormTextInput from "@/components/forms/FormTextInput.vue";
import FormEmailInput from "@/components/forms/FormEmailInput.vue";
import FormZipCodeInput from "@/components/forms/FormZipCodeInput.vue";
import Paya, {PayaForm} from "@/components/cart/Paya.vue";

const router = useRouter();
const shopStore = useShopStore();
const payer = ref({
  first_name: '',
  middle_name: null,
  last_name: '',
  email: '',
  city: '',
  address: '',
  address2: '',
  state: '',
  zip_code: '',
  phone: '',
  phone_country_code: '',
  card_owner: '',
  terms_and_conditions: 0,
});

const cart = ref(null);
const cartKey = ref(Symbol()); // A Symbol in JavaScript is a unique and immutable value
const cardKey = ref(Symbol()); // A Symbol in JavaScript is a unique and immutable value
const checkKey = ref(Symbol()); // A Symbol in JavaScript is a unique and immutable value


const selMethod = ref('CARD');
const cardMessage = ref<{ token: string; expiry: string, error: string }>();
const checkForm = ref<PayaForm | null>(null);

const setSelMethod = (method: string) => {
  selMethod.value = method;
}

watch(
    () => cart.value,
    (newVal) => {
      if (newVal) {
        cartUpdated();
      }
    },
    {immediate: true, deep: true}
);


watch(
    () => cardMessage.value,
    (newVal) => {
      if (!newVal || selMethod.value !== 'CARD') {
        return;
      }
      canProcessPayment.value = false;
      if (shopStore.fees.length > 0) {
        //reset fees on card fail
        shopStore.setFees([]);
      }
      if ('token' in newVal && newVal.token.length > 0) {
        getCardFees();
      }
    },
    {immediate: true, deep: true}
);

watch(
    () => checkForm.value,
    (newVal) => {
      if (!newVal || selMethod.value !== 'CHECK') {
        return;
      }
      canProcessPayment.value = false;
      if (shopStore.fees.length > 0) {
        //reset fees on card fail
        shopStore.setFees([]);
      }
      const failCheckForm = Object.values(newVal).some((field) => field.length === 0);
      if (!failCheckForm) {
        getCheckFees();
      }
    },
    {immediate: true, deep: true}
);

const cartUpdated = (): void => {
  //Reload the card iFrame
  cardKey.value = Symbol();
  //reset the fees
  shopStore.setFees([]);
  canProcessPayment.value = false;

  if (shopStore.cart.length === 0) {
    //If no items in the cart
    router.replace({
      name: 'DepartmentsAndCart',
      params: {},
    });
  }
};

const getFilteredCart = (): string[] => {
  return shopStore.cart.map(({department_id}) => department_id);
};

// Reactive merchants list with explicit typing
const iFrameSrc = ref('');
const canProcessPayment = ref(false);
const cardType = ref(null);

const showCardOwner = computed(() => {
  return cardType.value === 'credit';
})

const getCardFees = async () => {
  try {
    const response = await post(`merchants/card-web/fees`, {
      cart: shopStore.cart,
      token: cardMessage.value,
    });
    if ('fees' in response.data) {
      shopStore.setFees(response.data.fees);
    }
    if ('type' in response.data) {
      cardType.value = response.data.type;
    }
    canProcessPayment.value = true;
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
}
const getCheckFees = async () => {
  try {
    const response = await post(`merchants/check/fees`, {
      cart: shopStore.cart,
    });
    if ('fees' in response.data) {
      shopStore.setFees(response.data.fees);
    }
    if ('type' in response.data) {
      cardType.value = response.data.type;
    }
    canProcessPayment.value = true;
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
}

const submitCart = async () => {
  if (!canProcessPayment.value || payer.value.terms_and_conditions < 1) {
    return false;
  }
  cardKey.value = Symbol();//reset the credit iFrame
  try {
    const response = await post(`merchants/checkout`, {
      payer: payer.value,
      cart: shopStore.cart,
      fees: shopStore.fees,
      token: cardMessage.value,
      type: cardType.value,
      ach: checkForm.value,
    });
    console.log(response.data);
    shopStore.setTransactions(response.data);

    transactionSummary();
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
}

const getCardGateway = async () => {
  try {
    const response = await post(`merchants/card-web/gateway`, {department_ids: getFilteredCart()});
    if ('src' in response) {
      iFrameSrc.value = response.src ?? '';
    }
  } catch (error) {
    console.error("Failed to fetch:", error);
  }
};

const transactionSummary = () => {
  //alert('go to pay summary');
  return;
  /*
  router.push({
    name: 'Checkout',
    params: {},
  });

   */
};

onMounted(() => {
  shopStore.setFees([]);
  shopStore.setTransactions([])
  shopStore.loadCart();
  getCardGateway();
});
</script>