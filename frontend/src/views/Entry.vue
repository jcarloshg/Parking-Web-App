<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="logo">
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
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

.entry-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-row {
  display: flex;
  flex-direction: column;
}

.error-message {
  color: #dc3545;
  padding: 0.75rem;
  background-color: #f8d7da;
  border-radius: 4px;
  border: 1px solid #f5c6cb;
}

.submit-button {
  padding: 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-top: 1rem;
}

.submit-button:hover:not(:disabled) {
  background-color: #0056b3;
}

.submit-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.success-container {
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

.success-message {
  color: #666;
  margin: 1.5rem 0;
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
</style>
