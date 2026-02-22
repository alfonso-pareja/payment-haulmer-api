# 💳 Payment Dashboard API

API REST para procesamiento de pagos con simulador de adquirente bancario (fake acquirer). Desarrollado con **Laravel 10** y **PHP 8.2**, siguiendo principios de **Clean Architecture** y mejores prácticas de desarrollo.

---


## 📦 Requisitos

- Docker 20.10+
- Docker Compose 2.0+
- Git

---

## 🚀 Instalación

### Opción 1: Script Automatizado (Recomendado)

```bash
# 1. Clonar el repositorio
git clone https://github.com/alfonso-pareja/payment-haulmer-api.git
cd payment-dashboard

# 2. Dar permisos de ejecución al script
chmod +x scripts/install.sh

# 3. Ejecutar instalación
./scripts/install.sh
```

El script automáticamente:
- ✅ Crea archivo `.env` desde `.env.example`
- ✅ Levanta contenedores Docker
- ✅ Espera a que MySQL esté listo
- ✅ Instala dependencias de Composer
- ✅ Genera `APP_KEY`
- ✅ Ejecuta migraciones de base de datos
- ✅ Carga datos de ejemplo (seeders)

### Opción 2: Manual

```bash
# 1. Copiar archivo de configuración
cp .env.example .env

# 2. Levantar contenedores
docker compose up -d --build

# 3. Instalar dependencias
docker compose exec app composer install

# 4. Generar clave de aplicación
docker compose exec app php artisan key:generate

# 5. Ejecutar migraciones
docker compose exec app php artisan migrate

# 6. Cargar datos de ejemplo
docker compose exec app php artisan db:seed
```

### Verificar Instalación

```bash
curl http://localhost:8000/api/health
```

**Respuesta esperada:**
```json
{
  "status": "ok",
  "timestamp": "2024-02-18T10:30:00+00:00",
  "service": "Payment Dashboard API"
}
```

---

## 💻 Uso

### Servicios Disponibles

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| API | 8000 | API REST principal |
| MySQL | 3306 | Base de datos |
| Redis | 6379 | Cache |

### Comandos Útiles

```bash
# Ver logs de todos los servicios
docker compose logs -f

# Ver logs solo de la aplicación
docker compose logs -f app

# Acceder al contenedor de la aplicación
docker compose exec app bash

# Ejecutar comandos de Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan cache:clear

# Detener servicios
docker compose down

# Reiniciar servicios
docker compose restart

# Detener y eliminar todo (incluyendo volúmenes)
docker compose down -v
```

---

### Fake Acquirer Logic

```php
$lastDigit = substr($cardNumber, -1);

if ($lastDigit % 2 === 0) {
    return 'approved';  // ✅ Par
} else {
    return 'rejected';  // ❌ Impar
}
```

**Ejemplos:**
- `4111111111111112` (termina en **2**) → ✅ **APROBADO**
- `4111111111111111` (termina en **1**) → ❌ **RECHAZADO**



## 🔌 API Endpoints

### Base URL
```
http://localhost:8000/api
```

### 1. Health Check

**GET** `/health`

Verifica el estado del servicio.

```bash
curl http://localhost:8000/api/health
```

**Response (200):**
```json
{
  "status": "ok",
  "timestamp": "2024-02-18T10:30:00+00:00",
  "service": "Payment Dashboard API"
}
```

---

### 2. Procesar Pago

**POST** `/v1/transactions`

Procesa una nueva transacción de pago.

**Request:**
```bash
curl -X POST http://localhost:8000/api/v1/transactions \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100.50,
    "currency": "USD",
    "cardNumber": "4111111111111112",
    "cardHolder": "Juan Perez"
  }'
```

**Response Aprobada (201):**
```json
{
  "success": true,
  "data": {
    "id": "9d8e3f5a-7b2c-4d1e-8f9a-6b5c4d3e2f1a",
    "amount": "100.50",
    "currency": "USD",
    "cardNumberMasked": "****-****-****-1112",
    "cardHolder": "Juan Perez",
    "status": "approved",
    "processedAt": "2024-02-18T10:30:00+00:00",
    "createdAt": "2024-02-18T10:30:00+00:00"
  }
}
```

**Response Rechazada (200):**
```json
{
  "success": true,
  "data": {
    "id": "8c7d2e4b-6a1c-3d0e-7f8a-5b4c3d2e1f0a",
    "amount": "50.25",
    "currency": "EUR",
    "cardNumberMasked": "****-****-****-1111",
    "cardHolder": "Maria Lopez",
    "status": "rejected",
    "processedAt": "2024-02-18T10:30:00+00:00",
    "createdAt": "2024-02-18T10:30:00+00:00"
  }
}
```

**Validaciones:**
- `amount`: Numérico, mínimo 0.01, máximo 999,999.99
- `currency`: 3 caracteres mayúsculas (USD, EUR, CLP, etc.)
- `cardNumber`: 13-19 dígitos
- `cardHolder`: 3-100 caracteres, solo letras y espacios

