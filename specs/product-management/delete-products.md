# Spec: Eliminar Productos

## Historia De Usuario

Como administrador autenticado, quiero poder eliminar productos, para retirar del catalogo productos que ya no deben estar disponibles.

## Objetivo

Permitir que un usuario autenticado con rol `admin` pueda eliminar productos existentes desde la API, respetando reglas de autorizacion, integridad transaccional y respuestas JSON consistentes para consumo desde Angular.

## Alcance

- Eliminar productos existentes.
- Proteger la eliminacion con autenticacion y rol `admin`.
- Impedir eliminar productos con historial de compra asociado.
- Responder errores JSON consistentes.
- Retornar `204 No Content` cuando la eliminacion sea exitosa.

## Fuera De Alcance

- Listar productos.
- Crear productos.
- Editar productos.
- Consultar detalle individual de producto.
- Soft delete.
- Restaurar productos eliminados.
- Desactivar productos sin eliminarlos.
- Gestion de disponibilidad por estado `active/inactive`.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden eliminar productos.
- Usuarios con rol `user` no pueden eliminar productos.
- La eliminacion sera fisica de la base de datos.
- No se permitira eliminar productos que tengan historial de compra asociado.
- Si el producto tiene historial de compra asociado, la operacion debe bloquearse.
- La respuesta exitosa no debe incluir cuerpo.

## Flujo Principal

1. El administrador autenticado envia una peticion para eliminar un producto.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend busca el producto por `id`.
5. El backend valida que el producto no tenga historial de compra asociado.
6. El backend elimina fisicamente el producto.
7. El backend responde `204 No Content`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el usuario autenticado no es `admin`, responder `403 Forbidden`.
- Si el producto no existe, responder `404 Not Found`.
- Si el producto tiene historial de compra asociado, responder `409 Conflict`.

## Validaciones

### Parametros De Ruta

`DELETE /api/products/{id}`

- `id`: requerido en la ruta.
- `id`: debe corresponder a un producto existente.

No se requiere body para esta operacion.

## Permisos Y Roles

- Endpoint de eliminacion requiere sesion activa y rol `admin`.
- Usuarios con rol `user` no pueden eliminar productos y deben recibir `403 Forbidden`.
- Usuarios no autenticados no pueden eliminar productos y deben recibir `401 Unauthorized`.

## Estados

La historia contempla los siguientes estados funcionales:

- Producto existente sin historial asociado: puede eliminarse.
- Producto existente con historial de compra asociado: no puede eliminarse.
- Producto inexistente: debe responder `404`.
- Usuario autenticado admin.
- Usuario autenticado no admin.
- Usuario no autenticado.

## API / Contrato Esperado

### Eliminar Producto

`DELETE /api/products/{id}`

Request:

```http
DELETE /api/products/2
```

Response `204 No Content`:

```http
HTTP/1.1 204 No Content
```

Sin cuerpo de respuesta.

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

### Producto Con Historial De Compra Asociado

Response `409 Conflict`:

```json
{
  "message": "Product cannot be deleted because it has associated purchase history."
}
```

## Criterios De Aceptacion

- Dado un administrador autenticado, cuando elimina un producto existente sin historial asociado, entonces el sistema elimina el producto y responde `204 No Content`.
- Dado un administrador autenticado, cuando intenta eliminar un producto inexistente, entonces el sistema responde `404 Not Found`.
- Dado un administrador autenticado, cuando intenta eliminar un producto con historial de compra asociado, entonces el sistema responde `409 Conflict`.
- Dado un usuario regular autenticado, cuando intenta eliminar productos, entonces el sistema responde `403 Forbidden`.
- Dado un usuario no autenticado, cuando intenta eliminar productos, entonces el sistema responde `401 Unauthorized`.
- Dado una eliminacion exitosa, cuando la API responde, entonces no incluye cuerpo de respuesta.
- Dado un producto eliminado exitosamente, cuando se consulta la base de datos, entonces el producto ya no existe.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Middleware de rol `admin`.
- Modelo `Product`.
- Relacion `Product -> purchaseItems()`.
- Restricciones de base de datos existentes para proteger historial asociado.

## Notas De Implementacion

- Agregar endpoint en `routes/products.php`.
- Mantener proteccion con `session.api`, `auth:web` y `admin`.
- Crear controller dedicado para eliminacion.
- Crear service para encapsular la regla de negocio.
- Usar route model binding para resolver el producto.
- Reutilizar el manejo existente de `404` JSON para productos inexistentes.
- Validar historial asociado antes de eliminar.
- Agregar tests de feature para eliminacion exitosa, usuario no autenticado, usuario regular sin permisos, producto inexistente y producto con historial asociado.
