# Phase 1: Setup y Configuración

## Objetivo

Configurar el entorno de desarrollo y estructura base del proyecto.

## Tareas

### 1.1 Estructura de Directorios

```
parking-web-app/
├── backend/          # Laravel API
├── frontend/         # Vue.js SPA
└── docs/             # Documentación
```

### 1.2 Backend - Laravel

- [ ] Crear proyecto Laravel 10+
- [ ] Configurar `.env` para MySQL
- [ ] Instalar `tymon/jwt-auth`
- [ ] Configurar JWT en `config/auth.php`
- [ ] Generar secret key JWT: `php artisan jwt:secret`

### 1.3 Frontend - Vue.js

- [ ] Crear proyecto Vue 3 con Vite
- [ ] Instalar dependencias: pinia, vue-router, axios
- [ ] Instalar UI library (TailwindCSS o similar)
- [ ] Configurar axios con base URL

### 1.4 Base de Datos

- [ ] Crear base de datos MySQL: `parking_db`
- [ ] Configurar conexión en `.env`

## Entregables

- Proyecto Laravel configurado con JWT
- Proyecto Vue.js inicializado
- Base de datos creada y conectada
- Repositorio Git inicializado
