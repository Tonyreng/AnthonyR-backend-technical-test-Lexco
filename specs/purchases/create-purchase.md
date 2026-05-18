# Spec: Compra De Multiples Productos Con Descuento De Inventario

## Historia De Usuario

Como usuario autenticado, quiero poder comprar productos y descontar de inventario.

## Objetivo

Permitir que un usuario autenticado compre uno o varios productos distintos en una misma solicitud, registrando la compra completa y descontando inventario de forma segura, atomica y consistente.

## Alcance

- Crear una compra con uno o varios productos distintos.
- Validar autenticacion del usuario.
- Permitir compra a usuarios con rol `user` y `admin`.
- Validar existencia de todos los productos solicitados.
- Validar stock suficiente para cada producto solicitado.
- Calcular `unit_price`, `subtotal` por item y `total` general desde backend.
- Descontar inventario de todos los productos comprados.
- Registrar una compra asociada al usuario autenticado.
- Registrar un item de compra por cada producto comprado.
- Garantizar atomicidad: si falla un producto, falla toda la compra.
- Mantener respuesta JSON estable para consumo desde Angular.

## Fuera De Alcance

- Carrito persistido en backend.
- Pagos o pasarelas de pago.
- Facturacion.
- Envios.
- Direcciones.
- Impuestos.
- Reserva previa de inventario.
- Cancelaciones o devoluciones.
- Compra anonima.
- Compra de productos sin stock suficiente.

## Reglas De Negocio

- Solo usuarios autenticados pueden comprar.
- Usuarios con rol `user` pueden comprar.
- Usuarios con rol `admin` tambien pueden comprar.
- El cliente debe enviar un arreglo `items`.
- Cada item debe contener `product_id` y `quantity`.
- Cada `product_id` debe ser unico dentro de la compra.
- El backend no debe aceptar `price`, `stock`, `unit_price`, `subtotal`, `total`, `status` ni `user_id` como fuente de verdad.
- El backend debe calcular precios usando los valores actuales del producto en base de datos al momento de la compra.
- Cada `quantity` debe ser entero y mayor o igual a `1`.
- Todos los productos enviados deben existir.
- Todos los productos enviados deben tener stock suficiente.
- Si uno de los productos no existe, no se crea la compra.
- Si uno de los productos no tiene stock suficiente, no se crea la compra.
- Si la compra es exitosa, se descuentan todos los stocks correspondientes.
- Si ocurre cualquier error durante el proceso, no se descuenta ningun inventario y no queda compra parcial registrada.
- El estado inicial de la compra sera `completed`.
- La compra debe ser segura ante compras concurrentes para evitar vender mas unidades que las disponibles.

## Flujo Principal

1. El usuario autenticado envia una solicitud de compra con un arreglo de items.
2. El backend valida que la sesion sea valida.
3. El backend valida estructura y reglas del payload.
4. El backend valida que no existan productos duplicados en la solicitud.
5. El backend busca todos los productos solicitados.
6. El backend valida que todos los productos existan.
7. El backend valida stock suficiente para cada producto.
8. El backend calcula `unit_price` y `subtotal` de cada item con datos de base de datos.
9. El backend calcula el `total` general de la compra.
10. El backend crea el registro de compra asociado al usuario autenticado.
11. El backend crea un item de compra por cada producto comprado.
12. El backend descuenta el inventario de cada producto.
13. El backend confirma la transaccion.
14. El backend responde `201 Created` con el resumen de la compra.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si faltan campos o son invalidos, responder `422 Unprocessable Entity`.
- Si `items` esta vacio, responder `422 Unprocessable Entity`.
- Si existen `product_id` duplicados dentro de `items`, responder `422 Unprocessable Entity`.
- Si uno de los productos no existe, responder `404 Not Found`.
- Si uno de los productos tiene stock insuficiente, responder `409 Conflict`.
- Si ocurre un error interno durante la transaccion, no debe guardarse compra parcial ni descontarse stock.

## Validaciones

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

Validaciones:

- `items`: requerido, array, minimo `1` item.
- `items.*.product_id`: requerido, entero, producto existente.
- `items.*.quantity`: requerido, entero, minimo `1`.
- No se permiten `product_id` duplicados dentro de `items`.

Campos que no se aceptan como fuente de verdad:

- `price`
- `stock`
- `unit_price`
- `subtotal`
- `total`
- `status`
- `user_id`

## Permisos Y Roles

