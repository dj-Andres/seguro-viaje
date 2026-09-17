# Seguro de Viaje — Frontend (Vue 3)

SPA para cotizar y contratar un seguro de viaje en línea, construida con Vue 3 + Vite. Se conecta a
la API REST del backend Laravel (proyecto hermano dentro del mismo monorepo en `../backend`).

Funcionalidades del frontend:

- Formulario de cotización con validación en español (espejo de la del backend).
- Selector de países con búsqueda (cargado desde la API del backend).
- Resumen de la cotización con desglose de tarifas, confirmación de contratación y descarga del PDF.
- Módulo de consultas con filtros, tabla y paginación.
- Toasts con los mensajes del servidor, diálogos de confirmación (SweetAlert2) y loader global.

---

## Stack

- **Vue** 3.5 (Composition API, JavaScript — sin TypeScript)
- **Vite** 8.3
- **Pinia** 4 (estado global)
- **Vue Router** 5 (SPA)
- **Axios** 1.20 (cliente HTTP)
- **Tailwind CSS** 4 (estilos, vía plugin de Vite)
- **vee-validate + yup** (validación de formularios)
- **SweetAlert2** 11 (toasts y diálogos de confirmación)

---

## Requisitos

- Node.js >= 20.19 (probado con Node 24)
- npm 10+
- Backend Laravel corriendo en `http://localhost:8000` (ver `../backend/README.md`)

---

## Instalación

```bash
# 1. Instalar dependencias
npm install

# 2. Copiar y configurar el entorno
cp .env.example .env
```

### 3. Configurar `.env`

```dotenv
# URL base de la API del backend.
# - Con el proxy de Vite:  /api
# - Apuntando directo al backend: http://localhost:8000/api
VITE_API_URL=/api
```

> Cualquier variable con prefijo `VITE_` queda expuesta en el bundle; **no** guardes secretos aquí.

### 4. Levantar el entorno de desarrollo

```bash
npm run dev
```

La app queda disponible en `http://localhost:5173`.

Para producción:

```bash
npm run build      # genera dist/
npm run preview    # sirve el build localmente
```

---

## Comunicación con el backend

El `baseURL` de Axios se define en `src/config.js` leyendo `VITE_API_URL`:

```js
const API_URL = import.meta.env.VITE_API_URL || '/api'
```

- Si `VITE_API_URL=/api`, las peticiones pasan por el **proxy de Vite**
  (`vite.config.js` reenvía `/api` → `http://127.0.0.1:8000`). Es la forma recomendada en desarrollo
  porque evita problemas de CORS.
- Si se apunta directo a `http://localhost:8000/api`, el backend responde con `Access-Control-Allow-Origin: *`
  (ver `config/cors.php` del backend), así que también funciona sin proxy.

### Interceptor de respuestas

`src/services/http.js` normaliza los errores del backend para consumirlos fácilmente:

```js
{
  message:    'mensaje del servidor o genérico',
  fieldErrors: { campo: ['mensaje en español'], ... },   // errores de validación
  status:     422,
}
```

---

## Rutas

| Ruta         | Vista                 | Descripción                                     |
|--------------|-----------------------|-------------------------------------------------|
| `/`          | `QuoteView`           | Formulario de cotización + resultado            |
| `/consultas` | `PoliciesView`        | Listado de cotizaciones con filtros y paginación |

---

## Estructura del proyecto

```
frontend/
├── index.html
├── vite.config.js          # plugin Vue + Tailwind + proxy /api
├── .env / .env.example     # VITE_API_URL
└── src/
    ├── main.js             # bootstrap (Pinia + Router + estilos)
    ├── App.vue             # Shell: barra de navegación + router-view + LoadingOverlay
    ├── style.css           # Tailwind CSS
    ├── config.js           # API_URL (lee VITE_API_URL)
    ├── router/index.js     # Definición de rutas
    ├── services/           # Llamadas HTTP
    │   ├── http.js         # Instancia de Axios + interceptor de errores
    │   ├── countries.js    # getAllCountries() (recorre todas las páginas)
    │   └── quotes.js       # createQuote / listQuotes / contractQuote / quotePdfUrl
    ├── stores/             # Estado global (Pinia)
    │   ├── quote.js        # Países, cotización, creación y contratación
    │   └── policies.js     # Listado, filtros y paginación
    ├── composables/        # Lógica reutilizable
    │   ├── useToast.js     # Toasts (success/error/warning/info)
    │   ├── useConfirm.js   # Diálogo de confirmación (SweetAlert2)
    │   └── useLoading.js   # Loader global compartido
    ├── components/
    │   ├── CountrySelect.vue   # Selector de países con búsqueda
    │   ├── QuoteForm.vue       # Formulario de cotización
    │   ├── QuoteResult.vue     # Desglose + contratar/descargar/nueva cotización
    │   └── LoadingOverlay.vue  # Spinner global (se muestra con useLoading)
    └── views/
        ├── QuoteView.vue       # Página de cotización
        └── PoliciesView.vue    # Página de consultas
```

