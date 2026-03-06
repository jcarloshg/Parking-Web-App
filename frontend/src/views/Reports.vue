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
        <h1>Reportes</h1>
        <div class="user-info" v-if="authStore.user">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">{{ userRole }}</span>
          <button @click="handleLogout" class="logout-btn">
            Cerrar Sesión
          </button>
        </div>
      </header>

      <main class="content">
        <div v-if="!canViewReports" class="access-denied">
          <h2>Acceso Denegado</h2>
          <p>No tienes permiso para ver los reportes.</p>
          <p>
            Solo los administradores y supervisores pueden acceder a esta
            sección.
          </p>
          <router-link to="/dashboard" class="back-link"
            >Volver al Dashboard</router-link
          >
        </div>

        <div v-else class="reports-page">
          <div class="tabs">
            <button
              class="tab"
              :class="{ active: activeTab === 'daily' }"
              @click="activeTab = 'daily'"
            >
              Reporte Diario
            </button>
            <button
              class="tab"
              :class="{ active: activeTab === 'monthly' }"
              @click="activeTab = 'monthly'"
            >
              Reporte Mensual
            </button>
          </div>

          <div v-if="activeTab === 'daily'" class="tab-content">
            <div class="filters">
              <label>
                Fecha:
                <input
                  type="date"
                  v-model="dailyDate"
                  @change="loadDailyReport"
                />
              </label>
            </div>

            <div v-if="dailyLoading" class="loading">Cargando...</div>
            <div v-else-if="dailyError" class="error">{{ dailyError }}</div>
            <div v-else-if="!dailyReport" class="no-data">Selecciona una fecha para ver el reporte</div>
            <div v-else-if="dailyReport" class="report-content">
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="stat-icon">💰</div>
                  <div class="stat-info">
                    <h3>Total Ingresos</h3>
                    <p class="stat-value">
                      ${{ formatNumber(dailyReport.total_ingresos) }}
                    </p>
                  </div>
                </div>
                <div class="stat-card">
                  <div class="stat-icon">🎫</div>
                  <div class="stat-info">
                    <h3>Tickets Atendidos</h3>
                    <p class="stat-value">
                      {{ dailyReport.tickets_atendidos }}
                    </p>
                  </div>
                </div>
                <div class="stat-card">
                  <div class="stat-icon">📊</div>
                  <div class="stat-info">
                    <h3>Promedio por Ticket</h3>
                    <p class="stat-value">
                      ${{ formatNumber(dailyReport.promedio_por_ticket) }}
                    </p>
                  </div>
                </div>
                <div class="stat-card">
                  <div class="stat-icon">🅿️</div>
                  <div class="stat-info">
                    <h3>Cajones Disponibles</h3>
                    <p class="stat-value">
                      {{ dailyReport.cajones_disponibles }}
                    </p>
                  </div>
                </div>
              </div>
              <div
                v-if="
                  dailyReport.tipos_vehiculo &&
                  Object.keys(dailyReport.tipos_vehiculo).length
                "
                class="chart-container"
              >
                <h3>Tipo de Vehículo</h3>
                <Pie :data="dailyVehicleChartData" :options="pieOptions" />
              </div>
              <div
                v-if="dailyReport.tickets && dailyReport.tickets.length"
                class="tickets-table"
              >
                <h3>Detalle de Tickets</h3>
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Placa</th>
                      <th>Tipo</th>
                      <th>Cajón</th>
                      <th>Entrada</th>
                      <th>Salida</th>
                      <th>Total</th>
                      <th>Método</th>
                      <th>Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ticket in dailyReport.tickets" :key="ticket.id">
                      <td>#{{ ticket.id }}</td>
                      <td>{{ ticket.plate_number }}</td>
                      <td>{{ getVehicleName(ticket.vehicle_type) }}</td>
                      <td>{{ ticket.parking_space }}</td>
                      <td>{{ formatDateTime(ticket.entry_time) }}</td>
                      <td>
                        {{
                          ticket.exit_time
                            ? formatDateTime(ticket.exit_time)
                            : "-"
                        }}
                      </td>
                      <td>
                        {{
                          ticket.total ? "$" + formatNumber(ticket.total) : "-"
                        }}
                      </td>
                      <td>{{ ticket.payment_method || "-" }}</td>
                      <td>
                        <span :class="['status-badge', ticket.status]">
                          {{
                            ticket.status === "activo" ? "Activo" : "Finalizado"
                          }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div v-if="activeTab === 'monthly'" class="tab-content">
            <div class="filters">
              <label>
                Año:
                <select v-model="monthlyYear" @change="loadMonthlyReport">
                  <option
                    v-for="year in availableYears"
                    :key="year"
                    :value="year"
                  >
                    {{ year }}
                  </option>
                </select>
              </label>
              <label>
                Mes:
                <select v-model="monthlyMonth" @change="loadMonthlyReport">
                  <option
                    v-for="(name, index) in months"
                    :key="index + 1"
                    :value="index + 1"
                  >
                    {{ name }}
                  </option>
                </select>
              </label>
            </div>

            <div v-if="monthlyLoading" class="loading">Cargando...</div>
            <div v-else-if="monthlyError" class="error">{{ monthlyError }}</div>
            <div v-else-if="monthlyReport" class="report-content">
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="stat-icon">💰</div>
                  <div class="stat-info">
                    <h3>Ingresos del Mes</h3>
                    <p class="stat-value">
                      ${{ formatNumber(monthlyReport.total_ingresos_mes) }}
                    </p>
                  </div>
                </div>
                <div class="stat-card">
                  <div class="stat-icon">🎫</div>
                  <div class="stat-info">
                    <h3>Total Tickets</h3>
                    <p class="stat-value">
                      {{ monthlyReport.total_tickets_mes }}
                    </p>
                  </div>
                </div>
                <div class="stat-card">
                  <div class="stat-icon">⏰</div>
                  <div class="stat-info">
                    <h3>Hora Pico</h3>
                    <p class="stat-value">
                      {{
                        monthlyReport.hora_pica
                          ? `${monthlyReport.hora_pica}:00`
                          : "-"
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="charts-grid">
                <div class="chart-container">
                  <h3>Ingresos por Día</h3>
                  <Bar
                    v-if="monthlyChartData.labels.length"
                    :data="monthlyChartData"
                    :options="barOptions"
                  />
                  <p v-else class="no-data">No hay datos disponibles</p>
                </div>

                <div class="chart-container">
                  <h3>Tipo de Vehículo</h3>
                  <Pie
                    v-if="
                      Object.keys(monthlyReport.tipo_vehiculo_frecuente || {})
                        .length
                    "
                    :data="vehicleChartData"
                    :options="pieOptions"
                  />
                  <p v-else class="no-data">No hay datos disponibles</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { reportsApi } from "@/api/parking";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from "chart.js";
import { Bar, Pie } from "vue-chartjs";

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
);

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

const activeTab = ref<"daily" | "monthly">("daily");

const dailyDate = ref(new Date().toISOString().split('T')[0]);
const dailyLoading = ref(false);
const dailyError = ref("");
const dailyReport = ref<any>(null);

const monthlyYear = ref(new Date().getFullYear());
const monthlyMonth = ref(new Date().getMonth() + 1);
const monthlyLoading = ref(false);
const monthlyError = ref("");
const monthlyReport = ref<any>(null);

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear();
  return [currentYear - 1, currentYear, currentYear + 1];
});