- Requiere sesion autenticada.
- No requiere rol `admin`.
- Rol `user`: permitido.
- Rol `admin`: permitido.
- Usuario no autenticado: bloqueado con `401 Unauthorized`.

## Estados

La historia contempla estos estados funcionales:

- Compra solicitada con un producto.
- Compra solicitada con multiples productos distintos.
- Productos existentes con stock suficiente.
- Producto existente sin stock.
- Producto existente con stock insuficiente.
- Producto inexistente.
- Compra completada.
- Compra rechazada por validacion.
- Compra rechazada por stock insuficiente.
- Usuario autenticado con rol `user`.
- Usuario autenticado con rol `admin`.
- Usuario no autenticado.

## API / Contrato Esperado

### Crear Compra

`POST /api/purchases`

Request:

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

Response `201 Created`:

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
    "items": ["The items field is required."],
    "items.0.quantity": ["The items.0.quantity field must be at least 1."]
  }
}
```

### Producto No Encontrado

Response `404 Not Found`:

```json
{
  "message": "Product not found"
}
```

### Stock Insuficiente

Response `409 Conflict`:

```json
{
  "message": "Insufficient stock"
}
```

## Criterios De Aceptacion

- Dado un usuario autenticado con rol `user`, cuando compra varios productos existentes con stock suficiente, entonces el sistema responde `201 Created`.
- Dado un usuario autenticado con rol `admin`, cuando compra varios productos existentes con stock suficiente, entonces el sistema responde `201 Created`.
- Dada una compra exitosa, entonces se crea un registro en `purchases` asociado al usuario autenticado.
- Dada una compra exitosa, entonces se crea un `purchase_item` por cada producto comprado.
- Dada una compra exitosa, entonces se descuenta el stock correcto de cada producto comprado.
- Dada una compra exitosa, entonces el total de la compra corresponde a la suma de todos los subtotales calculados por backend.
- Dado un usuario autenticado, cuando envia `items` vacio, entonces el sistema responde `422 Unprocessable Entity`.
- Dado un usuario autenticado, cuando un item tiene `quantity < 1`, entonces el sistema responde `422 Unprocessable Entity`.
- Dado un usuario autenticado, cuando envia productos duplicados, entonces el sistema responde `422 Unprocessable Entity`.
- Dado un usuario autenticado, cuando uno de los productos no existe, entonces el sistema responde `404 Not Found` y no crea la compra.
- Dado un usuario autenticado, cuando uno de los productos tiene stock insuficiente, entonces el sistema responde `409 Conflict` y no descuenta stock de ningun producto.
- Dado un usuario no autenticado, cuando intenta comprar, entonces el sistema responde `401 Unauthorized`.
- Dado un intento de compra fallido, entonces no se descuenta inventario.
- Dado un intento de compra fallido, entonces no se registra una compra parcial.
- Dadas compras concurrentes sobre los mismos productos, cuando el stock no alcanza para todas, entonces el backend evita vender mas unidades que las disponibles.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Tabla `products`.
- Tabla `purchases`.
- Tabla `purchase_items`.
- Modelo `Product`.
- Modelo `Purchase`.
- Modelo `PurchaseItem`.
- Relacion `User hasMany Purchase`.
- Relacion `Purchase belongsTo User`.
- Relacion `Purchase hasMany PurchaseItem`.
- Relacion `PurchaseItem belongsTo Purchase`.
- Relacion `PurchaseItem belongsTo Product`.
- Relacion `Product hasMany PurchaseItem`.

## Notas De Implementacion

- Crear ruta separada para compras en `routes/purchases.php`.
- Registrar el prefijo `purchases` desde `routes/api.php`.
- Proteger el endpoint con `session.api` y `auth:web`.
- No usar middleware `admin`.
- Crear Form Request para validar `items`, `product_id`, `quantity` y duplicados.
- Crear Service para encapsular validacion de stock, calculos, transaccion y descuento de inventario.
- Usar transaccion de base de datos.
- Bloquear filas de productos durante la compra para evitar condiciones de carrera.
- Crear primero la compra y luego sus items dentro de la misma transaccion.
- Descontar stock dentro de la misma transaccion.
- Si falla cualquier producto, hacer rollback completo.
- No usar valores financieros enviados por frontend.
- Agregar tests de feature para compra exitosa con uno y varios productos, usuario no autenticado, producto inexistente, stock insuficiente, validacion de payload, productos duplicados, no descuento de stock en error y creacion atomica de compra e items.
