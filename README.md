# Seguro de Viaje

Sistema web para **cotizar y contratar un seguro de viaje en línea**:
el usuario ingresa los datos del asegurado y del viaje, obtiene una cotización según el destino y las
fechas, puede confirmar la contratación, consultar todas las cotizaciones generadas y descargarlas en PDF.

Este repositorio es un **monorepo** con dos proyectos:

| Carpeta     | Proyecto                                   | Documentación        |
|-------------|---------------------------------------------|----------------------|
| `backend/`  | API REST (Laravel + MySQL)                  | [`backend/README.md`](backend/README.md) |
| `frontend/` | SPA de consumo (Vue 3 + Vite)               | [`frontend/README.md`](frontend/README.md) |

---

## Stack

| Capa     | Tecnologías                                            |
|----------|--------------------------------------------------------|
| Backend  | PHP 8.3+, Laravel 13, MySQL 5.7+/8.x, barryvdh/laravel-dompdf |
| Frontend | Vue 3 (Composition API), Vite, Pinia, Vue Router, Axios, Tailwind CSS 4, sweetalert2 |
| Validación | vee-validate + yup (cliente) / Form Requests (servidor), mensajes en español |
| Externa  | REST Countries v5 (`https://api.restcountries.com`)     |

---

## Arquitectura

### 1. Arquitectura utilizada

- **Backend (Laravel):** API REST monolítica organizada en **capas**. Los controladores son delgados
  y solo orquestan; la validación se concentra en **Form Requests**; la lógica de negocio en
  **Servicios**; los datos en **Modelos Eloquent** (con relaciones y *scopes*); la salida se
  transforma con **API Resources**. Todas las respuestas son uniformes
  (`{ success, message, data, meta, errors }`), construidas por `App\Support\ApiResponse` y con el
  manejo de excepciones centralizado en `bootstrap/app.php`.

- **Frontend (Vue 3):** SPA con flujo unidireccional **views → stores (Pinia) → services (Axios)**.
  Los componentes son presentacionales y la lógica transversal se encapsula en **composables**
  reutilizables (`useToast`, `useConfirm`, `useLoading`). La URL del backend es configurable
  (`VITE_API_URL`), usando el proxy de Vite en desarrollo o CORS para acceso directo.

### 2. Organización de la lógica de negocio

La lógica vive en `backend/app/Services` y no en los controladores:

- **`PricingService`** — calcula los días de viaje, la tarifa base (USD 3/día), el recargo por región
  y el valor total de la póliza.
- **`QuoteService`** — orquesta la creación y contratación: reutiliza al asegurado por
  `numero_identificacion` (`firstOrCreate`), resuelve el país y su región desde el código ISO,
  genera la póliza en estado `cotizado` y la contrata validando que no esté ya contratada.
- **`CountryService`** — integración con REST Countries y derivación de la región de recargo
  (mapea `region`/`subregion` a las 6 regiones de tarifa).

Las reglas variables se externalizan en `config/pricing.php` y `config/restcountries.php`, y los
filtros del listado se implementan como *scopes* en el modelo `Policy`.

### 3. Integración con REST Countries

`CountryService` consume `https://api.restcountries.com/countries/v5` con autenticación por token:

- **Paginación:** el plan free limita a 100 objetos por petición, por lo que se recorre con
  `limit`/`offset` (con tope de seguridad `max_pages`), solicitando solo
  `fields=names,codes,region,subregion,flag`.
- **Nombres en español:** se usa `names.translations.spa.common` (con fallback a `names.common`);
  los registros sin código ISO `alpha_2` se descartan.
- **Robustez:** el listado normalizado se cachea (TTL configurable), con `timeout` y reintentos
  configurables, y **fallback a un snapshot local** en la tabla `paises` (sembrado por
  `CountrySeeder`) si la API no está disponible.

### 4. Decisiones técnicas relevantes

- **Dominio en español:** tablas, columnas, mensajes de validación y toasts en español (el resto del
  código en inglés).
- **Validación en dos capas:** cliente (vee-validate + yup, espejo de reglas del backend) y servidor
  (Form Requests) para una experiencia inmediata y datos siempre válidos.
- **Respuestas uniformes:** éxito y error con el mismo formato y códigos consistentes
  (200/201/422/404/500).
- **Zona horaria:** `America/Guayaquil` para que las validaciones de fecha ("no anterior a hoy")
  coincidan con el usuario local.
- **PDF:** generación con dompdf + Blade como endpoint descargable.
- **UX del frontend:** loader global, toasts con los mensajes del servidor y diálogos de confirmación
  (SweetAlert2), todo mediante composables reutilizables.