const months = [
  "Enero",
  "Febrero",
  "Marzo",
  "Abril",
  "Mayo",
  "Junio",
  "Julio",
  "Agosto",
  "Septiembre",
  "Octubre",
  "Noviembre",
  "Diciembre",
];

const formatNumber = (num: number) => {
  return (
    num?.toLocaleString("es-MX", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }) ?? "0.00"
  );
};

const formatDateTime = (dateStr: string) => {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  return date.toLocaleString("es-MX", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getVehicleName = (type: string) => {
  const names: Record<string, string> = {
    auto: "Auto",
    moto: "Moto",
    camioneta: "Camioneta",
  };
  return names[type] || type;
};

const loadDailyReport = async () => {
  dailyLoading.value = true;
  dailyError.value = "";
  try {
    const response = await reportsApi.getDaily(dailyDate.value);
    dailyReport.value = response.data;
  } catch (err: any) {
    dailyError.value =
      err.response?.data?.message || "Error al cargar el reporte";
  } finally {
    dailyLoading.value = false;
  }
};

const loadMonthlyReport = async () => {
  monthlyLoading.value = true;
  monthlyError.value = "";
  try {
    const response = await reportsApi.getMonthly(
      monthlyYear.value,
      monthlyMonth.value,
    );
    monthlyReport.value = response.data;
  } catch (err: any) {
    monthlyError.value =
      err.response?.data?.message || "Error al cargar el reporte";
  } finally {
    monthlyLoading.value = false;
  }
};

const dailyVehicleChartData = computed(() => {
  if (!dailyReport.value?.tipos_vehiculo) {
    return { labels: [] as string[], datasets: [] };
  }

  const data = dailyReport.value.tipos_vehiculo;
  const values = Object.values(data).map((v) => Number(v) || 0);
  const total = values.reduce((a, b) => a + b, 0);

  const labels = Object.keys(data).map((key, index) => {
    const nameMap: Record<string, string> = {
      auto: "Automóvil",
      moto: "Motocicleta",
      camioneta: "Camioneta",
    };
    const name = nameMap[key] || key;
    const value = values[index] ?? 0;
    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';
    return `${name} (${percentage}%)`;
  });

  return {
    labels,
    datasets: [
      {
        backgroundColor: [
          'rgba(59, 130, 246, 0.85)',
          'rgba(16, 185, 129, 0.85)',
          'rgba(245, 158, 11, 0.85)',
        ],
        borderColor: [
          '#3b82f6',
          '#10b981',
          '#f59e0b',
        ],
        borderWidth: 2,
        data: values,
        hoverOffset: 8,
      },
    ],
  };
});

const monthlyChartData = computed(() => {
  if (!monthlyReport.value?.ingresos_por_día) {
    return { labels: [] as string[], datasets: [] };
  }

  const data = monthlyReport.value.ingresos_por_día;
  const labels = Object.keys(data);
  const values = Object.values(data).map((v) => Number(v) || 0);

  return {
    labels,
    datasets: [
      {
        label: "Ingresos",
        backgroundColor: "#3b82f6",
        data: values,
      },
    ],
  };
});

const vehicleChartData = computed(() => {
  if (!monthlyReport.value?.tipo_vehiculo_frecuente) {
    return { labels: [] as string[], datasets: [] };
  }

  const data = monthlyReport.value.tipo_vehiculo_frecuente;
  const values = Object.values(data).map((v) => Number(v) || 0);
  const total = values.reduce((a, b) => a + b, 0);

  const labels = Object.keys(data).map((key, index) => {
    const nameMap: Record<string, string> = {
      auto: "Automóvil",
      moto: "Motocicleta",
      camioneta: "Camioneta",
    };
    const name = nameMap[key] || key;
    const value = values[index] ?? 0;
    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';
    return `${name} (${percentage}%)`;
  });

  return {
    labels,
    datasets: [
      {
        backgroundColor: [
          'rgba(59, 130, 246, 0.85)',
          'rgba(16, 185, 129, 0.85)',
          'rgba(245, 158, 11, 0.85)',
        ],
        borderColor: [
          '#3b82f6',
          '#10b981',
          '#f59e0b',
        ],
        borderWidth: 2,
        data: values,
        hoverOffset: 8,
      },
    ],
  };
});

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
    },
  },
};

