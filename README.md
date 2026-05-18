# Lexco Backend

Backend Laravel 13 preparado para desarrollar la API REST de usuarios, productos, catalogo y compras.

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
- `routes/catalog.php`
- `routes/purchases.php`

Endpoints implementados actualmente:

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`
- `GET /api/users`
- `POST /api/users`
- `PUT /api/users/{user}`
- `PATCH /api/users/{user}`
- `DELETE /api/users/{user}`
- `GET /api/products`
- `GET /api/products/{product}`
- `POST /api/products`
- `PUT /api/products/{product}`
- `PATCH /api/products/{product}`
- `DELETE /api/products/{product}`
- `GET /api/catalog/products`
- `GET /api/catalog/products/{product}`
- `POST /api/purchases`

Controladores implementados:

- `App\Http\Controllers\Auth\RegisterController`
- `App\Http\Controllers\Auth\LoginController`
- `App\Http\Controllers\Auth\MeController`
- `App\Http\Controllers\Auth\LogoutController`

Las rutas de autenticación usan sesión con cookie HTTPOnly y están preparadas para consumo desde Angular con `withCredentials: true`.

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

## Gestion De Usuarios

La gestion de usuarios corresponde al tag `v0.3.0-users-management`.

Funciones implementadas:

- Listar usuarios.
- Crear usuarios.
- Editar usuarios.
- Eliminar usuarios.

### Endpoints

- `GET /api/users`
- `POST /api/users`
- `PUT /api/users/{user}`
- `PATCH /api/users/{user}`
- `DELETE /api/users/{user}`

### Permisos

- Todas las rutas de user management requieren sesion valida.
- Todas las rutas de user management requieren rol `admin`.
- Un usuario autenticado con rol `user` recibe `403 Forbidden`.
- Un usuario no autenticado recibe `401 Unauthorized`.

### Listado De Usuarios

- `GET /api/users`

Comportamiento:

- Devuelve listado paginado de usuarios.
- Soporta query params `page`, `per_page`, `search` y `role`.
- La respuesta incluye `data`, `meta` y `message`.
- Nunca expone `password` ni `remember_token`.

### Crear Usuario

- `POST /api/users`

Comportamiento:

- Permite crear usuarios con `name`, `email`, `password`, `password_confirmation` y `role`.
- Valida email unico.
- Valida password segura.
- Permite asignar rol `admin` o `user`.
- Responde `201 Created`.

### Editar Usuario

- `PUT /api/users/{user}`
- `PATCH /api/users/{user}`

Comportamiento:

- Permite editar `name`, `email`, `role` y `password` opcional.
- Si no se envia `password`, la contrasena actual se mantiene.
- Valida email unico ignorando el usuario actual.
- Bloquea que un administrador cambie su propio rol de `admin` a `user`.
- Responde `200 OK`.

### Eliminar Usuario

- `DELETE /api/users/{user}`

Comportamiento:

- Elimina fisicamente al usuario.
- Bloquea que un administrador elimine su propia cuenta.
- Bloquea eliminar usuarios con historial de compras asociado.
- Responde `204 No Content` en caso de exito.

### Respuestas Y Errores Esperados

- `200 OK` para consultas y actualizaciones exitosas.
- `201 Created` para creacion exitosa.
- `204 No Content` para eliminacion exitosa.
- `401 Unauthorized` para usuarios no autenticados.
- `403 Forbidden` para usuarios autenticados sin permisos.
- `404 Not Found` cuando el usuario no existe.
- `409 Conflict` cuando el usuario no puede eliminarse por historial asociado.
- `422 Unprocessable Entity` para errores de validacion.

### Reglas De Negocio Relevantes

- Solo administradores pueden gestionar usuarios.
- No se expone informacion sensible en respuestas.
- Un administrador no puede degradar su propio rol.
- Un administrador no puede eliminarse a si mismo.
- Un usuario con historial de compras no puede eliminarse.

## Gestion De Productos

La gestion de productos y el catalogo autenticado corresponden al tag `v0.4.0-product-management`.

Funciones implementadas:

- Listar productos.
- Consultar detalle de producto.
- Crear productos.
- Editar productos.
- Eliminar productos.
- Listar productos disponibles en catalogo autenticado.
- Consultar detalle de producto disponible en catalogo autenticado.

### Endpoints Administrativos

- `GET /api/products`
- `GET /api/products/{product}`
- `POST /api/products`
- `PUT /api/products/{product}`
- `PATCH /api/products/{product}`
- `DELETE /api/products/{product}`

### Permisos Administrativos

- Todas las rutas de product management requieren sesion valida.
- Todas las rutas de product management requieren rol `admin`.
- Un usuario autenticado con rol `user` recibe `403 Forbidden`.
- Un usuario no autenticado recibe `401 Unauthorized`.

### Listado De Productos

- `GET /api/products`

Comportamiento:

- Devuelve listado paginado de productos.
- Soporta query params `page`, `per_page`, `search`, `category` e `in_stock`.
- La respuesta incluye `data`, `meta` y `message`.
- Permite filtrar por coincidencias de texto y categoria.

### Detalle De Producto Administrativo

- `GET /api/products/{product}`

Comportamiento:

- Devuelve el detalle completo del producto administrativo.
- Responde `404 Not Found` con `{"message":"Product not found"}` cuando el producto no existe.

### Crear Producto

- `POST /api/products`

Comportamiento:

- Permite crear productos con `name`, `description`, `category`, `price` y `stock`.
- Valida campos requeridos y tipos esperados.
- Permite precio `0` y stock `0`.
- Responde `201 Created`.

### Editar Producto

- `PUT /api/products/{product}`
- `PATCH /api/products/{product}`

Comportamiento:

- Permite editar `name`, `description`, `category`, `price` y `stock`.
- Soporta actualizacion total o parcial.
- Responde `200 OK`.
- Responde `404 Not Found` cuando el producto no existe.

### Eliminar Producto

- `DELETE /api/products/{product}`

Comportamiento:

- Elimina fisicamente al producto si no tiene historial asociado.
- Bloquea eliminar productos con historial de compra asociado.
- Responde `204 No Content` en caso de exito.
- Responde `409 Conflict` si existe historial asociado.

### Catalogo Autenticado

Endpoints:

- `GET /api/catalog/products`
- `GET /api/catalog/products/{product}`

Permisos:

- Requiere sesion valida.
- Permite acceso a usuarios con rol `admin` y `user`.
- No usa middleware `admin`.

#### Listado De Catalogo

- `GET /api/catalog/products`

Comportamiento:

- Devuelve solo productos con `stock > 0`.
- Soporta query params `page`, `per_page`, `search` y `category`.
- La respuesta incluye `data`, `meta` y `message`.
- Solo expone `id`, `name`, `description`, `category`, `price` y `stock`.

#### Detalle De Catalogo

- `GET /api/catalog/products/{product}`

Comportamiento:

- Devuelve solo productos con `stock > 0`.
- Solo expone `id`, `name`, `description`, `category`, `price` y `stock`.
- Responde `404 Not Found` con `{"message":"Product not found"}` si el producto no existe o no tiene stock.

### Respuestas Y Errores Esperados

- `200 OK` para consultas y actualizaciones exitosas.
- `201 Created` para creacion exitosa.
- `204 No Content` para eliminacion exitosa.
- `401 Unauthorized` para usuarios no autenticados.
- `403 Forbidden` para usuarios autenticados sin permisos administrativos.
- `404 Not Found` cuando el producto no existe o no esta disponible en catalogo.
- `409 Conflict` cuando el producto no puede eliminarse por historial asociado.
- `422 Unprocessable Entity` para errores de validacion.

### Reglas De Negocio Relevantes

- Solo administradores pueden crear, editar o eliminar productos.
- El catalogo autenticado permite acceso a usuarios `admin` y `user`.
- El catalogo solo expone productos con `stock > 0`.
- Los endpoints de catalogo no exponen `created_at`, `updated_at` ni relaciones internas.
- Un producto con historial de compra no puede eliminarse.

## Compra De Productos

El flujo de compras corresponde al tag `v0.5.0-purchase-flow`.

Funciones implementadas:

- Crear compras autenticadas.
- Comprar uno o varios productos distintos en una sola solicitud.
- Descontar inventario de todos los productos comprados.
- Registrar compra y sus items asociados.
- Evitar compras parciales cuando existe error de validacion, producto inexistente o stock insuficiente.

### Endpoint

- `POST /api/purchases`

### Permisos

- Requiere sesion valida.
- Permite acceso a usuarios con rol `admin` y `user`.
- No usa middleware `admin`.
- Un usuario no autenticado recibe `401 Unauthorized`.

### Contrato De Entrada

Request esperado:

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 3,
            "quantity": 1
        }
    ]
}
```

