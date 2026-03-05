<template>
  <div class="ticket-card" :data-testid="testId">
    <div class="ticket-header">
      <span class="ticket-number">Ticket #{{ ticket.id }}</span>
      <span class="ticket-status" :class="ticket.status">{{ ticket.status }}</span>
    </div>
    
    <div class="ticket-body">
      <div class="ticket-row">
        <span class="label">Placa:</span>
        <span class="value plate">{{ ticket.plate_number }}</span>
      </div>
      
      <div class="ticket-row">
        <span class="label">Tipo:</span>
        <span class="value">{{ vehicleTypeLabel }}</span>
      </div>
      
      <div class="ticket-row">
        <span class="label">Entrada:</span>
        <span class="value">{{ formatDateTime(ticket.entry_time) }}</span>
      </div>
      
      <div v-if="ticket.parking_space" class="ticket-row">
        <span class="label">Cajón:</span>
        <span class="value">{{ ticket.parking_space.number }}</span>
      </div>
      
      <div v-if="elapsedTime" class="ticket-row">
        <span class="label">Tiempo:</span>
        <span class="value">{{ elapsedTime }}</span>
      </div>
    </div>
    
    <div v-if="showTotal && total !== null" class="ticket-total">
      <span class="total-label">Total a pagar:</span>
      <span class="total-value">${{ total.toFixed(2) }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Ticket } from '@/api/tickets'

const props = withDefaults(
  defineProps<{
    ticket: Ticket
    showTotal?: boolean
    total?: number | null
    testId?: string
  }>(),
  {
    showTotal: false,
    total: null,
    testId: 'ticket-card',
  }
)

const vehicleTypeLabels: Record<string, string> = {
  auto: 'Automóvil',
  moto: 'Motocicleta',
  camioneta: 'Camioneta',
}

const vehicleTypeLabel = computed(() => {
  return vehicleTypeLabels[props.ticket.vehicle_type] || props.ticket.vehicle_type
})

const formatDateTime = (dateStr: string | undefined) => {
  if (!dateStr) return '-'
  const normalized = dateStr.replace(/\.(\d{6})\d*Z$/, '.$1Z')
  const date = new Date(normalized)
  if (isNaN(date.getTime())) return 'Invalid Date'
  return date.toLocaleString('es-MX', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const elapsedTime = computed(() => {
  const normalized = props.ticket.entry_time.replace(/\.(\d{6})\d*Z$/, '.$1Z')
  const entry = new Date(normalized)
  if (isNaN(entry.getTime())) return null
  const now = new Date()
  const diffMs = now.getTime() - entry.getTime()
  
  const hours = Math.floor(diffMs / (1000 * 60 * 60))
  const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60))
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
})
</script>

<style scoped>
.ticket-card {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #eee;
}

.ticket-number {
  font-weight: bold;
  font-size: 1.1rem;
}

.ticket-status {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: 500;
}

.ticket-status.activo {
  background-color: #d4edda;
  color: #155724;
}

.ticket-status.pagado {
  background-color: #cce5ff;
  color: #004085;
}

.ticket-status.cancelado {
  background-color: #f8d7da;
  color: #721c24;
}

.ticket-body {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ticket-row {
  display: flex;
  justify-content: space-between;
}

.label {
  color: #666;
}

.value {
  font-weight: 500;
}

.value.plate {
  text-transform: uppercase;
  letter-spacing: 1px;
}

.ticket-total {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid #007bff;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-label {
  font-size: 1.1rem;
  font-weight: 500;
}

.total-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #007bff;
}
</style>
