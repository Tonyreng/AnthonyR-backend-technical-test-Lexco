# Spec: Listar Productos

## Historia De Usuario

Como administrador autenticado, quiero poder listar productos, para visualizar el inventario disponible y gestionar el catalogo del sistema.

## Objetivo

Permitir que un usuario autenticado con rol `admin` consulte el listado administrativo de productos desde la API, con paginacion, busqueda y filtros basicos para facilitar la gestion del inventario.

## Alcance

- Listar productos desde endpoint administrativo.
- Proteger el listado con autenticacion y rol `admin`.
- Devolver productos paginados.
- Permitir busqueda por texto.
- Permitir filtro por categoria.
- Permitir filtro por disponibilidad de stock.
- Mantener respuestas JSON consistentes para consumo desde Angular.

## Fuera De Alcance

- Crear productos.
- Editar productos.
- Eliminar productos.
- Consultar detalle individual de producto.
- Listado publico o catalogo para usuarios regulares.
- Compra de productos.
- Ajustes de inventario por compra.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden acceder al listado administrativo de productos.
- Usuarios con rol `user` no pueden acceder al endpoint administrativo.
- La respuesta debe incluir unicamente campos publicos del producto.
- Si no existen productos o no hay coincidencias con los filtros, la API debe responder `200 OK` con `data` vacio y metadata de paginacion.
- El orden por defecto sera por `created_at` descendente.
- La paginacion debe limitar `per_page` a un maximo de `100`.

## Flujo Principal

1. El administrador autenticado solicita el listado de productos.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend valida los query params enviados.
5. El backend aplica busqueda y filtros cuando existan.
6. El backend ordena los productos por `created_at` descendente.
7. El backend pagina los resultados.
8. El backend responde `200 OK` con `data`, `meta` y `message`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el usuario autenticado no es `admin`, responder `403 Forbidden`.
- Si los query params son invalidos, responder `422 Unprocessable Entity`.
- Si no hay productos o no hay coincidencias, responder `200 OK` con `data: []`.

## Validaciones

Query params soportados:

- `page`: opcional, integer, minimo `1`.
- `per_page`: opcional, integer, minimo `1`, maximo `100`.
- `search`: opcional, string.
- `category`: opcional, string.
- `in_stock`: opcional, boolean.

Reglas:

- `search` aplica sobre `name`, `description` y `category`.
- `category` filtra por categoria exacta.
- `in_stock=true` devuelve productos con `stock > 0`.
- `in_stock=false` devuelve productos con `stock = 0`.

## Permisos Y Roles

- Endpoint de listado requiere sesion activa y rol `admin`.
- Usuarios con rol `user` no pueden acceder y deben recibir `403 Forbidden`.
- Usuarios no autenticados no pueden acceder y deben recibir `401 Unauthorized`.

## Estados

La historia contempla estos estados funcionales:

- Catalogo con productos existentes.
- Catalogo vacio.
- Filtros con resultados.
- Filtros sin resultados.
- Usuario autenticado admin.
- Usuario autenticado no admin.
- Usuario no autenticado.

## API / Contrato Esperado

### Listar Productos

`GET /api/products`

Ejemplos:

```http
GET /api/products
GET /api/products?page=1&per_page=10
GET /api/products?search=laptop
GET /api/products?category=electronics
GET /api/products?in_stock=true
GET /api/products?search=laptop&category=electronics&in_stock=true&page=1&per_page=10
```

Response `200 OK`:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Laptop Pro",
      "description": "Laptop de alto rendimiento",
      "category": "electronics",
      "price": "1299.99",
      "stock": 12,
      "created_at": "2026-05-18T00:00:00.000000Z",
      "updated_at": "2026-05-18T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  },
  "message": "Products retrieved successfully"
}
```

Response `200 OK` sin resultados:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 0,
    "last_page": 1
  },
  "message": "Products retrieved successfully"
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
    "per_page": ["The per page field must not be greater than 100."],
    "in_stock": ["The in stock field must be true or false."]
  }
}
```

## Criterios De Aceptacion

- Dado un administrador autenticado, cuando solicita `GET /api/products`, entonces el sistema responde `200 OK` con productos paginados.
- Dado un administrador autenticado, cuando existen productos, entonces la respuesta incluye `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.
- Dado un administrador autenticado, cuando no existen productos, entonces la respuesta incluye `data: []` y metadata de paginacion.
- Dado un administrador autenticado, cuando envia `search`, entonces el sistema filtra por coincidencias en `name`, `description` o `category`.
- Dado un administrador autenticado, cuando envia `category`, entonces el sistema filtra por categoria exacta.
- Dado un administrador autenticado, cuando envia `in_stock=true`, entonces el sistema devuelve productos con `stock > 0`.
- Dado un administrador autenticado, cuando envia `in_stock=false`, entonces el sistema devuelve productos con `stock = 0`.
- Dado un administrador autenticado, cuando envia `per_page` mayor a `100`, entonces el sistema responde `422`.
- Dado un usuario regular autenticado, cuando solicita `GET /api/products`, entonces el sistema responde `403 Forbidden`.
- Dado un usuario no autenticado, cuando solicita `GET /api/products`, entonces el sistema responde `401 Unauthorized`.

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
- Crear Form Request para validar query params.
- Crear Service para encapsular filtros, orden y paginacion.
- Crear Controller dedicado para listar productos.
- Agregar tests de feature para listado exitoso, respuesta vacia, busqueda, filtro por categoria, filtro por stock, validacion de query params, usuario no autenticado y usuario regular sin permisos.
