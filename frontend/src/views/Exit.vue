<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="logo">
        <h2>Parking</h2>
      </div>
      <nav class="nav-menu">
        <router-link to="/dashboard" class="nav-item" :class="{ active: $route.path === '/dashboard' }">
          <span class="icon">📊</span>
          Dashboard
        </router-link>
        <router-link to="/entry" class="nav-item" :class="{ active: $route.path === '/entry' }">
          <span class="icon">🚗</span>
          Entrada
        </router-link>
        <router-link to="/exit" class="nav-item" :class="{ active: $route.path === '/exit' }">
          <span class="icon">🚙</span>
          Salida
        </router-link>
        <router-link v-if="canViewReports" to="/reports" class="nav-item" :class="{ active: $route.path === '/reports' }">
          <span class="icon">📈</span>
          Reportes
        </router-link>
        <router-link v-if="isAdmin" to="/admin" class="nav-item" :class="{ active: $route.path === '/admin' }">
          <span class="icon">⚙️</span>
          Admin
        </router-link>
      </nav>
    </aside>

    <div class="main-content">
      <header class="header">
        <h1>Registro de Salida</h1>
        <div class="user-info" v-if="authStore.user">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">{{ userRole }}</span>
          <button @click="handleLogout" class="logout-btn">Cerrar Sesión</button>
        </div>
      </header>

      <main class="content">
        <div class="exit-page">
          <div class="page-header">
            <p class="subtitle">Busque el ticket del vehículo</p>
          </div>

          <div v-if="!ticket" class="search-container">
            <form @submit.prevent="handleSearch" class="search-form">
              <div class="search-row">
                <PlateInput
                  v-model="searchPlate"
                  placeholder="ABC-1234"
                  test-id="search-input"
                />
                <button
                  type="submit"
                  class="search-button"
                  :disabled="loading || !searchPlate"
                  data-testid="search-btn"
                >
                  {{ loading ? 'Buscando...' : 'Buscar' }}
                </button>
              </div>
            </form>

            <div v-if="error" class="error-message">
              {{ error }}
            </div>

            <div v-if="notFound" class="not-found-message">
              No se encontró ningún ticket con esa placa
            </div>
          </div>

          <div v-else-if="!paymentSuccess" class="payment-container">
            <TicketCard
              :ticket="ticket"
              :show-total="true"
              :total="calculation?.total ?? null"
              test-id="found-ticket"
            />

            <div v-if="calculation" class="calculation-details">
              <h3>Detalle del cálculo</h3>
              <div class="detail-row">
                <span>Tiempo:</span>
                <span>{{ calculation.hours }} hora(s)</span>
              </div>
              <div class="detail-row">
                <span>Tarifa:</span>
                <span>${{ (calculation.rate_per_hour || calculation.rate)?.toFixed(2) }}</span>
              </div>
              <div class="detail-row total">
                <span>Total:</span>
                <span>${{ calculation.total.toFixed(2) }}</span>
              </div>
            </div>

            <div v-else class="calculating">
              Calculando tarifa...
            </div>

            <PaymentForm
              v-model="paymentMethod"
              :disabled="paymentLoading"
              :error="paymentError"
              button-text="Pagar y Salir"
              button-test-id="pay-btn"
              @submit="handlePayment"
            />
          </div>

          <div v-else class="receipt-container">
            <div class="success-icon">✓</div>
            <h2>Pago Exitoso</h2>

            <div class="receipt">
              <h3>Comprobante de Pago</h3>
              <div class="receipt-row">
                <span>Ticket #:</span>
                <span>{{ ticket.id }}</span>
              </div>
              <div class="receipt-row">
                <span>Placa:</span>
                <span>{{ ticket.plate_number }}</span>
              </div>
              <div class="receipt-row">
                <span>Entrada:</span>
                <span>{{ formatDateTime(ticket.entry_time) }}</span>
              </div>
              <div class="receipt-row">
                <span>Salida:</span>
                <span>{{ formatDateTime(paymentData?.payment_time || new Date().toISOString()) }}</span>
              </div>
              <div class="receipt-row">
                <span>Método:</span>
                <span>{{ paymentMethod === 'efectivo' ? 'Efectivo' : 'Tarjeta' }}</span>
              </div>
              <div class="receipt-row total">
                <span>Total:</span>
                <span>${{ paymentData?.amount.toFixed(2) || calculation?.total.toFixed(2) }}</span>
              </div>
            </div>

            <button
              type="button"
              class="reset-button"
              @click="resetForm"
            >
              Procesar Otra Salida
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ticketsApi, type Ticket, type TicketCalculation } from '@/api/tickets'
import { paymentsApi, type Payment } from '@/api/payments'
import PlateInput from '@/components/PlateInput.vue'
import TicketCard from '@/components/TicketCard.vue'
import PaymentForm from '@/components/PaymentForm.vue'

const router = useRouter()
const authStore = useAuthStore()

