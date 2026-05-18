# Spec: Editar Productos

## Historia De Usuario

Como administrador autenticado, quiero poder editar productos, para actualizar su informacion, precio, categoria y stock cuando cambien las condiciones del catalogo.

## Objetivo

Permitir que un usuario autenticado con rol `admin` pueda editar productos existentes desde la API, actualizando su informacion comercial e inventario con validaciones consistentes y respuestas JSON preparadas para consumo desde Angular.

## Alcance

- Editar productos existentes desde endpoint administrativo.
- Proteger la edicion con autenticacion y rol `admin`.
- Permitir actualizar `name`, `description`, `category`, `price` y `stock`.
- Permitir actualizacion parcial.
- Exigir que se envie al menos un campo editable.
- Permitir `price = 0`.
- Permitir `stock = 0`.
- Mantener respuestas JSON consistentes.

## Fuera De Alcance

- Listar productos.
- Crear productos.
- Consultar detalle de producto.
- Eliminar productos.
- Validar unicidad de nombre.
- Gestion de imagenes.
- Gestion avanzada de variantes.
- Compra de productos.
- Ajustes automaticos de inventario por compra.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden editar productos.
- Usuarios con rol `user` no pueden editar productos.
- El producto se puede editar con `name`, `description`, `category`, `price` y `stock`.
- Todos los campos editables son opcionales, pero se debe enviar al menos uno.
- Los campos no enviados deben conservar su valor actual.
- `price`, si se envia, debe ser mayor o igual a `0`.
- `stock`, si se envia, debe ser entero y mayor o igual a `0`.
- Se permite actualizar productos dejando `stock = 0`.
- Se permite actualizar productos dejando `price = 0`.
- No se valida unicidad de `name`.
- La respuesta exitosa debe devolver los datos publicos del producto actualizado.

## Flujo Principal

1. El administrador autenticado envia una peticion para editar producto.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend busca el producto por `id`.
5. El backend valida que se haya enviado al menos un campo editable.
6. El backend valida los campos enviados.
7. El backend actualiza solo los campos enviados.
8. El backend responde `200 OK` con `data` y `message`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el usuario autenticado no es `admin`, responder `403 Forbidden`.
- Si el producto no existe, responder `404 Not Found`.
- Si no se envia ningun campo editable, responder `422 Unprocessable Entity`.
- Si los datos enviados son invalidos, responder `422 Unprocessable Entity`.

## Validaciones

Campos esperados:

```json
{
  "name": "Laptop Pro Updated",
  "description": "Laptop actualizada de alto rendimiento",
  "category": "computers",
  "price": 1199.99,
  "stock": 8
}
```

Reglas:

- `name`: opcional, string, no vacio, maximo razonable.
- `description`: opcional, string, no vacio, maximo razonable.
- `category`: opcional, string, no vacio, maximo razonable.
- `price`: opcional, numerico, mayor o igual a `0`.
- `stock`: opcional, integer, mayor o igual a `0`.
- Debe enviarse al menos uno de estos campos: `name`, `description`, `category`, `price`, `stock`.

## Permisos Y Roles

- Endpoint de edicion requiere sesion activa y rol `admin`.
- Usuarios con rol `user` no pueden editar productos y deben recibir `403 Forbidden`.
- Usuarios no autenticados no pueden editar productos y deben recibir `401 Unauthorized`.

## Estados

La historia contempla estos estados funcionales:

- Producto existente actualizado exitosamente.
- Producto actualizado parcialmente.
- Producto actualizado con `stock = 0`.
- Producto actualizado con `price = 0`.
- Producto inexistente.
- Usuario autenticado admin.
- Usuario autenticado no admin.
- Usuario no autenticado.
- Datos invalidos.

## API / Contrato Esperado

### Editar Producto

`PUT /api/products/{id}` o `PATCH /api/products/{id}`

Request completo:

```json
{
  "name": "Laptop Pro Updated",
  "description": "Laptop actualizada de alto rendimiento",
  "category": "computers",
  "price": 1199.99,
  "stock": 8
}
```

Request parcial:

```json
{
  "stock": 0
}
```

Response `200 OK`:

```json
{
  "data": {
    "id": 1,
    "name": "Laptop Pro Updated",
    "description": "Laptop actualizada de alto rendimiento",
    "category": "computers",
    "price": "1199.99",
    "stock": 8,
    "created_at": "2026-05-18T00:00:00.000000Z",
    "updated_at": "2026-05-18T00:00:00.000000Z"
  },
  "message": "Product updated successfully"
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

### Validacion Fallida

Response `422 Unprocessable Entity`:

```json
{
  "message": "Validation failed",
  "errors": {
    "product": ["At least one product field must be provided."],
    "price": ["The price field must be at least 0."],
    "stock": ["The stock field must be an integer."]
  }
}
```

## Criterios De Aceptacion

- Dado un administrador autenticado, cuando envia datos validos, entonces el sistema actualiza el producto y responde `200 OK`.
- Dado un administrador autenticado, cuando envia solo un campo editable valido, entonces el sistema actualiza solo ese campo y conserva los demas.
- Dado un administrador autenticado, cuando actualiza un producto, entonces la respuesta incluye `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.
- Dado un administrador autenticado, cuando envia `stock = 0`, entonces el sistema actualiza el producto correctamente.
- Dado un administrador autenticado, cuando envia `price = 0`, entonces el sistema actualiza el producto correctamente.
- Dado un administrador autenticado, cuando envia body vacio, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando envia `price < 0`, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando envia `stock < 0`, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando envia `stock` no entero, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando intenta editar un producto inexistente, entonces el sistema responde `404 Not Found`.
- Dado un usuario regular autenticado, cuando intenta editar un producto, entonces el sistema responde `403 Forbidden`.
- Dado un usuario no autenticado, cuando intenta editar un producto, entonces el sistema responde `401 Unauthorized`.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Middleware de rol `admin`.
- Modelo `Product`.
- Tabla `products` con campos `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.

## Notas De Implementacion

- Agregar endpoint en `routes/products.php`.
- Proteger endpoint con `session.api`, `auth:web` y `admin`.
- Crear Form Request para validar body parcial.
- Crear Service para encapsular actualizacion.
- Crear Controller dedicado para editar productos.
- Usar route model binding para resolver el producto.
- Agregar manejo JSON para `404 Product not found`.
- Agregar tests de feature para actualizacion exitosa completa, actualizacion parcial, actualizacion con `stock = 0`, actualizacion con `price = 0`, body vacio, validaciones invalidas, producto inexistente, usuario no autenticado y usuario regular sin permisos.
