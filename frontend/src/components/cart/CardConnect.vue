<template>
  <div id="card-connect-iframe" v-if="src.length > 0">
    <div>
      <iframe :src="urlString"></iframe>
    </div>

    <div v-if="isCardFailed" class="card-connect-iframe-error">
      <span v-text="modelValue?.error"></span>
      <span>Please ensure all card details are entered correctly.</span>
    </div>

  </div>
</template>

<script setup lang="ts">
//Doc: https://developer.fiserv.com/product/CardPointe/docs/?path=docs/documentation/HostediFrameTokenizer.md&branch=main
// Test card: https://developer.fiserv.com/product/CardPointe/docs/?path=docs/documentation/CardPointeGatewayDeveloperGuides.md&branch=main#rpct-responses
import {computed, onMounted, onUnmounted, ref} from 'vue';
import {defineProps, defineEmits} from 'vue';

const props = defineProps({
  src: {
    type: String,
    required: true,
  },
  modelValue: {
    type: Object as () => { token: string; expiry: string, error: string } | null,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const isCardFailed = ref(false);


function handleToken(event: MessageEvent) {
  if (!event || typeof event.data !== 'string') {
    return;
  }
  try {
    const response = JSON.parse(event.data);
    if (!response) {
      return;
    }
    if ('message' in response && response.message) {
      emit('update:modelValue', {
        token: response.message || '',
        expiry: response.expiry || '',
        error: '',
      });
    } else if ('errorMessage' in response && response.errorMessage) {
      const errorMessage = response.errorMessage.trim();
      emit('update:modelValue', {
        token: '',
        expiry: '',
        error: errorMessage.endsWith('.') ? errorMessage : `${errorMessage}.`,
      });
      showCardError();
    }
  } catch (err) {
    console.error('Error parsing message event data:', err);
  }
}

const showCardError = (): void => {
  isCardFailed.value = true;
  setTimeout(() => {
    isCardFailed.value = false;
  }, 8000);
};


const urlString = computed(() => {
  if (!props.src) {
    console.error('The src prop is not defined');
    return '';
  }


  let params = '';
  //include Expiration Date and CVV fields
  params += "useexpiry=true";
  params += "&usecvv=true";
  params += "&cvvlabel=Security Code";

  //Mobile
  params += "&fullmobilekeyboard=true";
  params += "&tokenizewheninactive=true";

  //Error
  params += "&enhancedresponse=true";
  //Auto Tokenized
  params += "&tokenizewheninactive=true";
  params += "&inactivityto=1000";

  //Expiration label
  params += "&expirylabel=Expiration:";
  //expiry month field title
  params += "&expirymonthtitle=Exp Month";
  //expiry Year field title
  params += "&expiryyeartitle=Exp Year";
  params += "&invalidinputevent=true";

  //Card number input
  params += "&cardinputmaxlength=19";
  params += "&formatinput=true";
  params += "&placeholder=1234 1234 1234 1234";

  //card CVV input
  params += "&placeholdercvv=123";

  //Controls how long (in milliseconds) to ignore input to a newly selected field after changing focus.
  params += "&selectinputdelay=100";

  //CardSecure generates a unique token for each tokenization of a given card number.
  params += "&unique=true";


  // Define the CSS with readable formatting
  let css = `
    .error {
        color: red;
    }
    input {
        outline: none;
        padding-left: 12px;
        font-weight: 400;
        font-size: 14px;
        line-height: 21px;
        font-family: 'Inter', sans-serif;
        border-radius: 6px;
        height: 45px;
        border: none;
        background: rgba(245, 248, 250, 1);
    }
    select {
        outline: none;
        padding: 14px;
        font-weight: 400;
        font-size: 14px;
        line-height: 21px;
        font-family: 'Inter', sans-serif;
        border-radius: 6px;
        border: none;
        height: 45px;
        width: 31%;
        background: rgba(245, 248, 250, 1);
    }
    label {
        margin-bottom: 5px;
        margin-top: 10px;
        height: 21px;
        display: inline-block;
        word-spacing: 7px;
        font-family: 'Inter', sans-serif;
        font-style: normal;
        font-weight: 500;
        font-size: 16px;
        line-height: 21px;
        color: rgba(24, 28, 50, 1);
    }
    `;

// Remove all spaces, newlines, and tabs from the CSS
  css = css.replace(/\s+/g, '');

// Encode the CSS
  params += "&" + encodeURI(`css=${css}`);

  const url = props.src.replace('?', '');
  return `${url}?${params.toString()}`;
});

onMounted(() => {
  window.addEventListener('message', handleToken, false);
});

onUnmounted(() => {
  window.removeEventListener('message', handleToken, false);
});
</script>


<style scoped>
#card-connect-iframe iframe {
  width: 100%;
  height: 280px;
}
</style>