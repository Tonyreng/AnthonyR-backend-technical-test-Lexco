# Lexco Backend

Backend Laravel 13 preparado para desarrollar la API REST de usuarios, productos y compras.

## Requisitos

- Docker
- Docker Compose

## Entorno

El proyecto usa Laravel Sail con PostgreSQL.

Variables principales para desarrollo local:

- `APP_URL=http://localhost`
- `FRONTEND_URL=http://localhost:4200`
- `DB_CONNECTION=pgsql`
- `DB_HOST=pgsql`
- `DB_PORT=5432`
- `DB_DATABASE=laravel`
- `DB_USERNAME=sail`
- `DB_PASSWORD=<local_password>`
- `CORS_ALLOWED_ORIGINS="http://localhost:4200,http://127.0.0.1:4200"`

El archivo `.env.example` contiene valores de ejemplo para levantar el entorno local con Sail. No subir secretos reales, `APP_KEY`, tokens ni credenciales de produccion.

## Comandos

Levantar servicios:

```bash
docker compose up -d
```

Instalar dependencias:

```bash
docker compose exec -T laravel.test composer install
```

Ejecutar migraciones:

```bash
docker compose exec -T laravel.test php artisan migrate
```

Reconstruir la base de datos desde cero durante desarrollo:

```bash
docker compose exec -T laravel.test php artisan migrate:fresh --force
```

Listar rutas:

```bash
docker compose exec -T laravel.test php artisan route:list
```

Ejecutar pruebas:

```bash
docker compose exec -T laravel.test php artisan test
```

## CORS

CORS está configurado en `config/cors.php` para aceptar peticiones con credenciales desde Angular en `http://localhost:4200`.

Las rutas API base están registradas en `routes/api.php`, separadas en:

- `routes/auth.php`
- `routes/users.php`
- `routes/products.php`

Endpoints implementados actualmente:

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

Controladores implementados:

- `App\Http\Controllers\Auth\RegisterController`
- `App\Http\Controllers\Auth\LoginController`
- `App\Http\Controllers\Auth\MeController`
- `App\Http\Controllers\Auth\LogoutController`

Las rutas de autenticación usan sesión con cookie HTTPOnly y están preparadas para consumo desde Angular con `withCredentials: true`.

## Autenticacion

La autenticación actual está publicada en el tag `v0.2.0-auth`.

Flujos implementados:

- Registro público.
- Login con `email` y `password`.
- Consulta del usuario autenticado actual.
- Logout con invalidación de sesión.

### Registro

- `POST /api/auth/register`

Comportamiento:

- Valida `name`, `email`, `password` y `password_confirmation`.
- El primer usuario registrado recibe rol `admin`.
- Los usuarios posteriores reciben rol `user`.
- Ignora cualquier `role` enviado por el cliente.
- Inicia sesión automáticamente con cookie HTTPOnly.

### Login

- `POST /api/auth/login`

Comportamiento:

- Valida `email` y `password`.
- Responde `401` con mensaje genérico si las credenciales son inválidas.
- Regenera la sesión después de autenticación exitosa.
- Devuelve el usuario autenticado con su `role`.

### Usuario Autenticado

- `GET /api/auth/me`

Comportamiento:

- Requiere sesión autenticada válida.
- Devuelve `id`, `name`, `email`, `role`, `created_at` y `updated_at`.
- Si no hay sesión activa, responde `401` con `{"message":"Unauthenticated."}`.

### Logout

- `POST /api/auth/logout`

Comportamiento:

- Requiere sesión autenticada válida.
- Cierra la sesión actual.
- Invalida la sesión.
- Regenera el token de sesión.
- Responde `204 No Content`.

### Contrato JSON Actual

Respuesta exitosa típica:

```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "Anthony Rengifo",
      "email": "anthony@example.com",
      "role": "admin",
      "created_at": "2026-05-17T00:00:00.000000Z",
      "updated_at": "2026-05-17T00:00:00.000000Z"
    }
  },
  "message": "Operation completed successfully"
}
```

Usuario no autenticado:

```json
{
  "message": "Unauthenticated."
}
```

## Base De Datos

La estructura inicial de base de datos corresponde al tag `v0.1.0-database`.

Tablas principales:

- `users`
- `products`
- `purchases`
- `purchase_items`

### Users

Campos principales:

- `id`
- `name`
- `email`
- `role`
- `password`
- `remember_token`
- `created_at`
- `updated_at`

Decisiones:

- `role` tiene valor por defecto `user`.
- `remember_token` se conserva para soportar sesiones persistentes en una fase posterior.
- `email_verified_at` fue eliminado porque la prueba no requiere verificacion de correo.
- La tabla `password_reset_tokens` fue eliminada porque la prueba no requiere recuperacion de contrasena.

### Products

Campos principales:

- `id`
- `name`
- `description`
- `category`
- `price`
- `stock`
- `created_at`
- `updated_at`

Decisiones:

- `category` se incluye para alinear el backend con el requerimiento del frontend.
- `price` usa `decimal(10, 2)`.
- `stock` usa entero sin signo.
- Existen constraints para impedir `price < 0` y `stock < 0`.

### Purchases

Campos principales:

- `id`
- `user_id`
- `total`
- `status`
- `created_at`
- `updated_at`

Decisiones:

- `status` tiene valor por defecto `completed`.
- `total` usa `decimal(10, 2)`.
- Existe constraint para impedir `total < 0`.
- `user_id` usa `restrictOnDelete()` para bloquear eliminacion de usuarios con historial de compras.

### Purchase Items

Campos principales:

- `id`
- `purchase_id`
- `product_id`
- `quantity`
- `unit_price`
- `subtotal`
- `created_at`
- `updated_at`

Decisiones:

- `unit_price` guarda el precio del producto al momento de la compra.
- `subtotal` guarda el total de la linea de compra.
- Existen constraints para impedir `quantity <= 0`, `unit_price < 0` y `subtotal < 0`.
- `purchase_id` usa `cascadeOnDelete()` para eliminar items si se elimina la compra.
- `product_id` usa `restrictOnDelete()` para bloquear eliminacion de productos con historial de compra.

## Relaciones Eloquent

Relaciones implementadas:

- `User hasMany Purchase`
- `Purchase belongsTo User`
- `Purchase hasMany PurchaseItem`
- `PurchaseItem belongsTo Purchase`
- `PurchaseItem belongsTo Product`
- `Product hasMany PurchaseItem`

Modelos disponibles:

- `App\Models\User`
- `App\Models\Product`
- `App\Models\Purchase`
- `App\Models\PurchaseItem`

## Flujo Gitflow

Ramas usadas hasta ahora:

- `feature/database-structure`
- `feature/authentication`

Tags publicados:

- `v0.1.0-database`
- `v0.2.0-auth`