const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        padding: 20,
        font: {
          size: 14,
        },
        usePointStyle: true,
        pointStyle: 'circle' as const,
      },
    },
    tooltip: {
      callbacks: {
        label: (context: any) => {
          const label = context.label || '';
          const value = context.raw || 0;
          const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
          const percentage = ((value / total) * 100).toFixed(1);
          return `${label}: ${value} (${percentage}%)`;
        },
      },
    },
  },
};

onMounted(() => {
  if (canViewReports.value) {
    loadDailyReport();
  }
});
</script>

<style scoped>
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

.access-denied {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
}

.access-denied h2 {
  color: #dc2626;
}

.back-link {
  display: inline-block;
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

.reports-page {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.tab {
  padding: 0.75rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  font-size: 1rem;
  color: #6b7280;
  transition: all 0.2s;
}

.tab:hover {
  color: #3b82f6;
}

.tab.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filters label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.filters input,
.filters select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}

.loading,
.error {
  text-align: center;
  padding: 2rem;
}

.error {
  color: #dc2626;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
}

.stat-icon {
  font-size: 2rem;
}

.stat-info h3 {
  margin: 0;
  font-size: 0.875rem;
  color: #6b7280;
}

.stat-value {
  margin: 0.25rem 0 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.chart-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.chart-container h3 {
  margin: 0 0 1rem;
  text-align: center;
  color: #1f2937;
  font-weight: 600;
  font-size: 1.1rem;
}

.chart-container canvas {
  max-height: 300px;
}

.no-data {
  text-align: center;
  color: #9ca3af;
  padding: 2rem;
}

.vehicle-types {
  margin-top: 2rem;
  padding: 1.5rem;
  background: #f9fafb;
  border-radius: 8px;
}

.vehicle-types h3 {
  margin: 0 0 1rem;
  color: #1f2937;
}

.vehicle-types-grid {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.vehicle-type-card {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.vehicle-icon {
  font-size: 1.5rem;
}

.vehicle-name {
  font-weight: 500;
  color: #374151;
}

.vehicle-count {
  font-weight: bold;
  color: #2563eb;
  background: #dbeafe;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.tickets-table {
  margin-top: 2rem;
  overflow-x: auto;
}

.tickets-table h3 {
  margin: 0 0 1rem;
  color: #1f2937;
}

.tickets-table table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tickets-table th,
.tickets-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.tickets-table th {
  background: #f3f4f6;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.tickets-table td {
  color: #6b7280;
  font-size: 0.875rem;
}

.tickets-table tr:last-child td {
  border-bottom: none;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-badge.activo {
  background: #dbeafe;
  color: #2563eb;
}

.status-badge.finalizado {
  background: #d1fae5;
  color: #059669;
}
</style>
