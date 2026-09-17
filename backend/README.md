# Seguro de Viaje — Backend (Laravel)

API REST para cotizar y contratar un seguro de viaje en línea. Permite registrar los datos del
asegurado y del viaje, calcular el valor del seguro según el destino y las fechas, confirmar la
contratación, consultar las cotizaciones y descargarlas en PDF.

Este repositorio corresponde únicamente al **backend**. El frontend (Vue 3) vive en un proyecto
separado dentro del mismo repositorio monorepo.

---

## Stack

- **PHP** 8.3+ (probado en 8.5)
- **Laravel** 13
- **MySQL** 5.7+ / 8.x
- **barryvdh/laravel-dompdf** para la generación del PDF
- API externa: **REST Countries v5** (`https://api.restcountries.com`)

---

## Requisitos

- PHP >= 8.3
- Composer 2
- MySQL (local, ej. Laragon / XAMPP)
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `gd` (recomendada para dompdf)

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio> seguro-viaje
cd seguro-viaje/backend

# 2. Instalar dependencias de PHP
composer install

# 3. Copiar y configurar el entorno
cp .env.example .env
php artisan key:generate
```

### 4. Configurar `.env`

```dotenv
APP_NAME="Seguro de Viaje"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seguro_viaje
DB_USERNAME=root
DB_PASSWORD=

# API REST Countries (token obligatorio)
REST_COUNTRIES_API_URL=https://api.restcountries.com/countries/v5
REST_COUNTRIES_API_KEY=rc_live_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
REST_COUNTRIES_API_LIMIT=100
```

> **Importante:** no subir el token real al repositorio. `.env` está ignorado por Git y
> `.env.example` solo contiene placeholders.

### 5. Crear la base de datos, migrar y sembrar

```bash
# Crear la base de datos (ej. con el cliente de MySQL)
mysql -u root -e "CREATE DATABASE seguro_viaje CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Migrar y sembrar los países de respaldo
php artisan migrate --seed
```

El seeder `CountrySeeder` carga un snapshot de países que se usa como **fallback** si la API
externa no está disponible.

### 6. Levantar el servidor

```bash
php artisan serve
```

La API queda disponible en `http://localhost:8000/api`.

---

## Arquitectura

Se separan claramente las responsabilidades para no concentrar la lógica en controladores:

```
app/
├── Http/
│   ├── Controllers/Api/     # Controladores delgados (solo orquestan)
│   ├── Requests/            # Form Requests con validación y mensajes en español
│   └── Resources/           # API Resources (transformación de salida)
├── Models/                  # Modelos Eloquent + relaciones + scopes
├── Services/                # Lógica de negocio
│   ├── PricingService       # Cálculo de días, tarifa base, recargo y total
│   ├── CountryService       # Integración con REST Countries (caché + fallback)
│   └── QuoteService         # Creación de cotizaciones y contratación
├── Support/
│   └── ApiResponse.php      # Constructor de respuestas uniformes
└── Traits/
    └── ApiResponse.php      # Trait que exponen los controladores
```

- **Controllers** reciben el request, llaman a los servicios y devuelven la respuesta.
- **Form Requests** concentran las reglas de validación y los mensajes de error en español.
- **Services** contienen la lógica de negocio (cálculo de tarifas, integración con la API, etc.).
- **API Resources** definen la estructura de salida de los recursos.
- **Scopes** en los modelos encapsulan los filtros de los listados.
- **Config** (`config/pricing.php`, `config/restcountries.php`) externalizan reglas y credenciales.

### Por qué esta organización

- La lógica de negocio fuera de los controladores facilita testearla de forma aislada y reutilizarla.
- Las respuestas uniformes se construyen en un único punto (`ApiResponse` + handler de excepciones),
  garantizando consistencia en toda la API.
- Los mensajes de validación en español se definen en los Form Requests, no dispersos en el código.

---

## Modelo de datos

| Tabla       | Descripción                                                              |
|-------------|--------------------------------------------------------------------------|
| `asegurados`| Datos personales del asegurado (nombres, apellidos, identificación, etc.) |
| `polizas`   | Cotizaciones/seguros: destino, fechas, valores y estado                   |
| `paises`    | Snapshot local de países (fallback de la API externa)                     |

Relaciones: `Asegurado hasMany Poliza` / `Poliza belongsTo Asegurado` (FK `asegurado_id`).

Estados de una póliza: `cotizado` y `contratado`.

---

## Lógica de negocio (cálculo de la cotización)

Definida en `config/pricing.php` y ejecutada por `PricingService`.

- **Tarifa base:** USD 3 por cada día de viaje.
- **Recargo por región:**

