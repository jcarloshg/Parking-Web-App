<template>
  <div class="plate-input">
    <label v-if="label" :for="id">{{ label }}</label>
    <input
      :id="id"
      ref="inputRef"
      v-model="internalValue"
      type="text"
      :placeholder="placeholder"
      :disabled="disabled"
      :data-testid="testId"
      @input="handleInput"
      @blur="$emit('blur', $event)"
    />
    <span v-if="error" class="error">{{ error }}</span>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue?: string
    label?: string
    placeholder?: string
    disabled?: boolean
    error?: string
    testId?: string
  }>(),
  {
    modelValue: '',
    placeholder: 'ABC-1234',
    disabled: false,
    testId: 'plate-input',
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
  blur: [event: FocusEvent]
}>()

const id = `plate-input-${Math.random().toString(36).substring(7)}`
const inputRef = ref<HTMLInputElement | null>(null)

const internalValue = ref(props.modelValue)

watch(
  () => props.modelValue,
  (newVal) => {
    internalValue.value = newVal
  }
)

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  const upperValue = target.value.toUpperCase()
  internalValue.value = upperValue
  emit('update:modelValue', upperValue)
}

const focus = () => {
  inputRef.value?.focus()
}

defineExpose({ focus })
</script>

<style scoped>
.plate-input {
  display: flex;
  flex-direction: column;
}

label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #333;
}

input {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

input:focus {
  outline: none;
  border-color: #007bff;
}

input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.error {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}
</style>
