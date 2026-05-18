# Spec: Crear Productos

## Historia De Usuario

Como administrador autenticado, quiero poder crear productos, para registrar nuevos articulos en el catalogo con su informacion comercial e inventario inicial.

## Objetivo

Permitir que un usuario autenticado con rol `admin` pueda crear productos desde la API, registrando su informacion comercial e inventario inicial con validaciones consistentes y respuestas JSON preparadas para consumo desde Angular.

## Alcance

- Crear productos desde endpoint administrativo.
- Proteger la creacion con autenticacion y rol `admin`.
- Registrar `name`, `description`, `category`, `price` y `stock`.
- Validar campos obligatorios.
- Permitir `price = 0`.
- Permitir `stock = 0`.
- Mantener respuestas JSON consistentes.

## Fuera De Alcance

- Listar productos.
- Consultar detalle de producto.
- Editar productos.
- Eliminar productos.
- Validar unicidad de nombre.
- Gestion de imagenes.
- Gestion avanzada de variantes.
- Compra de productos.
- Ajustes automaticos de inventario por compra.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden crear productos.
- Usuarios con rol `user` no pueden crear productos.
- Todos los campos son obligatorios al crear.
- El producto se crea con `name`, `description`, `category`, `price` y `stock`.
- `price` debe ser mayor o igual a `0`.
- `stock` debe ser mayor o igual a `0`.
- Se permite crear productos con `stock = 0`.
- Se permite crear productos con `price = 0`.
- No se valida unicidad de `name`.
- La respuesta exitosa debe devolver los datos publicos del producto creado.

## Flujo Principal

1. El administrador autenticado envia una peticion para crear producto.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend valida los campos enviados.
5. El backend crea el producto con los datos validados.
6. El backend responde `201 Created` con `data` y `message`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el usuario autenticado no es `admin`, responder `403 Forbidden`.
- Si los datos enviados son invalidos, responder `422 Unprocessable Entity`.

## Validaciones

Campos esperados:

```json
{
  "name": "Laptop Pro",
  "description": "Laptop de alto rendimiento",
  "category": "electronics",
  "price": 1299.99,
  "stock": 12
}
```

Reglas:

- `name`: requerido, string, no vacio, maximo razonable.
- `description`: requerido, string, no vacio, maximo razonable.
- `category`: requerido, string, no vacio, maximo razonable.
- `price`: requerido, numerico, mayor o igual a `0`.
- `stock`: requerido, integer, mayor o igual a `0`.

## Permisos Y Roles

- Endpoint de creacion requiere sesion activa y rol `admin`.
- Usuarios con rol `user` no pueden crear productos y deben recibir `403 Forbidden`.
- Usuarios no autenticados no pueden crear productos y deben recibir `401 Unauthorized`.

## Estados

La historia contempla estos estados funcionales:

- Producto creado con stock disponible.
- Producto creado con `stock = 0`.
- Producto creado con `price = 0`.
- Usuario autenticado admin.
- Usuario autenticado no admin.
- Usuario no autenticado.
- Datos invalidos.

## API / Contrato Esperado

### Crear Producto

`POST /api/products`

Request:

```json
{
  "name": "Laptop Pro",
  "description": "Laptop de alto rendimiento",
  "category": "electronics",
  "price": 1299.99,
  "stock": 12
}
```

Response `201 Created`:

```json
{
  "data": {
    "id": 1,
    "name": "Laptop Pro",
    "description": "Laptop de alto rendimiento",
    "category": "electronics",
    "price": "1299.99",
    "stock": 12,
    "created_at": "2026-05-18T00:00:00.000000Z",
    "updated_at": "2026-05-18T00:00:00.000000Z"
  },
  "message": "Product created successfully"
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

### Validacion Fallida

Response `422 Unprocessable Entity`:

```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price field must be at least 0."],
    "stock": ["The stock field must be an integer."]
  }
}
```

## Criterios De Aceptacion

- Dado un administrador autenticado, cuando envia datos validos, entonces el sistema crea el producto y responde `201 Created`.
- Dado un administrador autenticado, cuando crea un producto, entonces la respuesta incluye `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.
- Dado un administrador autenticado, cuando envia `stock = 0`, entonces el sistema crea el producto correctamente.
- Dado un administrador autenticado, cuando envia `price = 0`, entonces el sistema crea el producto correctamente.
- Dado un administrador autenticado, cuando omite campos obligatorios, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando envia `price < 0`, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando envia `stock < 0`, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando envia `stock` no entero, entonces el sistema responde `422`.
- Dado un usuario regular autenticado, cuando intenta crear un producto, entonces el sistema responde `403 Forbidden`.
- Dado un usuario no autenticado, cuando intenta crear un producto, entonces el sistema responde `401 Unauthorized`.

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
- Crear Form Request para validar body.
- Crear Service para encapsular creacion.
- Crear Controller dedicado para crear productos.
- Agregar tests de feature para creacion exitosa, creacion con `stock = 0`, creacion con `price = 0`, validacion de campos obligatorios, validacion de `price < 0`, validacion de `stock < 0`, validacion de `stock` no entero, usuario no autenticado y usuario regular sin permisos.