---

## Arquitectura

- **services** → llamadas HTTP al backend. Devuelven la respuesta del servidor tal cual.
- **stores** (Pinia) → estado global (datos, cargas, errores) y acciones que invocan los services.
  Conservan y propagan el `message` del servidor para mostrarlo en la UI.
- **composables** → lógica transversal reutilizable:
  - `useToast()`: toasts con los mensajes del servidor.
  - `useConfirm()`: diálogos de confirmación ("¿Estás seguro?") antes de acciones destructivas o
    relevantes (contratar, reiniciar cotización).
  - `useLoading()`: contador global de tareas en curso + `run(task)` que rodea cualquier promesa;
    `LoadingOverlay.vue` muestra el spinner mientras haya tareas activas. Se usa en cargas de países,
    guardado de cotización, contratación, listados, filtros y paginación.
- **views/components** → presentación. Las vistas orquestan los stores y composables; los
  componentes son presentacionales (reciben props / emiten eventos).

---

## Stores

### `quote.js`

| Campo              | Descripción                                           |
|--------------------|-------------------------------------------------------|
| `countries`        | Listado completo de países (normalizado)              |
| `quote`            | Cotización activa (`PolicyResource`) o `null`         |
| `submitting` / `contracting` | Flags para deshabilitar botones            |
| `error`            | Último error ocurrido                                 |

Acciones: `loadCountries()`, `createQuote(payload)`, `contract(id)`, `resetQuote()`.

### `policies.js`

| Campo     | Descripción                                          |
|-----------|------------------------------------------------------|
| `policies`| Resultados de la página actual                       |
| `filters` | Filtros aplicados (`estado`, `destino`, `identificacion`, `cliente`, `desde`, `hasta`) |
| `meta`    | Paginación (`current_page`, `per_page`, `total`, `last_page`) |

Acciones: `fetch()`, `applyFilters(filters)`, `goToPage(page)`.

---

## Integración con la API

El frontend consume los mismos endpoints documentados en `../backend/README.md`:

| Método | Ruta                | Uso en frontend                               |
|--------|---------------------|-----------------------------------------------|
| GET    | `/api/countries`    | `getAllCountries()` (recorre páginas de 100)  |
| POST   | `/api/quotes`       | Crear cotización                              |
| GET    | `/api/quotes`       | Listado con filtros y paginación              |
| POST   | `/api/quotes/{id}/contract` | Confirmar contratación               |
| GET    | `/api/quotes/{id}/pdf` | Descarga del PDF (se abre en otra pestaña) |

La descarga del PDF (`quotePdfUrl`) usa el mismo `VITE_API_URL` que el resto de peticiones, por lo
que funciona igual apuntando directo al backend o a través del proxy.

---

## Validaciones

El formulario valida en el cliente con **vee-validate + yup** usando los mismos mensajes y reglas que
el backend (StoreQuoteRequest) para una experiencia inmediata:

- Nombres, apellidos, identificación y correo obligatorios (correo con formato válido).
- Fecha de nacimiento obligatoria y mayor de 18 años.
- País de destino obligatorio (código ISO de 2 caracteres).
- Fecha de salida no anterior a hoy (según la fecha local del navegador).
- Fecha de regreso posterior a la fecha de salida.

Si el backend responde con errores de validación (`errors`), el formulario los refleja campo a campo
y muestra un toast.

---

## Flujo de usuario

1. **Cotizar** (`/`): el usuario llena los datos del asegurado y del viaje y envía el formulario.
   - Se muestra el loader global mientras se calcula la cotización.
   - Al éxito, un toast muestra el mensaje del servidor y se presenta el desglose de valores.
2. **Resultado**: botón *Contratar seguro* (con confirmación de SweetAlert2 y toast del servidor),
   *Descargar PDF* y *Nueva cotización* (con confirmación para reiniciar).
3. **Consultas** (`/consultas`): listado paginado con filtros (estado, destino, identificación,
   cliente y rango de fechas) y acceso directo al PDF de cada registro.

---

## Notas

- El backend es el que resuelve el nombre del país y la región de recargo a partir del código ISO;
  el frontend solo recibe `PolicyResource` normalizado.
- Las fechas se manejan como strings `YYYY-MM-DD` y se muestran en formato `dd/mm/aaaa`.
- Sin autenticación: la API es pública en ambiente local.

---

## Mejoras futuras

- Migrar a TypeScript.
- Testing automatizado (Vitest + Vue Test Utils) para composables, stores y componentes.
- Dark mode y tema configurable con Tailwind.
- i18n para mensajes (hoy están en español).
- Lazy loading de rutas (`() => import(...)`) para reducir el bundle inicial.
- Autenticación en el backend con Sanctum y manejo de sesión/token en el frontend.