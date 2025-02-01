<template>
  <div class="password-rules-container">
    <div class="password-rules">
      <div v-for="(status, rule) in ruleStatus" :key="rule" class="rule-item">
        <span :class="{'rule-passed': status, 'rule-failed': !status}">
          {{ ruleDescriptions[rule] }}
        </span>
      </div>
    </div>

    <div v-if="requireConfirmation && confirmPassword">
      <div v-if="confirmPassword !== password" class="error-message">
        Passwords do not match
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, onMounted, watch} from "vue";
import {useAuthStore} from "@/stores/auth";

const authStore = useAuthStore();

const props = defineProps({
  password: {
    type: [String, null],
    required: true,
  },
  confirmPassword: {
    type: [String, null],
    required: false,
    default: '',
  },
  requireConfirmation: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: Boolean,
    required: true,
  }
});

const emit = defineEmits(['update:modelValue']);

// Reactive states
const rules = ref<Record<string, boolean | number>>({});
const ruleStatus = ref<Record<string, boolean>>({});

// Default rule descriptions
const ruleDescriptions: Record<string, string> = {
  min: "Minimum length of 8 characters",
  mixedCase: "Includes uppercase and lowercase letters",
  numbers: "Contains at least one number",
  symbols: "Contains at least one special character",
};

// Fetch password rules
onMounted(async () => {
  const fetchedRules = await authStore.passwordRules();
  rules.value = fetchedRules || {};
  Object.keys(ruleDescriptions).forEach(rule => {
    ruleStatus.value[rule] = false;
  });
  validatePassword();
});

// Validate password based on rules
const validatePassword = () => {
  // Type guard: ensure 'min' is a number, otherwise default to 8
  const minLength = Number(rules.value.min) ?? 8;

  const password = String(props.password);
  ruleStatus.value = {
    min: password.length >= minLength,
    mixedCase: /[a-z]/.test(password) && /[A-Z]/.test(password),
    numbers: /\d/.test(password),
    symbols: /[^A-Za-z0-9]/.test(password),
  };

  const allRulesPass = Object.values(ruleStatus.value).every(Boolean);
  const passwordsMatch = !props.requireConfirmation || props.password === props.confirmPassword;

  // Emit the boolean directly through v-model
  emit('update:modelValue', allRulesPass && passwordsMatch);
};

// Revalidate when password changes
watch(() => [props.password, props.confirmPassword], validatePassword);
</script>

<style scoped>
.password-rules-container {
  margin: 20px 0;
}

.password-rules {
  margin-top: 10px;
}

.rule-item {
  margin: 5px 0;
}

.rule-passed {
  color: #2ecc71;
}

.rule-failed {
  color: #e74c3c;
}

.error-message {
  color: #e74c3c;
  font-size: 0.9em;
  margin-top: 5px;
}
</style>