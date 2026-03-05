<template>
  <div class="login-container">
    <div class="login-card">
      <h1>Parking System</h1>
      <h2>Iniciar Sesión</h2>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            placeholder="Ingrese su email"
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            required
            placeholder="Ingrese su password"
            :disabled="loading"
          />
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button type="submit" :disabled="loading" class="login-button">
          {{ loading ? 'Ingresando...' : 'Iniciar Sesión' }}
        </button>
      </form>

      <div class="test-credentials">
        <p>Credenciales de prueba:</p>
        <ul>
          <li>Admin: admin@parking.com / password</li>
          <li>Cajero: cajero@parking.com / password</li>
          <li>Supervisor: supervisor@parking.com / password</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { login, loading, error } = useAuth()

const email = ref('')
const password = ref('')

const handleLogin = async () => {
  if (!email.value || !password.value) return

  try {
    await login(email.value, password.value)
  } catch {
    // Error is handled by the store
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h1 {
  text-align: center;
  color: #333;
  margin-bottom: 0.5rem;
}

h2 {
  text-align: center;
  color: #666;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #333;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: #007bff;
}

.error-message {
  color: #dc3545;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background-color: #f8d7da;
  border-radius: 4px;
}

.login-button {
  width: 100%;
  padding: 0.75rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
}

.login-button:hover:not(:disabled) {
  background-color: #0056b3;
}

.login-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.test-credentials {
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
  font-size: 0.875rem;
  color: #666;
}

.test-credentials p {
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.test-credentials ul {
  list-style: none;
  padding: 0;
}

.test-credentials li {
  margin-bottom: 0.25rem;
}
</style>
