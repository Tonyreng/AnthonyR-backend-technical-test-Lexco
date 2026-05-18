# Spec: Consultar Usuario Autenticado Y Cerrar Sesion

## Historias De Usuario

### Historia 1: Consultar Usuario Autenticado

Como usuario autenticado, quiero consultar mi sesion actual dentro de la aplicacion, para que Angular pueda saber quien soy y redirigirme segun mi rol.

### Historia 2: Cerrar Sesion

Como usuario autenticado, quiero cerrar sesion dentro de la aplicacion, para salir de forma segura y evitar que mi sesion siga activa.

## Objetivo

Permitir que Angular consulte el usuario autenticado actual y permita cerrar sesion de forma segura usando cookies HTTPOnly, manteniendo al backend como fuente de verdad del estado de autenticacion.

## Alcance

- Endpoint privado para consultar el usuario autenticado.
- Endpoint privado para cerrar sesion.
- Validacion de sesion activa.
- Respuesta JSON con datos basicos del usuario autenticado.
- No exposicion de datos sensibles.
- Cierre de sesion actual del usuario.
- Invalidacion de sesion tras logout.
- Regeneracion de token de sesion tras logout.
- Compatibilidad con Angular usando `withCredentials: true`.
- Respuestas `401 Unauthorized` cuando no exista sesion activa.
- Sin redirecciones desde backend.

## Fuera De Alcance

- Login.
- Registro.
- Recuperacion de contrasena.
- Verificacion de correo electronico.
- Refresh tokens.
- Tokens Bearer.
- Gestion de roles.
- Perfil editable.
- Redirecciones desde backend.
- Logout global en todos los dispositivos.
- Sesiones persistentes tipo recordarme.

## Reglas De Negocio

- `me` solo debe responder si existe una sesion autenticada valida.
- `me` debe devolver los datos basicos del usuario autenticado.
- `me` no debe devolver `password`, `remember_token` ni informacion sensible.
- Si no hay sesion activa, `me` debe responder `401 Unauthorized`.
- Angular puede llamar `me` al cargar la aplicacion para restaurar el estado del usuario.
- Angular puede usar el `role` devuelto por `me` para redirigir al area correspondiente.
- `logout` solo debe ejecutarse si existe una sesion autenticada valida.
- `logout` debe cerrar la sesion actual.
- `logout` debe invalidar la sesion.
- `logout` debe regenerar el token de sesion.
- `logout` debe responder `204 No Content` cuando finalice correctamente.
- Si no hay sesion activa, `logout` debe responder `401 Unauthorized`.
- El backend no debe hacer redirecciones despues de `me` o `logout`.
- La redireccion posterior pertenece al frontend.

## Flujo Principal: Consultar Usuario Autenticado

1. Angular carga la aplicacion.
2. Angular llama `GET /api/auth/me` con credenciales habilitadas.
3. El backend valida que exista una sesion autenticada.
4. El backend obtiene el usuario autenticado.
5. El backend responde con los datos basicos del usuario.
6. Angular recibe el usuario.
7. Angular usa el `role` para decidir navegacion o estado de sesion.

## Flujo Principal: Cerrar Sesion

1. El usuario autenticado solicita cerrar sesion desde Angular.
2. Angular llama `POST /api/auth/logout` con credenciales habilitadas.
3. El backend valida que exista una sesion autenticada.
4. El backend ejecuta logout de la sesion actual.
5. El backend invalida la sesion.
6. El backend regenera el token de sesion.
7. El backend responde `204 No Content`.
8. Angular limpia su estado local y redirige al login.

## Flujos Alternativos

- Si Angular llama `me` sin sesion activa, el backend responde `401 Unauthorized`.
- Si Angular llama `logout` sin sesion activa, el backend responde `401 Unauthorized`.
- Si la sesion expiro, cualquier endpoint privado responde `401 Unauthorized`.
- Si ocurre un error no controlado, el backend responde con error generico sin exponer detalles sensibles.