Reglas:

- `items` es requerido y debe contener al menos un item.
- Cada item debe incluir `product_id` y `quantity`.
- `quantity` debe ser entero y mayor o igual a `1`.
- No se permiten productos duplicados dentro del mismo request.
- El backend no acepta `price`, `stock`, `subtotal`, `total`, `status` ni `user_id` como fuente de verdad.

### Comportamiento

- Busca todos los productos solicitados dentro de una transaccion.
- Bloquea filas de productos con `lockForUpdate()` para evitar vender mas unidades que las disponibles en compras concurrentes.
- Calcula `unit_price`, `subtotal` por item y `total` general desde la base de datos.
- Crea un registro en `purchases` asociado al usuario autenticado.
- Crea un registro en `purchase_items` por cada producto comprado.
- Descuenta el stock correspondiente de cada producto.
- Si falla cualquier validacion o regla de negocio, hace rollback completo.
- Responde `201 Created` cuando la compra es exitosa.

### Respuesta Exitosa

```json
{
    "data": {
        "id": 1,
        "user_id": 5,
        "total": "2649.97",
        "status": "completed",
        "items": [
            {
                "product_id": 1,
                "quantity": 2,
                "unit_price": "1299.99",
                "subtotal": "2599.98"
            },
            {
                "product_id": 3,
                "quantity": 1,
                "unit_price": "49.99",
                "subtotal": "49.99"
            }
        ]
    },
    "message": "Purchase completed successfully"
}
```

### Respuestas Y Errores Esperados

- `201 Created` para compra exitosa.
- `401 Unauthorized` para usuarios no autenticados.
- `404 Not Found` cuando uno de los productos no existe.
- `409 Conflict` cuando uno de los productos no tiene stock suficiente.
- `422 Unprocessable Entity` para errores de validacion.

### Reglas De Negocio Relevantes

- Solo usuarios autenticados pueden comprar.
- Usuarios `admin` y `user` pueden comprar.
- Una compra puede contener multiples productos distintos.
- Todos los productos deben existir para crear la compra.
- Todos los productos deben tener stock suficiente para crear la compra.
- Si un producto falla, toda la compra falla.
- No se debe descontar inventario ni registrar compras parciales en errores.
- El estado inicial de la compra es `completed`.

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
- `feature/user-management`
- `feature/product-management`
- `feature/purchase-flow`

Tags publicados:

- `v0.1.0-database`
- `v0.2.0-auth`
- `v0.3.0-users-management`
- `v0.4.0-product-management`
- `v0.5.0-purchase-flow`
