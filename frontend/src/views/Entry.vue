<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="logo">
        <div class="logo-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="9" y1="3" x2="9" y2="21"></line>
          </svg>
        </div>
        <h2>Parking</h2>
      </div>
      <nav class="nav-menu">
        <router-link
          to="/dashboard"
          class="nav-item"
          :class="{ active: $route.path === '/dashboard' }"
        >
          <span class="icon">📊</span>
          Dashboard
        </router-link>
        <router-link
          to="/entry"
          class="nav-item"
          :class="{ active: $route.path === '/entry' }"
        >
          <span class="icon">🚗</span>
          Entrada
        </router-link>
        <router-link
          to="/exit"
          class="nav-item"
          :class="{ active: $route.path === '/exit' }"
        >
          <span class="icon">🚙</span>
          Salida
        </router-link>
        <router-link
          v-if="canViewReports"
          to="/reports"
          class="nav-item"
          :class="{ active: $route.path === '/reports' }"
        >
          <span class="icon">📈</span>
          Reportes
        </router-link>
        <router-link
          v-if="isAdmin"
          to="/admin"
          class="nav-item"
          :class="{ active: $route.path === '/admin' }"
        >
          <span class="icon">⚙️</span>
          Admin
        </router-link>
      </nav>
    </aside>

    <div class="main-content">
      <header class="header">
        <h1>Registro de Entrada</h1>
        <div class="user-info" v-if="authStore.user">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">{{ userRole }}</span>
          <button @click="handleLogout" class="logout-btn">
            Cerrar Sesión
          </button>
        </div>
      </header>

      <main class="content">
        <div class="entry-page">
          <div class="page-header">
            <h1>Registro de Entrada</h1>
            <p class="subtitle">Ingrese los datos del vehículo</p>
          </div>
          <div v-if="!successTicket" class="entry-form-container">
            <form @submit.prevent="handleSubmit" class="entry-form">
              <div class="form-row">
                <PlateInput
                  v-model="form.plate"
                  label="Placa del vehículo"
                  placeholder="ABC-1234"
                  :error="errors.plate"
                  test-id="plate-input"
                  @blur="validatePlate"
                />
              </div>

              <div class="form-row">
                <VehicleSelect
                  v-model="form.vehicleType"
                  label="Tipo de vehículo"
                  :error="errors.vehicleType"
                  test-id="vehicle-select"
                />
              </div>

              <div class="form-row">
                <ParkingSpaceSelect
                  v-model="form.parkingSpaceId"
                  label="Cajón de estacionamiento"
                  :error="errors.parkingSpaceId"
                  test-id="space-select"
                  :vehicle-type="form.vehicleType"
                />
              </div>

              <div v-if="submitError" class="error-message">
                {{ submitError }}
              </div>

              <button
                type="submit"
                class="submit-button"
                :disabled="loading"
                data-testid="submit-btn"
              >
                {{ loading ? "Registrando..." : "Registrar Entrada" }}
              </button>
            </form>
          </div>

          <div v-else class="success-container">
            <div class="success-icon">✓</div>
            <h2>Entrada Registrada</h2>

            <TicketCard :ticket="successTicket" test-id="created-ticket" />

            <p class="success-message">
              conserve este ticket para realizar el pago al salir.
            </p>

            <button type="button" class="reset-button" @click="resetForm">
              Registrar Otro Vehículo
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { ticketsApi, type Ticket } from "@/api/tickets";
import PlateInput from "@/components/PlateInput.vue";
import VehicleSelect from "@/components/VehicleSelect.vue";
import ParkingSpaceSelect from "@/components/ParkingSpaceSelect.vue";
import TicketCard from "@/components/TicketCard.vue";

const router = useRouter();
const authStore = useAuthStore();

const isAdmin = computed(() => authStore.user?.role === "admin");
const canViewReports = computed(
  () =>
    authStore.user?.role === "admin" || authStore.user?.role === "supervisor",
);
const userName = computed(() => authStore.user?.name ?? "Usuario");
const userRole = computed(() => {
  if (!authStore.user) return "";
  const roles: Record<string, string> = {
    admin: "Administrador",
    cajero: "Cajero",
    supervisor: "Supervisor",
  };
  return roles[authStore.user.role] ?? authStore.user.role;
});

