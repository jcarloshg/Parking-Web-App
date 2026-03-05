<template>
  <div class="payment-form">
    <h3>Método de Pago</h3>
    
    <div class="payment-methods">
      <label
        v-for="method in paymentMethods"
        :key="method.value"
        class="method-option"
        :class="{ selected: modelValue === method.value }"
      >
        <input
          type="radio"
          :value="method.value"
          :checked="modelValue === method.value"
          :disabled="disabled"
          @change="$emit('update:modelValue', method.value)"
        />
        <span class="method-icon">{{ method.icon }}</span>
        <span class="method-label">{{ method.label }}</span>
      </label>
    </div>
    
    <span v-if="error" class="error">{{ error }}</span>
    
    <button
      type="button"
      class="pay-button"
      :disabled="disabled || !modelValue"
      :data-testid="buttonTestId"
      @click="$emit('submit')"
    >
      {{ buttonText }}
    </button>
  </div>
</template>

<script setup lang="ts">
interface PaymentMethod {
  value: 'efectivo' | 'tarjeta'
  label: string
  icon: string
}

const props = withDefaults(
  defineProps<{
    modelValue?: 'efectivo' | 'tarjeta' | null
    disabled?: boolean
    error?: string
    buttonText?: string
    buttonTestId?: string
  }>(),
  {
    modelValue: null,
    disabled: false,
    buttonText: 'Pagar y Salir',
    buttonTestId: 'pay-btn',
  }
)

defineEmits<{
  'update:modelValue': [value: 'efectivo' | 'tarjeta']
  submit: []
}>()

const paymentMethods: PaymentMethod[] = [
  { value: 'efectivo', label: 'Efectivo', icon: '💵' },
  { value: 'tarjeta', label: 'Tarjeta', icon: '💳' },
]
</script>

<style scoped>
.payment-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

h3 {
  margin: 0;
  color: #333;
}

.payment-methods {
  display: flex;
  gap: 1rem;
}

.method-option {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border: 2px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.method-option:hover {
  border-color: #007bff;
}

.method-option.selected {
  border-color: #007bff;
  background-color: #f0f7ff;
}

.method-option input {
  display: none;
}

.method-icon {
  font-size: 2rem;
}

.method-label {
  font-weight: 500;
}

.error {
  color: #dc3545;
  font-size: 0.875rem;
}

.pay-button {
  padding: 1rem;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.pay-button:hover:not(:disabled) {
  background-color: #218838;
}

.pay-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>
