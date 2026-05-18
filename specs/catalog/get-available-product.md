# Spec: Consultar Detalle De Producto Disponible Para Usuarios Autenticados

## Historia De Usuario

Como usuario autenticado, quiero poder consultar el detalle de un producto, para revisar su informacion completa antes de realizar compras.

## Objetivo

Permitir que cualquier usuario autenticado, con rol `admin` o `user`, pueda consultar el detalle publico de un producto disponible en catalogo antes de iniciar una compra.

## Alcance

- Consultar el detalle de un producto disponible por identificador.
- Permitir acceso a usuarios autenticados con rol `admin` o `user`.
- Mostrar solo productos con `stock > 0`.
- Devolver informacion publica necesaria para catalogo y compra.
- Mantener respuesta JSON estable para consumo desde Angular.

## Fuera De Alcance

- Gestion administrativa de productos.
- Crear productos.
- Editar productos.
- Eliminar productos.
- Listar productos disponibles.
- Comprar productos.
- Gestionar carrito.
- Reservar inventario.
- Mostrar productos sin stock en catalogo.
- Exponer fechas administrativas, historial de compras o relaciones internas.

## Reglas De Negocio

- Solo usuarios autenticados pueden consultar el detalle de producto en catalogo.
- Usuarios con rol `user` pueden consultar el detalle.
- Usuarios con rol `admin` tambien pueden consultar el detalle.
- El endpoint no debe requerir rol `admin`.
- Solo se considera visible en catalogo un producto con `stock > 0`.
- Si el producto no existe, debe responder `404 Not Found`.
- Si el producto existe pero tiene `stock = 0`, debe responder `404 Not Found` para mantenerlo fuera del catalogo.
- La respuesta exitosa solo debe incluir `id`, `name`, `description`, `category`, `price` y `stock`.
- La respuesta no debe incluir `created_at`, `updated_at`, historial de compras ni relaciones internas.

## Flujo Principal

1. El usuario autenticado solicita el detalle de un producto del catalogo.
2. El backend valida que la sesion sea valida.
3. El backend busca el producto por identificador.
4. El backend valida que el producto tenga `stock > 0`.
5. El backend responde `200 OK` con `data` y `message`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el producto no existe, responder `404 Not Found`.
- Si el producto existe pero no tiene stock disponible, responder `404 Not Found`.

## Validaciones

- `id` debe representar un producto existente y disponible en catalogo.
- No se reciben query params funcionales para esta historia.
- No se reciben datos por body.

## Permisos Y Roles

- El endpoint requiere sesion activa.
- El endpoint no requiere rol `admin`.
- Usuarios con rol `user` pueden consultar el detalle de productos disponibles.
- Usuarios con rol `admin` pueden consultar el detalle de productos disponibles.
- Usuarios no autenticados no pueden acceder y deben recibir `401 Unauthorized`.

## Estados

La historia contempla estos estados funcionales:

- Producto existente y disponible con `stock > 0`.
- Producto inexistente.
- Producto existente sin stock.
- Usuario autenticado con rol `user`.
- Usuario autenticado con rol `admin`.
- Usuario no autenticado.

## API / Contrato Esperado

### Consultar Detalle De Producto Disponible

`GET /api/catalog/products/{product}`

Ejemplo:

```http
GET /api/catalog/products/1
```

Response `200 OK`:

```json
{
  "data": {
    "id": 1,
    "name": "Laptop Pro",
    "description": "Laptop de alto rendimiento",
    "category": "electronics",
    "price": "1299.99",
    "stock": 12
  },
  "message": "Available product retrieved successfully"
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

### Producto No Encontrado O No Disponible

Response `404 Not Found`:

```json
{
  "message": "Product not found"
}
```

## Criterios De Aceptacion

- Dado un usuario autenticado con rol `user`, cuando solicita `GET /api/catalog/products/{product}` para un producto con `stock > 0`, entonces el sistema responde `200 OK` con el detalle publico del producto.
- Dado un usuario autenticado con rol `admin`, cuando solicita `GET /api/catalog/products/{product}` para un producto con `stock > 0`, entonces el sistema responde `200 OK` con el detalle publico del producto.
- Dado un usuario autenticado, cuando consulta un producto disponible, entonces la respuesta incluye unicamente `id`, `name`, `description`, `category`, `price` y `stock`.
- Dado un usuario autenticado, cuando consulta un producto inexistente, entonces el sistema responde `404 Not Found` con mensaje `Product not found`.
- Dado un usuario autenticado, cuando consulta un producto existente con `stock = 0`, entonces el sistema responde `404 Not Found` con mensaje `Product not found`.
- Dado un usuario no autenticado, cuando solicita `GET /api/catalog/products/{product}`, entonces el sistema responde `401 Unauthorized`.
- Dado cualquier usuario autenticado, cuando consulta el detalle de catalogo, entonces la respuesta no incluye `created_at`, `updated_at`, historial de compras ni relaciones internas.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Modelo `Product`.
- Tabla `products` con campos `id`, `name`, `description`, `category`, `price`, `stock`, `created_at` y `updated_at`.
- Endpoint existente de listado de catalogo `GET /api/catalog/products`.

## Notas De Implementacion

- Agregar la ruta en `routes/catalog.php` bajo el prefijo existente `catalog`.
- Proteger el endpoint con `session.api` y `auth:web`.
- No usar middleware `admin`.
- Crear Controller dedicado para consultar el detalle de producto disponible.
- Crear Service para encapsular la busqueda por identificador y la regla `stock > 0`.
- Limitar columnas devueltas a `id`, `name`, `description`, `category`, `price` y `stock`.
- Reutilizar el formato de error `Product not found` ya existente para recursos de producto no encontrados.
- Agregar tests de feature para usuario `user` autenticado, usuario `admin` autenticado, usuario no autenticado, producto inexistente, producto sin stock y exclusion de campos internos.
