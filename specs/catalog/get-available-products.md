# Spec: Listar Productos Disponibles Para Usuarios Autenticados

## Historia De Usuario

Como usuario autenticado, quiero poder listar productos disponibles, para consultar el catalogo antes de realizar compras.

## Objetivo

Permitir que cualquier usuario autenticado, con rol `admin` o `user`, pueda consultar el catalogo de productos disponibles para compra desde la API, mostrando unicamente productos con stock y datos publicos necesarios para catalogo y compra.

## Alcance

- Listar productos disponibles para usuarios autenticados.
- Permitir acceso a usuarios con rol `admin` o `user`.
- Mostrar solo productos con `stock > 0`.
- Devolver listado paginado.
- Permitir busqueda por texto.
- Permitir filtro por categoria.
- Mantener respuestas JSON consistentes para Angular.

## Fuera De Alcance

- Gestion administrativa de productos.
- Crear productos.
- Editar productos.
- Eliminar productos.
- Consultar detalle individual de producto.
- Comprar productos.
- Gestionar carrito.
- Mostrar productos sin stock.
- Exponer relaciones internas o historial de compras.

## Reglas De Negocio

- Solo usuarios autenticados pueden acceder al catalogo.
- Usuarios con rol `admin` pueden acceder.
- Usuarios con rol `user` pueden acceder.
- Usuarios no autenticados no pueden acceder.
- El catalogo solo muestra productos con `stock > 0`.
- Productos con `stock = 0` no deben aparecer.
- La respuesta solo incluye `id`, `name`, `description`, `category`, `price` y `stock`.
- No se deben incluir `created_at`, `updated_at`, historial de compras ni relaciones internas.
- El orden por defecto sera por `created_at` descendente.
- La paginacion debe limitar `per_page` a un maximo de `100`.

## Flujo Principal

1. El usuario autenticado solicita el catalogo de productos disponibles.
2. El backend valida que la sesion sea valida.
3. El backend consulta productos con `stock > 0`.
4. El backend aplica busqueda y filtros cuando existan.
5. El backend ordena los productos por `created_at` descendente.
6. El backend pagina los resultados.
7. El backend responde `200 OK` con `data`, `meta` y `message`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si los query params son invalidos, responder `422 Unprocessable Entity`.
- Si no hay productos disponibles, responder `200 OK` con `data: []`.

## Validaciones

Query params soportados:

- `page`: opcional, integer, minimo `1`.
- `per_page`: opcional, integer, minimo `1`, maximo `100`.
- `search`: opcional, string.
- `category`: opcional, string.

Reglas:

- `search` aplica sobre `name`, `description` y `category`.
- `category` filtra por categoria exacta.
- No existe filtro `in_stock` porque este endpoint siempre devuelve unicamente productos con `stock > 0`.

## Permisos Y Roles

- Endpoint de catalogo requiere sesion activa.
- Endpoint de catalogo no requiere rol `admin`.
- Usuarios con rol `user` pueden listar productos disponibles.
- Usuarios con rol `admin` tambien pueden listar productos disponibles.
- Usuarios no autenticados no pueden acceder y deben recibir `401 Unauthorized`.

## Estados

La historia contempla estos estados funcionales:

- Catalogo con productos disponibles.
- Catalogo sin productos disponibles.
- Productos existentes sin stock.
- Filtros con resultados.
- Filtros sin resultados.
- Usuario autenticado `admin`.
- Usuario autenticado `user`.
- Usuario no autenticado.

## API / Contrato Esperado

### Listar Productos Disponibles

`GET /api/catalog/products`

Ejemplos:

```http
GET /api/catalog/products
GET /api/catalog/products?page=1&per_page=10
GET /api/catalog/products?search=laptop
GET /api/catalog/products?category=electronics
GET /api/catalog/products?search=laptop&category=electronics&page=1&per_page=10
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
      "stock": 12
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  },
  "message": "Available products retrieved successfully"
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
  "message": "Available products retrieved successfully"
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

### Validacion Fallida

Response `422 Unprocessable Entity`:

```json
{
  "message": "Validation failed",
  "errors": {
    "per_page": ["The per page field must not be greater than 100."],
    "page": ["The page field must be at least 1."]
  }
}
```

## Criterios De Aceptacion

- Dado un usuario autenticado con rol `user`, cuando solicita `GET /api/catalog/products`, entonces el sistema responde `200 OK` con productos disponibles paginados.
- Dado un usuario autenticado con rol `admin`, cuando solicita `GET /api/catalog/products`, entonces el sistema responde `200 OK` con productos disponibles paginados.
- Dado un usuario autenticado, cuando existen productos con `stock > 0`, entonces la respuesta incluye unicamente `id`, `name`, `description`, `category`, `price` y `stock`.
- Dado un usuario autenticado, cuando existen productos con `stock = 0`, entonces esos productos no aparecen en la respuesta.
- Dado un usuario autenticado, cuando no existen productos disponibles, entonces la respuesta incluye `data: []` y metadata de paginacion.
- Dado un usuario autenticado, cuando envia `search`, entonces el sistema filtra por coincidencias en `name`, `description` o `category`.
- Dado un usuario autenticado, cuando envia `category`, entonces el sistema filtra por categoria exacta.
- Dado un usuario autenticado, cuando envia `per_page` mayor a `100`, entonces el sistema responde `422`.
- Dado un usuario no autenticado, cuando solicita `GET /api/catalog/products`, entonces el sistema responde `401 Unauthorized`.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Modelo `Product`.
- Tabla `products` con campos `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.

## Notas De Implementacion

- Agregar ruta separada de administracion en `routes/catalog.php`.
- Registrar prefijo `catalog` en `routes/api.php`.
- Proteger endpoint con `session.api` y `auth:web`.
- No usar middleware `admin`.
- Crear Form Request para validar query params.
- Crear Service para encapsular filtros, orden y paginacion.
- Crear Controller dedicado para listar productos disponibles.
- Limitar columnas devueltas a `id`, `name`, `description`, `category`, `price` y `stock`.
- Agregar tests de feature para usuario `user` autenticado, usuario `admin` autenticado, usuario no autenticado, exclusion de productos sin stock, respuesta vacia, busqueda, filtro por categoria y validacion de query params.
