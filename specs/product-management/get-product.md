# Spec: Consultar Detalle De Producto

## Historia De Usuario

Como administrador autenticado, quiero poder consultar el detalle de un producto, para revisar su informacion completa antes de editarlo o tomar decisiones de inventario.

## Objetivo

Permitir que un usuario autenticado con rol `admin` consulte la informacion completa publica de un producto especifico desde la API, con control de acceso y respuestas JSON consistentes para consumo desde Angular.

## Alcance

- Consultar detalle individual de producto.
- Proteger la consulta con autenticacion y rol `admin`.
- Devolver campos publicos completos del producto.
- Responder `404` si el producto no existe.
- Mantener formato JSON consistente.

## Fuera De Alcance

- Listar productos.
- Crear productos.
- Editar productos.
- Eliminar productos.
- Exponer historial de compras del producto.
- Exponer relaciones asociadas.
- Catalogo publico para usuarios regulares.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden consultar el detalle administrativo de producto.
- Usuarios con rol `user` no pueden acceder al endpoint administrativo.
- La respuesta debe incluir unicamente campos publicos del producto.
- No se debe incluir historial de compras ni relaciones asociadas.
- Si el producto no existe, la API debe responder `404 Not Found`.

## Flujo Principal

1. El administrador autenticado solicita el detalle de un producto.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend busca el producto por `id`.
5. El backend responde `200 OK` con `data` y `message`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el usuario autenticado no es `admin`, responder `403 Forbidden`.
- Si el producto no existe, responder `404 Not Found`.

## Validaciones

### Parametros De Ruta

`GET /api/products/{id}`

- `id`: requerido en la ruta.
- `id`: debe corresponder a un producto existente.

No se requiere body para esta operacion.

## Permisos Y Roles

- Endpoint de detalle requiere sesion activa y rol `admin`.
- Usuarios con rol `user` no pueden consultar detalle administrativo y deben recibir `403 Forbidden`.
- Usuarios no autenticados no pueden consultar detalle administrativo y deben recibir `401 Unauthorized`.

## Estados

La historia contempla los siguientes estados funcionales:

- Producto existente.
- Producto inexistente.
- Usuario autenticado admin.
- Usuario autenticado no admin.
- Usuario no autenticado.

## API / Contrato Esperado

### Consultar Detalle De Producto

`GET /api/products/{id}`

Request:

```http
GET /api/products/2
```

Response `200 OK`:

```json
{
  "data": {
    "id": 2,
    "name": "Laptop Pro",
    "description": "Laptop de alto rendimiento",
    "category": "electronics",
    "price": "1299.99",
    "stock": 12,
    "created_at": "2026-05-18T00:00:00.000000Z",
    "updated_at": "2026-05-18T00:00:00.000000Z"
  },
  "message": "Product retrieved successfully"
}
```

## Casos De Error

### Usuario No Autenticado

Response `401 Unauthorized`:

```json
{
  "message": "Unauthenticated."
}
```

### Usuario Sin Permisos

Response `403 Forbidden`:

```json
{
  "message": "Forbidden."
}
```

### Producto No Encontrado

Response `404 Not Found`:

```json
{
  "message": "Product not found"
}
```

## Criterios De Aceptacion

- Dado un administrador autenticado, cuando consulta un producto existente, entonces el sistema responde `200 OK` con `data` y `message`.
- Dado un administrador autenticado, cuando consulta un producto existente, entonces la respuesta incluye `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.
- Dado un administrador autenticado, cuando consulta un producto inexistente, entonces el sistema responde `404 Not Found`.
- Dado un usuario regular autenticado, cuando consulta el detalle administrativo de un producto, entonces el sistema responde `403 Forbidden`.
- Dado un usuario no autenticado, cuando consulta el detalle administrativo de un producto, entonces el sistema responde `401 Unauthorized`.
- Dado una respuesta exitosa, entonces no incluye historial de compras ni relaciones asociadas.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Middleware de rol `admin`.
- Modelo `Product`.
- Manejo existente de `404 Product not found`.
- Tabla `products` con campos `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.

## Notas De Implementacion

- Agregar endpoint en `routes/products.php`.
- Mantener proteccion con `session.api`, `auth:web` y `admin`.
- Crear controller dedicado para detalle de producto.
- Usar route model binding para resolver el producto.
- Reutilizar el manejo existente de `404` JSON para productos inexistentes.
- Agregar tests de feature para consulta exitosa, usuario no autenticado, usuario regular sin permisos y producto inexistente.