const handleLogout = async () => {
  await authStore.logout();
  router.push("/login");
};

const loading = ref(false);
const submitError = ref("");
const successTicket = ref<Ticket | null>(null);

const form = reactive({
  plate: "",
  vehicleType: "",
  parkingSpaceId: null as number | null,
});

const errors = reactive({
  plate: "",
  vehicleType: "",
  parkingSpaceId: "",
});

const plateRegex = /^[A-Z]{3}-?\d{4}$/;

const validatePlate = () => {
  if (!form.plate) {
    errors.plate = "La placa es requerida";
    return false;
  }
  const cleanPlate = form.plate
    .replace(/-/g, "")
    .substring(0, 7)
    .padStart(7, " ");
  if (!plateRegex.test(cleanPlate)) {
    errors.plate = "Formato de placa inválido (ABC-1234)";
    return false;
  }
  errors.plate = "";
  return true;
};

const validateForm = () => {
  let isValid = true;

  if (!validatePlate()) isValid = false;
  if (!form.vehicleType) {
    errors.vehicleType = "Seleccione un tipo de vehículo";
    isValid = false;
  } else {
    errors.vehicleType = "";
  }
  if (!form.parkingSpaceId) {
    errors.parkingSpaceId = "Seleccione un cajón";
    isValid = false;
  } else {
    errors.parkingSpaceId = "";
  }

  return isValid;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  loading.value = true;
  submitError.value = "";

  try {
    const response = await ticketsApi.create({
      plate_number: form.plate.toUpperCase(),
      vehicle_type: form.vehicleType as "auto" | "moto" | "camioneta",
      parking_space_id: form.parkingSpaceId!,
    });
    successTicket.value = response.data.data;
  } catch (err: unknown) {
    console.error("Error creating ticket:", err);
    const error = err as { response?: { data?: { message?: string } } };
    submitError.value =
      error.response?.data?.message || "Error al registrar la entrada";
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  form.plate = "";
  form.vehicleType = "";
  form.parkingSpaceId = null;
  successTicket.value = null;
  submitError.value = "";
  Object.keys(errors).forEach((key) => {
    (errors as Record<string, string>)[key] = "";
  });
};
</script>

<style scoped>
.entry-page {
  max-width: 600px;
  margin: 0 auto;
}

.entry-form-container {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #1f2937;
  margin-bottom: 0.5rem;
  font-size: 1.75rem;
  font-weight: 700;
}

.subtitle {
  color: #6b7280;
}

.entry-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: flex;
  flex-direction: column;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #dc2626;
  font-size: 0.875rem;
  padding: 0.75rem 1rem;
  background-color: #fef2f2;
  border-radius: 10px;
  border: 1px solid #fecaca;
}

.submit-button {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -10px rgba(102, 126, 234, 0.5);
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.success-container {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  text-align: center;
}

.success-icon {
  width: 60px;
  height: 60px;
  background-color: #10b981;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1rem;
}

h2 {
  color: #10b981;
  margin-bottom: 1.5rem;
}

.success-message {
  color: #6b7280;
  margin: 1.5rem 0;
}

.reset-button {
  padding: 0.75rem 1.5rem;
  background-color: #6b7280;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.reset-button:hover {
  background-color: #4b5563;
}

.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}

.sidebar {
  width: 260px;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem 0;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
}

.sidebar .logo {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar .logo-icon {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar .logo h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.nav-menu {
  padding: 1rem 0;
  margin-top: 1rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.5rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.2s ease;
  margin: 0.25rem 0.75rem;
  border-radius: 10px;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.15);
  color: white;
}

.nav-item.active {
  background-color: white;
  color: #667eea;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.nav-item .icon {
  font-size: 1.25rem;
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
  padding: 1.25rem 2rem;
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-weight: 600;
  color: #1f2937;
}

.user-role {
  color: #667eea;
  font-size: 0.875rem;
  font-weight: 500;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
}

.logout-btn {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.content {
  flex: 1;
  padding: 2rem;
}
</style>