- Sin autenticación (la API es pública en ambiente local).

---

## Endpoints del API

| Método | Ruta                          | Descripción                                  |
|--------|-------------------------------|----------------------------------------------|
| GET    | `/api/countries`              | Listado paginado de países (`page`, `per_page`) |
| POST   | `/api/quotes`                 | Crear una cotización (`cotizado`)            |
| GET    | `/api/quotes`                 | Listado paginado con filtros (estado, destino, identificación, cliente, fechas) |
| GET    | `/api/quotes/{id}`            | Detalle de una cotización                    |
| POST   | `/api/quotes/{id}/contract`   | Confirmar contratación (`contratado`)        |
| GET    | `/api/quotes/{id}/pdf`        | Descargar la cotización en PDF               |

---

## Inicio rápido

**Backend** (ver [`backend/README.md`](backend/README.md)):

```bash
cd backend
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve
```

**Frontend** (ver [`frontend/README.md`](frontend/README.md)):

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

La API queda en `http://localhost:8000/api` y la SPA en `http://localhost:5173`.

---

## Mejoras futuras (evolución a producción)

Aspectos que se incorporarían para llevar el sistema de demo a un entorno productivo:

### Seguridad y autenticación
- **Autenticación y autorización** con Laravel Sanctum (tokens/bearer) y roles (p. ej. asesor vs. administrador), protegiendo por lo menos los endpoints de consulta y contratación.
- **Rate limiting por usuario/IP** adaptado al plan (`throttle`), además del límite global actual.
- **CORS restringido** a los dominios permitidos (hoy es `*`).
- **Gestión de secretos**: mover el token de REST Countries (y cualquier credencial) a un *secret manager* con rotación; `.env` solo con placeholders en todos los entornos.
- Fortalecer validaciones de negocio (días máximos de viaje, montos, listas de países no permitidos) y proteger contra abuso/uso indebido.

### Pagos y negocio
- **Pasarela de pagos real** (Stripe/PayPal) al confirmar la contratación, con estados de pago (`pendiente`, `pagado`, `reembolsado`), webhooks y manejo de intención de pago en fallos.
- **Facturación electrónica** (SRI en Ecuador) y generación de comprobantes/retenciones.
- **Motor de tarifas más rico**: múltiples planes/coberturas, extras opcionales, descuentos, promociones e impuestos, todo configurable en BD o `config`.
- **Historial de versiones** de la póliza y auditoría de cambios (quién/cuándo contrató o modificó).

### Datos y persistencia
- **Backups automáticos** de MySQL con *point-in-time recovery* y políticas de retención.
- **Soft deletes** y modelo de auditoría para pólizas/asegurados.
- **Política de retención y borrado** de datos personales (Ley de Protección de Datos / RGPD): exportación y derecho al olvido para asegurados.

### Escalabilidad y rendimiento
- **Colas** (Redis + Laravel Queue) para tareas pesadas/desacoplables: sincronización de países, generación de PDFs y notificaciones por correo.
- **Caché con Redis** del catálogo de países y consultas frecuentes, con invalidación controlada.
- **Indexación** adicional de `polizas` (destino, cliente, fechas) y evaluar paginación *cursor-based* si el volumen crece.
- **Paginación y carga eficiente** en el listado (actualmente OK para demo, optimizable a escala).
- **Integraciones externas robustas**: *timeout*, reintentos con *backoff* y *circuit breaker*, monitoreo del SLA de REST Countries y del ratio de uso de caché/fallback.

### Operaciones y devops
- **Docker Compose** (nginx + php-fpm + MySQL + Redis) para entornos reproducibles.
- **CI/CD**: pipeline con linting, análisis estático (PHPStan), tests y despliegue por ambientes (staging/producción).
- **Observabilidad**: logs estructurados, métricas/APM, *health checks* y alertas (p. ej. sobre fallos de la API externa).
- **Tests automatizados** en ambos lados: Pest para servicios/endpoints/validaciones del backend, Vitest + Vue Test Utils y E2E (Playwright/Cypress) en el frontend, con medición de cobertura.

### Frontend
- Migrar a **TypeScript** y añadir **lazy loading** de rutas + *code splitting* para reducir el bundle inicial.
- **Gestión de errores global** (p. ej. Sentry) y logs de cliente.
- **PWA** (offline/service worker) y auditoría de **accesibilidad (a11y)** y rendimiento (Lighthouse).
- Refinamientos de UX: *skeleton loaders*, paginación infinita y temas (dark mode).

### Regulatorio
- Términos y condiciones + detalle de uso y consentimiento de datos personales.