---

### 3. Historial Completo (Sin Paginación)

**GET** `/v1/transactions/all`

Retorna todas las transacciones ordenadas por fecha descendente.

```bash
curl http://localhost:8000/api/v1/transactions/all
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "9d8e3f5a...",
      "amount": "100.50",
      "currency": "USD",
      "cardNumberMasked": "****-****-****-1112",
      "cardHolder": "Juan Perez",
      "status": "approved",
      "processedAt": "2024-02-18T10:30:00+00:00",
      "createdAt": "2024-02-18T10:30:00+00:00"
    }
  ],
  "meta": {
    "total": 20
  }
}
```

---

### 4. Historial Paginado

**GET** `/v1/transactions?page=1&per_page=10`

Retorna transacciones con paginación.

```bash
# Página 1, 10 resultados por página
curl "http://localhost:8000/api/v1/transactions?page=1&per_page=10"

# Página 2, 5 resultados por página
curl "http://localhost:8000/api/v1/transactions?page=2&per_page=5"
```

**Response (200):**
```json
{
  "data": [...],
  "links": {
    "first": "http://localhost:8000/api/v1/transactions?page=1",
    "last": "http://localhost:8000/api/v1/transactions?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/v1/transactions?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "last_page": 5
  }
}
```

**Parámetros:**
- `page` (opcional): Número de página, default: 1
- `per_page` (opcional): Resultados por página, default: 10

---

## 🧪 Tests

El proyecto incluye tests completos (unitarios e integración).

### Ejecutar Tests

```bash
# Todos los tests
docker compose exec app php artisan test

# Solo tests de Feature (integración)
docker compose exec app php artisan test --testsuite=Feature

# Solo tests Unit (unitarios)
docker compose exec app php artisan test --testsuite=Unit

# Con cobertura
docker compose exec app php artisan test --coverage

# Test específico
docker compose exec app php artisan test tests/Feature/TransactionApiTest.php
```

## 📁 Estructura del Proyecto

```
payment-dashboard/
├── app/
│   ├── DTOs/                          # Data Transfer Objects
│   │   └── TransactionDTO.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── TransactionController.php
│   │   ├── Requests/                  # Form Requests (Validación)
│   │   │   └── ProcessPaymentRequest.php
│   │   └── Resources/                 # API Resources (Respuestas)
│   │       ├── TransactionResource.php
│   │       ├── TransactionCollection.php
│   │       └── TransactionCollectionPaginated.php
│   ├── Models/
│   │   └── Transaction.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   └── TransactionRepositoryInterface.php
│   │   └── TransactionRepository.php
│   ├── Services/
│   │   └── PaymentService.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── RepositoryServiceProvider.php
│
├── config/                             # Configuración de Laravel
│   ├── app.php
│   └── database.php
│
├── database/
│   ├── factories/
│   │   └── TransactionFactory.php
│   ├── migrations/
│   │   └── 2026_02_17_000000_create_transactions_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── TransactionSeeder.php
│
├── docker/
│   └── nginx.conf                      # Configuración de Nginx
│
├── routes/
│   ├── api.php                         # Rutas de la API
│   └── web.php
│
├── scripts/
│   └── install.sh                      # Script de instalación automatizada
│
├── tests/
│   ├── Feature/                        # Tests de integración
│   │   ├── TransactionApiTest.php
│   │   ├── PaymentServiceTest.php
│   │   └── TransactionRepositoryTest.php
│   └── Unit/                           # Tests unitarios
│       └── FakeAcquirerLogicTest.php
│
├── .env.example                        # Configuración de ejemplo
├── docker-compose.yml                  # Definición de servicios Docker
├── Dockerfile                          # Imagen de PHP-FPM
├── phpunit.xml                         # Configuración de tests
└── README.md                           # Este archivo
```

## 🛠 Stack Tecnológico

| Tecnología | Versión | Uso |
|------------|---------|-----|
| **PHP** | 8.2 | Lenguaje de programación |
| **Laravel** | 10 (LTS) | Framework web |
| **MySQL** | 8.0 | Base de datos |
| **Redis** | Alpine | Cache |
| **Nginx** | Alpine | Web server |
| **Docker** | 20.10+ | Containerización |
| **Docker Compose** | 2.0+ | Orquestación |
| **PHPUnit** | 10.0 | Testing |


## 🔒 Seguridad

### Enmascaramiento de Tarjetas

Los números de tarjeta **nunca se guardan completos**. Solo se almacenan los últimos 4 dígitos enmascarados:

```
Input:  4111111111111112
Stored: ****-****-****-1112
```

### Validaciones

- ✅ Todos los inputs son validados con Form Requests
- ✅ Currency se convierte a mayúsculas automáticamente
- ✅ Card holder solo acepta letras y espacios
- ✅ Timestamps automáticos (`processed_at`, `created_at`)

## 👨‍💻 Autor

Desarrollado como parte del Desafío Técnico - Payment Dashboard
