<template>
  <div class="vehicle-select">
    <label v-if="label" :for="id">{{ label }}</label>
    <select
      :id="id"
      :value="modelValue"
      :disabled="disabled"
      :data-testid="testId"
      @change="handleChange"
    >
      <option value="" disabled>{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <span v-if="error" class="error">{{ error }}</span>
  </div>
</template>

<script setup lang="ts">
interface VehicleOption {
  value: 'auto' | 'moto' | 'camioneta'
  label: string
}

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
    placeholder: 'Seleccione un tipo',
    disabled: false,
    testId: 'vehicle-select',
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const id = `vehicle-select-${Math.random().toString(36).substring(7)}`

const options: VehicleOption[] = [
  { value: 'auto', label: 'Automóvil' },
  { value: 'moto', label: 'Motocicleta' },
  { value: 'camioneta', label: 'Camioneta' },
]

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  emit('update:modelValue', target.value)
}
</script>

<style scoped>
.vehicle-select {
  display: flex;
  flex-direction: column;
}

label {
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #333;
}

select {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  background-color: white;
  cursor: pointer;
}

select:focus {
  outline: none;
  border-color: #007bff;
}

select:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.error {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}
</style>