## Validaciones

- `me` no requiere body.
- `logout` no requiere body.
- Ambos endpoints requieren sesion autenticada valida.
- Ambos endpoints deben recibir cookies de sesion desde Angular.
- Angular debe llamar estos endpoints usando `withCredentials: true`.

## Permisos Y Roles

- `me` requiere usuario autenticado.
- `logout` requiere usuario autenticado.
- Ambos endpoints pueden ser usados por usuarios `admin`.
- Ambos endpoints pueden ser usados por usuarios `user`.
- El rol no cambia durante `me` ni `logout`.
- El backend devuelve `role` en `me` para que Angular pueda decidir navegacion.
- El backend no confia en roles enviados por el frontend.

## Estados

- `authenticated`: existe sesion activa y usuario autenticado.
- `guest`: no existe sesion activa.
- `session_expired`: la sesion expiro o ya no es valida.
- `logged_out`: la sesion fue cerrada correctamente.
- `server_error`: error inesperado no controlado.

## Criterios De Aceptacion

- Dado que un usuario tiene sesion activa, cuando Angular llama `GET /api/auth/me`, entonces el backend debe responder `200 OK`.
- Dado que un usuario tiene sesion activa, cuando el backend responde a `me`, entonces debe devolver `id`, `name`, `email`, `role`, `created_at` y `updated_at`.
- Dado que un usuario tiene sesion activa, cuando el backend responde a `me`, entonces no debe incluir `password` ni `remember_token`.
- Dado que un usuario no tiene sesion activa, cuando Angular llama `GET /api/auth/me`, entonces el backend debe responder `401 Unauthorized`.
- Dado que un usuario autenticado tiene rol `admin`, cuando Angular recibe la respuesta de `me`, entonces puede redirigirlo al dashboard administrativo.
- Dado que un usuario autenticado tiene rol `user`, cuando Angular recibe la respuesta de `me`, entonces puede redirigirlo al catalogo de productos.
- Dado que un usuario tiene sesion activa, cuando Angular llama `POST /api/auth/logout`, entonces el backend debe cerrar la sesion y responder `204 No Content`.
- Dado que un usuario hace logout correctamente, cuando el proceso termina, entonces la sesion debe quedar invalidada.
- Dado que un usuario hace logout correctamente, cuando el proceso termina, entonces el token de sesion debe ser regenerado.
- Dado que un usuario no tiene sesion activa, cuando Angular llama `POST /api/auth/logout`, entonces el backend debe responder `401 Unauthorized`.
- Dado que el frontend usa Angular, cuando llama `me` o `logout`, entonces debe poder enviar cookies usando `withCredentials: true`.

## Casos De Error

- `401 Unauthorized`: llamada a `me` sin sesion activa.
- `401 Unauthorized`: llamada a `logout` sin sesion activa.
- `401 Unauthorized`: sesion expirada o invalida.
- `500 Internal Server Error`: error inesperado del servidor.

## Contrato De Datos Esperado

### GET /api/auth/me

#### Response Exitosa

```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "Anthony Rengifo",
      "email": "anthony@example.com",
      "role": "admin",
      "created_at": "2026-05-17T00:00:00.000000Z",
      "updated_at": "2026-05-17T00:00:00.000000Z"
    }
  },
  "message": "Authenticated user retrieved successfully"
}
```

#### Response No Autenticado

```json
{
  "message": "Unauthenticated."
}
```

### POST /api/auth/logout

#### Response Exitosa

```txt
204 No Content
```

#### Response No Autenticado

```json
{
  "message": "Unauthenticated."
}
```

## Dependencias

- Registro o login implementado previamente.
- Tabla `users`.
- Sesiones configuradas con cookies HTTPOnly.
- Middleware de autenticacion basado en sesion.
- Configuracion CORS compatible con Angular y credenciales.
- Angular debe usar `withCredentials: true`.
- Angular debe manejar `401 Unauthorized` limpiando estado local y redirigiendo al login.