| Región        | Recargo |
|---------------|---------|
| South America | 0%      |
| North America | 15%     |
| Europe        | 20%     |
| Asia          | 25%     |
| Africa        | 20%     |
| Oceania       | 25%     |

**Ejemplo:** viaje de 10 días a España (Europe) → tarifa base `10 × $3 = $30`,
recargo `$30 × 20% = $6`, **total $36**.

La región se deriva del país seleccionado (código ISO) usando `CountryService::pricingRegion()`,
que mapea `region`/`subregion` de la API a una de las seis regiones de recargo.

---

## Integración con REST Countries v5

`CountryService` consume `https://api.restcountries.com/countries/v5` con autenticación por token.

Manejo implementado:

- **Paginación:** la API limita a 100 objetos por petición en plan free, por lo que se recorre con
  `limit`/`offset` hasta obtener todos los países (con un tope de seguridad `max_pages`).
- **`fields`:** se solicitan solo `names,codes,region,subregion,flag` para reducir el payload.
- **Timeout y retries:** `timeout` y `retry` configurables desde `config/restcountries.php`.
- **Caché:** el listado normalizado se cachea (por defecto 1 día) para no depender de la
  disponibilidad continua de la API.
- **Fallback:** si la API falla, responde con error o devuelve una estructura inesperada, se usa el
  snapshot de la tabla `paises` (cargado por `CountrySeeder`).
- **Nombres en español:** se usa `names.translations.spa.common` (con fallback a `names.common`).
- Los registros sin código ISO `alpha_2` se descartan.

---

## Endpoints

### Países

| Método | Ruta             | Descripción                                  |
|--------|------------------|----------------------------------------------|
| GET    | `/api/countries` | Listado paginado de países (`page`, `per_page`) |

### Cotizaciones

| Método | Ruta                              | Descripción                              |
|--------|-----------------------------------|------------------------------------------|
| POST   | `/api/quotes`                     | Crear una cotización (`cotizado`)        |
| GET    | `/api/quotes`                     | Listado paginado con filtros             |
| GET    | `/api/quotes/{policy}`            | Ver detalle de una cotización            |
| POST   | `/api/quotes/{policy}/contract`   | Confirmar contratación (`contratado`)    |
| GET    | `/api/quotes/{policy}/pdf`        | Descargar la cotización en PDF           |

### Filtros de `GET /api/quotes`

| Parámetro       | Descripción                                          |
|-----------------|------------------------------------------------------|
| `estado`        | `cotizado` o `contratado`                            |
| `destino`       | Búsqueda parcial por país de destino                 |
| `identificacion`| Búsqueda parcial por número de identificación        |
| `cliente`       | Búsqueda parcial por nombres/apellidos del asegurado |
| `desde` / `hasta` | Rango por fecha de salida                          |
| `page` / `per_page` | Paginación                                       |

### Crear cotización (`POST /api/quotes`)

```json
{
  "nombres": "Juan",
  "apellidos": "Perez",
  "numero_identificacion": "1234567890",
  "correo_electronico": "juan@example.com",
  "fecha_nacimiento": "1990-01-01",
  "codigo_pais": "ES",
  "fecha_salida": "2026-09-20",
  "fecha_regreso": "2026-09-30"
}
```

---

## Formato de respuestas

Todas las respuestas siguen la misma estructura (éxito y error):

```json
// Éxito
{ "success": true, "message": "...", "data": { ... }, "meta": { ... } }

// Error
{ "success": false, "message": "...", "errors": { "campo": ["mensaje en español"] } }
```

Códigos de estado:

| Código | Caso                                              |
|--------|---------------------------------------------------|
| 200    | Listar / ver / contratar / descargar              |
| 201    | Crear recurso                                     |
| 422    | Errores de validación (con mensajes en español)   |
| 404    | Recurso no encontrado                             |
| 500    | Error interno del servidor                        |

---

## Validaciones

Definidas en `StoreQuoteRequest` y `ContractPolicyRequest`:

- Nombres, apellidos, identificación y correo obligatorios (correo con formato válido).
- Fecha de nacimiento obligatoria y mayor de 18 años.
- País de destino obligatorio (código ISO de 2 caracteres).
- Fecha de salida no anterior a hoy.
- Fecha de regreso posterior a la fecha de salida.
- No se puede contratar una póliza que ya esté `contratado`.

---

## Mejoras futuras

- Autenticación (Laravel Sanctum) para restringir el acceso a la API.
- Pasarela de pagos real al confirmar la contratación.
- Colas (`queue`) para reintentar la sincronización de países fuera de la petición.
- Endpoint para exportar el listado a Excel/CSV.
- Tests automatizados (Pest) para servicios, endpoints y validaciones.
- Docker para un entorno reproducible.
- Caché con Redis en lugar de la base de datos.
