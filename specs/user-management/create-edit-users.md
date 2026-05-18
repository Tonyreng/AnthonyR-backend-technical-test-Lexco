# Spec: Crear y Editar Usuarios

## Historia De Usuario

Como Administrador autenticado, quiero poder crear y editar usuarios, para gestionar las cuentas y roles de acceso dentro del sistema.

## Objetivo

Permitir que un usuario autenticado con rol `admin` pueda crear nuevos usuarios y modificar usuarios existentes desde la API, manteniendo validaciones de seguridad, control de roles y respuestas JSON consistentes para consumo desde Angular.

## Alcance

- Crear usuarios desde endpoint administrativo.
- Editar usuarios existentes desde endpoint administrativo.
- Asignar rol `admin` o `user`.
- Validar datos obligatorios y formatos.
- Mantener contrasenas seguras.
- Evitar exposicion de informacion sensible.
- Proteger los endpoints con autenticacion y rol `admin`.

## Fuera De Alcance

- Eliminar usuarios.
- Consultar detalle de un usuario individual.
- Listar usuarios, ya implementado en `GET /api/users`.
- Registro publico de usuarios.
- Recuperacion de contrasena.
- Verificacion de email.
- Edicion de perfil por usuario regular.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden crear o editar usuarios.
- Los usuarios con rol `user` no pueden acceder a estos endpoints.
- El campo `role` solo acepta `admin` o `user`.
- El email debe ser unico.
- Al crear usuario, la contrasena es obligatoria.
- Al editar usuario, la contrasena es opcional.
- Si no se envia contrasena al editar, se mantiene la contrasena actual.
- Las contrasenas deben cumplir minimo 8 caracteres, al menos una letra mayuscula, al menos un numero y al menos un caracter especial.
- Un administrador no puede cambiar su propio rol de `admin` a `user`.
- Las respuestas nunca deben incluir `password`, `remember_token` ni cualquier dato sensible.

## Flujo Principal: Crear Usuario

1. El administrador autenticado envia una peticion para crear usuario.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend valida los campos enviados.
5. El backend verifica que el email no exista.
6. El backend crea el usuario con la contrasena encriptada.
7. El backend responde con `201 Created` y los datos publicos del usuario creado.

## Flujo Principal: Editar Usuario

1. El administrador autenticado envia una peticion para editar un usuario existente.
2. El backend valida que la sesion sea valida.
3. El backend valida que el usuario autenticado tenga rol `admin`.
4. El backend busca el usuario por `id`.
5. Si el usuario no existe, responde `404 Not Found`.
6. El backend valida los campos enviados.
7. Si se envia password, la actualiza de forma segura.
8. Si no se envia password, mantiene la contrasena actual.
9. Si el admin intenta cambiar su propio rol a `user`, el backend bloquea la operacion.
10. El backend actualiza el usuario.
11. El backend responde con `200 OK` y los datos publicos actualizados.

## Flujos Alternativos

- Si el usuario no esta autenticado, responder `401 Unauthorized`.
- Si el usuario autenticado no es `admin`, responder `403 Forbidden`.
- Si el email ya esta en uso, responder `422 Unprocessable Entity`.
- Si la contrasena no cumple las reglas, responder `422 Unprocessable Entity`.
- Si el usuario a editar no existe, responder `404 Not Found`.

## Validaciones

### Crear Usuario

Campos esperados:

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "Password1!",
  "password_confirmation": "Password1!",
  "role": "user"
}
```

- `name`: requerido, string, longitud razonable.
- `email`: requerido, email valido, unico.
- `password`: requerido, confirmado, seguro.
- `password_confirmation`: requerido para confirmar password.
- `role`: requerido, solo `admin` o `user`.

### Editar Usuario

Campos esperados:

```json
{
  "name": "Jane Updated",
  "email": "jane.updated@example.com",
  "role": "admin",
  "password": "NewPassword1!",
  "password_confirmation": "NewPassword1!"
}
```

- `name`: requerido o presente segun contrato final, string.
- `email`: requerido o presente segun contrato final, email valido, unico ignorando el usuario actual.
- `role`: requerido o presente segun contrato final, solo `admin` o `user`.
- `password`: opcional, confirmado, seguro.
- `password_confirmation`: requerido solo si se envia `password`.

## Permisos Y Roles

- Endpoint de creacion requiere sesion activa y rol `admin`.
- Endpoint de edicion requiere sesion activa y rol `admin`.
- Usuarios con rol `user` no pueden crear ni editar usuarios y deben recibir `403 Forbidden`.

## Estados

Los usuarios mantienen unicamente el estado derivado de su existencia y rol:

- Usuario existente con rol `admin`.
- Usuario existente con rol `user`.

No se contempla estado activo/inactivo en esta historia.

## API / Contrato Esperado

### Crear Usuario

`POST /api/users`

Request:

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "Password1!",
  "password_confirmation": "Password1!",
  "role": "user"
}
```

