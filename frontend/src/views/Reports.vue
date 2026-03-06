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

.access-denied {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.access-denied h2 {
  color: #dc2626;
  margin-bottom: 1rem;
}

.back-link {
  display: inline-block;
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.back-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.reports-page {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.tab {
  padding: 0.875rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  color: #6b7280;
  transition: all 0.2s ease;
}

.tab:hover {
  color: #667eea;
}

.tab.active {
  color: #667eea;
  border-bottom-color: #667eea;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
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
  color: #374151;
}

.filters input,
.filters select {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  background-color: #f9fafb;
}

.filters input:focus,
.filters select:focus {
  outline: none;
  border-color: #667eea;
  background-color: white;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.loading,
.error {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.error {
  color: #dc2626;
  background-color: #fef2f2;
  border-radius: 10px;
  border: 1px solid #fecaca;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-radius: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  font-size: 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-info h3 {
  margin: 0;
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.stat-value {
  margin: 0.25rem 0 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
}

.chart-container {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
  background-color: #f9fafb;
  border-radius: 12px;
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
  border-radius: 16px;
  border: 1px solid #e5e7eb;
}

.tickets-table h3 {
  margin: 0 0 1rem;
  color: #1f2937;
  font-weight: 600;
}

.tickets-table table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.tickets-table th,
.tickets-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.tickets-table th {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.tickets-table td {
  color: #4b5563;
  font-size: 0.875rem;
}

.tickets-table tr:last-child td {
  border-bottom: none;
}

.tickets-table tbody tr {
  transition: background-color 0.2s ease;
}

.tickets-table tbody tr:hover {
  background-color: #f9fafb;
}

.status-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge.activo {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #2563eb;
}

.status-badge.finalizado {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #059669;
}
</style>
