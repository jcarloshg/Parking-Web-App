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
        <h1>Administración</h1>
        <div class="user-info" v-if="authStore.user">
          <span class="user-name">{{ userName }}</span>
          <span class="user-role">{{ userRole }}</span>
          <button @click="handleLogout" class="logout-btn">Cerrar Sesión</button>
        </div>
      </header>

      <main class="content">
        <div v-if="!isAdmin" class="access-denied">
          <h2>Acceso Denegado</h2>
          <p>No tienes permiso para acceder a esta sección.</p>
          <router-link to="/dashboard" class="back-link">Volver al Dashboard</router-link>
        </div>

        <div v-else class="admin-page">
          <div class="tabs">
            <button 
              class="tab" 
              :class="{ active: activeTab === 'users' }"
              @click="activeTab = 'users'"
            >
              Usuarios
            </button>
            <button 
              class="tab" 
              :class="{ active: activeTab === 'spaces' }"
              @click="activeTab = 'spaces'"
            >
              Cajones
            </button>
          </div>

          <!-- Users Tab -->
          <div v-if="activeTab === 'users'" class="tab-content">
            <div class="tab-header">
              <h2>Gestión de Usuarios</h2>
              <button class="btn-primary" @click="openUserModal()">+ Crear Usuario</button>
            </div>

            <div v-if="usersLoading" class="loading">Cargando...</div>
            <div v-else-if="usersError" class="error">{{ usersError }}</div>
            <div v-else class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in users" :key="user.id">
                    <td>{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                      <span class="role-badge" :class="user.role">{{ getRoleLabel(user.role) }}</span>
                    </td>
                    <td>{{ formatDate(user.created_at) }}</td>
                    <td>
                      <button class="btn-icon" @click="openUserModal(user)">✏️</button>
                      <button class="btn-icon btn-danger" @click="confirmDeleteUser(user)">🗑️</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="usersPagination.last_page > 1" class="pagination">
              <button 
                :disabled="usersPagination.current_page === 1"
                @click="loadUsers(usersPagination.current_page - 1)"
              >
                Anterior
              </button>
              <span>Página {{ usersPagination.current_page }} de {{ usersPagination.last_page }}</span>
              <button 
                :disabled="usersPagination.current_page === usersPagination.last_page"
                @click="loadUsers(usersPagination.current_page + 1)"
              >
                Siguiente
              </button>
            </div>
          </div>

          <!-- Parking Spaces Tab -->
          <div v-if="activeTab === 'spaces'" class="tab-content">
            <div class="tab-header">
              <h2>Gestión de Cajones</h2>
              <button class="btn-primary" @click="openSpaceModal()">+ Crear Cajón</button>
            </div>

            <div v-if="spacesLoading" class="loading">Cargando...</div>
            <div v-else-if="spacesError" class="error">{{ spacesError }}</div>
            <div v-else class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="space in spaces" :key="space.id">
                    <td>{{ space.id }}</td>
                    <td>{{ space.number }}</td>
                    <td>{{ getTypeLabel(space.type) }}</td>
                    <td>
                      <span class="status-badge" :class="space.status">{{ getStatusLabel(space.status) }}</span>
                    </td>
                    <td>
                      <button class="btn-icon" @click="openSpaceModal(space)">✏️</button>
                      <button class="btn-icon btn-danger" @click="confirmDeleteSpace(space)">🗑️</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="spacesPagination.last_page > 1" class="pagination">
              <button 
                :disabled="spacesPagination.current_page === 1"
                @click="loadSpaces(spacesPagination.current_page - 1)"
              >
                Anterior
              </button>
              <span>Página {{ spacesPagination.current_page }} de {{ spacesPagination.last_page }}</span>
              <button 
                :disabled="spacesPagination.current_page === spacesPagination.last_page"
                @click="loadSpaces(spacesPagination.current_page + 1)"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- User Modal -->
    <div v-if="showUserModal" class="modal-overlay" @click.self="closeUserModal">
      <div class="modal">
        <h3>{{ editingUser ? 'Editar Usuario' : 'Crear Usuario' }}</h3>
        <form @submit.prevent="saveUser">
          <div class="form-group">
            <label>Nombre</label>
            <input v-model="userForm.name" type="text" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="userForm.email" type="email" required />
          </div>
          <div class="form-group">
            <label>Password {{ editingUser ? '(dejar vacío para mantener)' : '' }}</label>
            <input v-model="userForm.password" type="password" :required="!editingUser" />
          </div>
          <div class="form-group">
            <label>Rol</label>
            <select v-model="userForm.role" required>
              <option value="admin">Administrador</option>
              <option value="cajero">Cajero</option>
              <option value="supervisor">Supervisor</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeUserModal">Cancelar</button>
            <button type="submit" class="btn-primary">{{ editingUser ? 'Actualizar' : 'Crear' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Space Modal -->
    <div v-if="showSpaceModal" class="modal-overlay" @click.self="closeSpaceModal">
      <div class="modal">
        <h3>{{ editingSpace ? 'Editar Cajón' : 'Crear Cajón' }}</h3>
        <form @submit.prevent="saveSpace">
          <div class="form-group">
            <label>Número</label>
            <input v-model="spaceForm.number" type="text" required />
          </div>
          <div class="form-group">
            <label>Tipo</label>
            <select v-model="spaceForm.type" required>
              <option value="general">General</option>
              <option value="discapacitado">Discapacitado</option>
              <option value="eléctrico">Eléctrico</option>
            </select>
          </div>
          <div v-if="editingSpace" class="form-group">
            <label>Estado</label>
            <select v-model="spaceForm.status">
              <option value="disponible">Disponible</option>
              <option value="ocupado">Ocupado</option>
              <option value="fuera_servicio">Fuera de Servicio</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeSpaceModal">Cancelar</button>
            <button type="submit" class="btn-primary">{{ editingSpace ? 'Actualizar' : 'Crear' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
      <div class="modal">
        <h3>Confirmar Eliminación</h3>
        <p>¿Estás seguro de que deseas eliminar {{ deleteType === 'user' ? 'el usuario' : 'el cajón' }} "{{ deleteTargetName }}"?</p>
        <p class="warning">Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
          <button type="button" class="btn-secondary" @click="closeDeleteModal">Cancelar</button>
          <button type="button" class="btn-danger" @click="executeDelete">Eliminar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { usersApi, adminParkingApi, type User } from '@/api/users'

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

const activeTab = ref<'users' | 'spaces'>('users')

const getRoleLabel = (role: string) => {
  const labels: Record<string, string> = {
    admin: 'Administrador',
    cajero: 'Cajero',
    supervisor: 'Supervisor'
  }
  return labels[role] || role
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    general: 'General',
    discapacitado: 'Discapacitado',
    eléctrico: 'Eléctrico'
  }
  return labels[type] || type
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    disponible: 'Disponible',
    ocupado: 'Ocupado',
    fuera_servicio: 'Fuera de Servicio'
  }
  return labels[status] || status
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Users state
const users = ref<User[]>([])
const usersLoading = ref(false)
const usersError = ref('')
const usersPagination = ref({ current_page: 1, last_page: 1 })

const loadUsers = async (page = 1) => {
  usersLoading.value = true
  usersError.value = ''
  try {
    const response = await usersApi.getAll(page)
    console.log('Full response:', response)
    const paginatedData = response.data
    const userArray = paginatedData?.data || []
    console.log('userArray:', userArray)
    users.value = userArray
    console.log('users.value set to:', users.value)
    usersPagination.value = {
      current_page: paginatedData?.current_page || 1,
      last_page: paginatedData?.last_page || 1
    }
    console.log('Users after load:', users.value)
  } catch (err: any) {
    console.error('Error loading users:', err)
    usersError.value = err.response?.data?.message || 'Error al cargar usuarios'
  } finally {
    usersLoading.value = false
    console.log('Finally - users.value:', users.value)
  }
}

// Spaces state
const spaces = ref<any[]>([])
const spacesLoading = ref(false)
const spacesError = ref('')
const spacesPagination = ref({ current_page: 1, last_page: 1 })

const loadSpaces = async (page = 1) => {
  spacesLoading.value = true
  spacesError.value = ''
  try {
    const response = await adminParkingApi.getAll(page)
    spaces.value = response.data.data
    spacesPagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page
    }
  } catch (err: any) {
    spacesError.value = err.response?.data?.message || 'Error al cargar cajones'
  } finally {
    spacesLoading.value = false
  }
}

// User Modal
const showUserModal = ref(false)
const editingUser = ref<User | null>(null)
const userForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'cajero' as 'admin' | 'cajero' | 'supervisor'
})

const openUserModal = (user?: User) => {
  if (user) {
    editingUser.value = user
    userForm.value = {
      name: user.name,
      email: user.email,
      password: '',
      role: user.role
    }
  } else {
    editingUser.value = null
    userForm.value = { name: '', email: '', password: '', role: 'cajero' }
  }
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  editingUser.value = null
}

const saveUser = async () => {
  try {
    if (editingUser.value) {
      const data: any = { name: userForm.value.name, email: userForm.value.email, role: userForm.value.role }
      if (userForm.value.password) data.password = userForm.value.password
      await usersApi.update(editingUser.value.id, data)
    } else {
      await usersApi.create(userForm.value)
    }
    closeUserModal()
    loadUsers()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Error al guardar usuario')
  }
}

// Space Modal
const showSpaceModal = ref(false)
const editingSpace = ref<any>(null)
const spaceForm = ref({
  number: '',
  type: 'general' as 'general' | 'discapacitado' | 'eléctrico',
  status: 'disponible' as 'disponible' | 'ocupado' | 'fuera_servicio'
})

const openSpaceModal = (space?: any) => {
  if (space) {
    editingSpace.value = space
    spaceForm.value = {
      number: space.number,
      type: space.type,
      status: space.status
    }
  } else {
    editingSpace.value = null
    spaceForm.value = { number: '', type: 'general', status: 'disponible' }
  }
  showSpaceModal.value = true
}

const closeSpaceModal = () => {
  showSpaceModal.value = false
  editingSpace.value = null
}

const saveSpace = async () => {
  try {
    if (editingSpace.value) {
      await adminParkingApi.update(editingSpace.value.id, spaceForm.value)
    } else {
      await adminParkingApi.create({ number: spaceForm.value.number, type: spaceForm.value.type })
    }
    closeSpaceModal()
    loadSpaces()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Error al guardar cajón')
  }
}

// Delete Confirmation
const showDeleteModal = ref(false)
const deleteType = ref<'user' | 'space'>('user')
const deleteTargetId = ref(0)
const deleteTargetName = ref('')

const confirmDeleteUser = (user: User) => {
  deleteType.value = 'user'
  deleteTargetId.value = user.id
  deleteTargetName.value = user.name
  showDeleteModal.value = true
}

const confirmDeleteSpace = (space: any) => {
  deleteType.value = 'space'
  deleteTargetId.value = space.id
  deleteTargetName.value = space.number
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deleteTargetId.value = 0
  deleteTargetName.value = ''
}

const executeDelete = async () => {
  try {
    if (deleteType.value === 'user') {
      await usersApi.delete(deleteTargetId.value)
      loadUsers()
    } else {
      await adminParkingApi.delete(deleteTargetId.value)
      loadSpaces()
    }
    closeDeleteModal()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Error al eliminar')
  }
}

onMounted(() => {
  console.log('onMounted called, isAdmin:', isAdmin.value, 'authStore.user:', authStore.user)
  if (isAdmin.value) {
    console.log('Calling loadUsers()')
    loadUsers()
    loadSpaces()
  } else {
    console.log('Not admin, not loading users')
  }
})
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
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.access-denied h2 {
  color: #dc2626;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.access-denied p {
  color: #6b7280;
}

.back-link {
  display: inline-block;
  margin-top: 1.5rem;
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

.admin-page {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  border-bottom: 2px solid #e5e7eb;
}

.tab {
  padding: 0.875rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.2s ease;
  border-radius: 8px 8px 0 0;
}

.tab:hover {
  color: #667eea;
  background-color: #f9fafb;
}

.tab.active {
  color: #667eea;
  border-bottom-color: #667eea;
}

.tab-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.tab-header h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.btn-primary {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  padding: 0.625rem 1.25rem;
  background-color: #f3f4f6;
  color: #374151;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background-color: #e5e7eb;
}

.btn-danger {
  padding: 0.625rem 1.25rem;
  background-color: #dc2626;
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.btn-danger:hover {
  background-color: #b91c1c;
}

.btn-icon {
  padding: 0.5rem;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.btn-icon:hover {
  background-color: #f3f4f6;
}

.btn-icon.btn-danger:hover {
  background-color: #fef2f2;
}

.loading,
.error {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.error {
  color: #dc2626;
  background-color: #fef2f2;
  border-radius: 10px;
  border: 1px solid #fecaca;
}

.table-container {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

th {
  background-color: #f9fafb;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

td {
  color: #4b5563;
  font-size: 0.9rem;
}

tr:hover td {
  background-color: #f9fafb;
}

.role-badge, .status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.role-badge.admin {
  background-color: #dbeafe;
  color: #1e40af;
}

.role-badge.cajero {
  background-color: #d1fae5;
  color: #065f46;
}

.role-badge.supervisor {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.disponible {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.ocupado {
  background-color: #fee2e2;
  color: #991b1b;
}

.status-badge.fuera_servicio {
  background-color: #f3f4f6;
  color: #6b7280;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination button {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.pagination button:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination span {
  color: #6b7280;
  font-size: 0.875rem;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal h3 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  background-color: #f9fafb;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  background-color: white;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.warning {
  color: #dc2626;
  font-size: 0.875rem;
  padding: 0.75rem;
  background-color: #fef2f2;
  border-radius: 8px;
  border: 1px solid #fecaca;
}
</style>