const isAdmin = computed(() => authStore.user?.role === 'admin')
const canViewReports = computed(() => authStore.user?.role === 'admin' || authStore.user?.role === 'supervisor')
const userName = computed(() => authStore.user?.name ?? 'Usuario')
const userRole = computed(() => {
  if (!authStore.user) return ''
  const roles: Record<string, string> = {
    admin: 'Administrador',
    cajero: 'Cajero',
    supervisor: 'Supervisor'
  }
  return roles[authStore.user.role] ?? authStore.user.role
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const searchPlate = ref('')
const loading = ref(false)
const error = ref('')
const notFound = ref(false)

const ticket = ref<Ticket | null>(null)
const calculation = ref<TicketCalculation | null>(null)
const calculating = ref(false)

const paymentMethod = ref<'efectivo' | 'tarjeta' | null>(null)
const paymentLoading = ref(false)
const paymentError = ref('')
const paymentSuccess = ref(false)
const paymentData = ref<Payment | null>(null)

const handleSearch = async () => {
  if (!searchPlate.value) return

  loading.value = true
  error.value = ''
  notFound.value = false

  try {
    const response = await ticketsApi.search(searchPlate.value.toUpperCase())
    if (response.data.data.length === 0) {
      notFound.value = true
      ticket.value = null
    } else {
      const foundTicket = response.data.data[0]
      if (foundTicket) {
        ticket.value = foundTicket
        await loadCalculation()
      }
    }
  } catch (err: unknown) {
    console.error('Error searching ticket:', err)
    const axiosError = err as { response?: { data?: { message?: string } } }
    error.value = axiosError.response?.data?.message || 'Error al buscar el ticket'
  } finally {
    loading.value = false
  }
}

const loadCalculation = async () => {
  if (!ticket.value) return

  calculating.value = true
  try {
    const response = await ticketsApi.calculate(ticket.value.id)
    console.log('Calculation response:', response.data)
    calculation.value = response.data.data
    console.log('Calculation set:', calculation.value)
  } catch (err) {
    console.error('Error calculating fee:', err)
  } finally {
    calculating.value = false
  }
}

const handlePayment = async () => {
  if (!ticket.value || !paymentMethod.value) return

  paymentLoading.value = true
  paymentError.value = ''

  try {
    const response = await paymentsApi.process({
      ticket_id: ticket.value.id,
      payment_method: paymentMethod.value,
    })
    paymentData.value = response.data.data
    paymentSuccess.value = true
  } catch (err: unknown) {
    console.error('Error processing payment:', err)
    const axiosError = err as { response?: { data?: { message?: string } } }
    paymentError.value = axiosError.response?.data?.message || 'Error al procesar el pago'
  } finally {
    paymentLoading.value = false
  }
}

const formatDateTime = (dateStr: string) => {
  const date = new Date(dateStr)
  return date.toLocaleString('es-MX', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const resetForm = () => {
  searchPlate.value = ''
  ticket.value = null
  calculation.value = null
  paymentMethod.value = null
  paymentSuccess.value = false
  paymentData.value = null
  error.value = ''
  notFound.value = false
}
</script>

<style scoped>
.exit-page {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

h1 {
  color: #333;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #666;
}

.search-container {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.search-form {
  margin-bottom: 1rem;
}

.search-row {
  display: flex;
  gap: 1rem;
}

.search-row > :first-child {
  flex: 1;
}

.search-button {
  padding: 0.75rem 1.5rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  white-space: nowrap;
  transition: background-color 0.2s;
}

.search-button:hover:not(:disabled) {
  background-color: #0056b3;
}

.search-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.error-message {
  color: #dc3545;
  padding: 0.75rem;
  background-color: #f8d7da;
  border-radius: 4px;
  border: 1px solid #f5c6cb;
}

.not-found-message {
  color: #856404;
  padding: 0.75rem;
  background-color: #fff3cd;
  border-radius: 4px;
  border: 1px solid #ffeaa7;
  text-align: center;
}

.payment-container {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.calculation-details {
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.calculation-details h3 {
  margin: 0 0 1rem;
  color: #333;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
}

.detail-row.total {
  border-top: 2px solid #007bff;
  font-weight: bold;
  font-size: 1.1rem;
  margin-top: 0.5rem;
  padding-top: 1rem;
}

.calculating {
  text-align: center;
  color: #666;
  padding: 1rem;
}

.receipt-container {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.success-icon {
  width: 60px;
  height: 60px;
  background-color: #28a745;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1rem;
}

h2 {
  color: #28a745;
  margin-bottom: 1.5rem;
}

.receipt {
  border: 2px dashed #ddd;
  border-radius: 8px;
  padding: 1.5rem;
  margin: 1.5rem 0;
  text-align: left;
}

.receipt h3 {
  text-align: center;
  margin: 0 0 1rem;
  color: #333;
}

.receipt-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
}

.receipt-row.total {
  border-top: 2px solid #333;
  font-weight: bold;
  font-size: 1.2rem;
  margin-top: 0.5rem;
  padding-top: 1rem;
}

.reset-button {
  padding: 0.75rem 1.5rem;
  background-color: #6c757d;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.reset-button:hover {
  background-color: #5a6268;
}

.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f3f4f6;
}

.sidebar {
  width: 250px;
  background-color: #1f2937;
  color: white;
  padding: 1rem 0;
}

.sidebar .logo {
  padding: 1rem;
  border-bottom: 1px solid #374151;
}

.sidebar .logo h2 {
  margin: 0;
  color: #60a5fa;
}

.nav-menu {
  padding: 1rem 0;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #d1d5db;
  text-decoration: none;
  transition: background-color 0.2s;
}

.nav-item:hover {
  background-color: #374151;
}

.nav-item.active {
  background-color: #3b82f6;
  color: white;
}

.nav-item .icon {
  margin-right: 0.5rem;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
}

.header h1 {
  margin: 0;
  font-size: 1.5rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-weight: 500;
}

.user-role {
  color: #6b7280;
  font-size: 0.875rem;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.logout-btn:hover {
  background-color: #dc2626;
}

.content {
  flex: 1;
  padding: 2rem;
}

.exit-page {
  max-width: 600px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #333;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #666;
}
</style>
