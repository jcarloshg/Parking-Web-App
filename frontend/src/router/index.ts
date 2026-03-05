import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Login.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/entry',
    name: 'entry',
    component: () => import('@/views/Entry.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'cajero', 'supervisor'] },
  },
  {
    path: '/exit',
    name: 'exit',
    component: () => import('@/views/Exit.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'cajero', 'supervisor'] },
  },
  {
    path: '/reports',
    name: 'reports',
    component: () => import('@/views/Reports.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'supervisor'] },
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('@/views/Admin.vue'),
    meta: { requiresAuth: true, roles: ['admin'] },
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    redirect: '/dashboard',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  authStore.initialize()

  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const allowedRoles = to.matched
    .filter((record) => record.meta.roles)
    .flatMap((record) => record.meta.roles as string[])

  if (requiresAuth && !authStore.token) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (authStore.token && to.name === 'login') {
    next({ name: 'dashboard' })
    return
  }

  if (allowedRoles.length > 0 && authStore.user) {
    if (!allowedRoles.includes(authStore.user.role)) {
      next({ name: 'dashboard' })
      return
    }
  }

  next()
})

export default router
