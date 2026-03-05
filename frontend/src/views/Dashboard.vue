<template>
  <div class="dashboard">
    <header>
      <h1>Dashboard</h1>
      <button @click="handleLogout">Cerrar Sesión</button>
    </header>
    <div class="stats">
      <div class="stat-card">
        <h3>Cajones Disponibles</h3>
        <p>{{ summary?.cajones_disponibles ?? 0 }}</p>
      </div>
      <div class="stat-card">
        <h3>Ingresos del Día</h3>
        <p>${{ summary?.ingresos_dia ?? 0 }}</p>
      </div>
      <div class="stat-card">
        <h3>Tickets Activos</h3>
        <p>{{ summary?.tickets_activos ?? 0 }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'
import api from '@/api'

const { logout } = useAuth()
const summary = ref<{
  cajones_disponibles: number
  ingresos_dia: number
  tickets_activos: number
} | null>(null)

const handleLogout = async () => {
  await logout()
}

onMounted(async () => {
  try {
    const response = await api.get('/reports/summary')
    summary.value = response.data
  } catch (err) {
    console.error('Failed to load summary:', err)
  }
})
</script>

<style scoped>
.dashboard {
  padding: 2rem;
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
button {
  padding: 0.5rem 1rem;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}
.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.stat-card h3 {
  margin: 0 0 0.5rem 0;
  color: #666;
  font-size: 0.875rem;
}
.stat-card p {
  margin: 0;
  font-size: 2rem;
  font-weight: bold;
  color: #333;
}
</style>
