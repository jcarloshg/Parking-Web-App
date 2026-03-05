import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import { setRouter } from './api'
import { useAuthStore } from './stores/auth'
import App from './App.vue'
import './style.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)

useAuthStore().initialize()

setRouter(router)

app.mount('#app')
