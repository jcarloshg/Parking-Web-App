import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import { setRouter } from './api'
import App from './App.vue'
import './style.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)

setRouter(router)

app.mount('#app')