Response `201 Created`:

```json
{
  "data": {
    "id": 2,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "user",
    "created_at": "2026-05-18T00:00:00.000000Z",
    "updated_at": "2026-05-18T00:00:00.000000Z"
  },
  "message": "User created successfully"
}
```

### Editar Usuario

`PUT /api/users/{id}` o `PATCH /api/users/{id}`

Request:

```json
{
  "name": "Jane Updated",
  "email": "jane.updated@example.com",
  "role": "admin",
  "password": "NewPassword1!",
  "password_confirmation": "NewPassword1!"
}
```

Response `200 OK`:

```json
{
  "data": {
    "id": 2,
    "name": "Jane Updated",
    "email": "jane.updated@example.com",
    "role": "admin",
    "created_at": "2026-05-18T00:00:00.000000Z",
    "updated_at": "2026-05-18T00:00:00.000000Z"
  },
  "message": "User updated successfully"
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
    "email": ["The email has already been taken."],
    "password": ["The password must contain at least one uppercase letter, one number, and one special character."]
  }
}
```

### Usuario No Encontrado

Response `404 Not Found`:

```json
{
  "message": "User not found"
}
```

### Admin Intenta Degradarse A Si Mismo

Response sugerido `422 Unprocessable Entity`:

```json
{
  "message": "Validation failed",
  "errors": {
    "role": ["You cannot change your own admin role."]
  }
}
```

## Criterios De Aceptacion

- Dado un administrador autenticado, cuando envia datos validos para crear un usuario, entonces el sistema crea el usuario y responde `201 Created`.
- Dado un administrador autenticado, cuando crea un usuario con rol `admin`, entonces el usuario creado queda con rol `admin`.
- Dado un administrador autenticado, cuando crea un usuario con rol `user`, entonces el usuario creado queda con rol `user`.
- Dado un administrador autenticado, cuando intenta crear un usuario con email ya existente, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando intenta crear un usuario con password inseguro, entonces el sistema responde `422`.
- Dado un administrador autenticado, cuando edita un usuario existente con datos validos, entonces el sistema actualiza el usuario y responde `200 OK`.
- Dado un administrador autenticado, cuando edita un usuario sin enviar password, entonces el sistema conserva la contrasena actual.
- Dado un administrador autenticado, cuando edita un usuario enviando password valido, entonces el sistema actualiza la contrasena.
- Dado un administrador autenticado, cuando intenta editar un usuario inexistente, entonces el sistema responde `404`.
- Dado un usuario regular autenticado, cuando intenta crear o editar usuarios, entonces el sistema responde `403`.
- Dado un usuario no autenticado, cuando intenta crear o editar usuarios, entonces el sistema responde `401`.
- Dado un administrador autenticado, cuando intenta cambiar su propio rol de `admin` a `user`, entonces el sistema bloquea la operacion.
- Dado cualquier respuesta exitosa de usuario, entonces la respuesta no incluye `password` ni `remember_token`.

## Dependencias

- Autenticacion por sesion/cookie HTTPOnly existente.
- Middleware de sesion `session.api`.
- Middleware de autenticacion `auth:web`.
- Middleware de rol `admin`.
- Modelo `User` con campos `id`, `name`, `email`, `password`, `role`, `created_at` y `updated_at`.
- Validaciones de password ya definidas en el backend.

## Notas De Implementacion

- Agregar endpoints dentro de `routes/users.php`.
- Mantener rutas protegidas con `session.api`, `auth:web` y `admin`.
- Crear Form Requests separados para creacion y actualizacion.
- Crear Services separados o reutilizables para crear y actualizar usuarios.
- Mantener Controllers delgados.
- Agregar tests de feature para creacion exitosa, edicion exitosa, validacion de email unico, validacion de password, usuario no autenticado, usuario regular sin permisos, usuario inexistente y proteccion contra autodegradacion de admin.
