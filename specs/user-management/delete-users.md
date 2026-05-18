# Spec: Eliminar Usuarios

## Historia De Usuario

Como administrador autenticado, quiero poder eliminar usuarios, para mantener la gestion de cuentas del sistema y remover usuarios que ya no deben tener acceso.

## Objetivo

Permitir que un usuario autenticado con rol `admin` pueda eliminar usuarios existentes desde la API, respetando reglas de autorizacion, restricciones de integridad y respuestas JSON consistentes para consumo desde Angular.

## Alcance

- Eliminar usuarios existentes.
- Proteger la eliminacion con autenticacion y rol `admin`.
- Impedir que un administrador se elimine a si mismo.
- Impedir eliminar usuarios con historial asociado.
- Responder errores JSON consistentes.
- Retornar `204 No Content` cuando la eliminacion sea exitosa.

## Fuera De Alcance

- Listar usuarios.
- Crear usuarios.
- Consultar detalle de usuario.
- Editar usuarios.
- Soft delete.
- Restaurar usuarios eliminados.
- Desactivar usuarios sin eliminarlos.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden eliminar usuarios.
- Los usuarios con rol `user` no pueden eliminar usuarios.
- Un administrador no puede eliminar su propia cuenta.
- La eliminacion sera fisica de la base de datos.
- No se permitira eliminar usuarios que tengan historial asociado, por ejemplo compras registradas.
- Si el usuario tiene historial asociado, la operacion debe bloquearse.
- La respuesta exitosa no debe incluir cuerpo.

## Flujo Principal

1. El administrador autenticado envia una peticion para eliminar un usuario.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend busca el usuario por `id`.
5. El backend valida que el usuario objetivo no sea el mismo administrador autenticado.
6. El backend valida que el usuario objetivo no tenga historial asociado.
7. El backend elimina fisicamente el usuario.
8. El backend responde `204 No Content`.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el usuario autenticado no es `admin`, responder `403 Forbidden`.
- Si el usuario a eliminar no existe, responder `404 Not Found`.
- Si el administrador intenta eliminarse a si mismo, responder `422 Unprocessable Entity`.
- Si el usuario tiene historial asociado, responder `409 Conflict`.

## Validaciones

### Parametros De Ruta

`DELETE /api/users/{id}`

- `id`: requerido en la ruta.
- `id`: debe corresponder a un usuario existente.

No se requiere body para esta operacion.

## Permisos Y Roles

- Endpoint de eliminacion requiere sesion activa y rol `admin`.
- Usuarios con rol `user` no pueden eliminar usuarios y deben recibir `403 Forbidden`.
- Usuarios no autenticados no pueden eliminar usuarios y deben recibir `401 Unauthorized`.

## Estados

La historia contempla los siguientes estados funcionales:

- Usuario existente sin historial asociado: puede eliminarse.
- Usuario existente con historial asociado: no puede eliminarse.
- Usuario inexistente: debe responder `404`.
- Usuario autenticado intentando eliminarse a si mismo: debe responder `422`.

## API / Contrato Esperado

### Eliminar Usuario

`DELETE /api/users/{id}`

Request:

```http
DELETE /api/users/2
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

### Usuario No Encontrado

Response `404 Not Found`:

```json
{
  "message": "User not found"
}
```

### Admin Intenta Eliminarse A Si Mismo

Response `422 Unprocessable Entity`:

```json
{
  "message": "Validation failed",
  "errors": {
    "user": ["You cannot delete your own user account."]
  }
}
```

### Usuario Con Historial Asociado

Response `409 Conflict`:

```json
{
  "message": "User cannot be deleted because it has associated history."
}
```

## Criterios De Aceptacion

- Dado un administrador autenticado, cuando elimina un usuario existente sin historial asociado, entonces el sistema elimina el usuario y responde `204 No Content`.
- Dado un administrador autenticado, cuando intenta eliminar un usuario inexistente, entonces el sistema responde `404 Not Found`.
- Dado un administrador autenticado, cuando intenta eliminarse a si mismo, entonces el sistema responde `422 Unprocessable Entity`.
- Dado un administrador autenticado, cuando intenta eliminar un usuario con historial asociado, entonces el sistema responde `409 Conflict`.
- Dado un usuario regular autenticado, cuando intenta eliminar usuarios, entonces el sistema responde `403 Forbidden`.
- Dado un usuario no autenticado, cuando intenta eliminar usuarios, entonces el sistema responde `401 Unauthorized`.
- Dado una eliminacion exitosa, cuando la API responde, entonces no incluye cuerpo de respuesta.
- Dado un usuario eliminado exitosamente, cuando se consulta la base de datos, entonces el usuario ya no existe.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Middleware de rol `admin`.
- Modelo `User`.
- Relacion `User -> purchases()`.
- Restricciones de base de datos existentes para proteger historial asociado.

## Notas De Implementacion

- Agregar endpoint en `routes/users.php`.
- Mantener proteccion con `session.api`, `auth:web` y `admin`.
- Crear controller dedicado para eliminacion.
- Crear service para encapsular la regla de negocio.
- Usar route model binding para resolver el usuario.
- Reutilizar el manejo existente de `404` JSON para usuarios inexistentes.
- Validar autodelete antes de eliminar.
- Validar historial asociado antes de eliminar.
- Agregar tests de feature para eliminacion exitosa, usuario no autenticado, usuario regular sin permisos, usuario inexistente, administrador intentando eliminarse y usuario con historial asociado.
