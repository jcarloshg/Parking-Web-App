<template>
  <div class="parking-space-select">
    <label v-if="label" :for="id">{{ label }}</label>
    <select
      :id="id"
      :value="modelValue"
      :disabled="disabled || loading"
      :data-testid="testId"
      @change="handleChange"
    >
      <option v-if="loading" value="" disabled>Cargando...</option>
      <option v-else-if="!options.length" value="" disabled>No hay cajones disponibles</option>
      <option v-else value="" disabled>{{ placeholder }}</option>
      <option v-for="option in options" :key="option.id" :value="option.id">
        {{ option.number }} ({{ getTypeLabel(option.type) }})
      </option>
    </select>
    <span v-if="error" class="error">{{ error }}</span>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { parkingApi, type ParkingSpace } from '@/api/parking'

const props = withDefaults(
  defineProps<{
    modelValue?: number | null
    label?: string
    placeholder?: string
    disabled?: boolean
    error?: string
    testId?: string
    vehicleType?: string
  }>(),
  {
    modelValue: null,
    placeholder: 'Seleccione un cajón',
    disabled: false,
    testId: 'space-select',
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
}>()

const id = `parking-space-select-${Math.random().toString(36).substring(7)}`
const options = ref<ParkingSpace[]>([])
const loading = ref(false)

const typeLabels: Record<string, string> = {
  general: 'General',
  discapacitado: 'Discapacitado',
  eléctrico: 'Eléctrico',
}

const getTypeLabel = (type: string) => typeLabels[type] || type

const loadSpaces = async () => {
  loading.value = true
  try {
    const response = await parkingApi.getAvailable()
    options.value = response.data.data
  } catch (err) {
    console.error('Error loading parking spaces:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSpaces()
})

watch(() => props.vehicleType, () => {
  loadSpaces()
})

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  const value = target.value ? parseInt(target.value) : null
  emit('update:modelValue', value)
}
</script>

<style scoped>
.parking-space-select {
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
