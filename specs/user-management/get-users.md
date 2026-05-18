# Spec: Listar Usuarios Registrados

## Historia De Usuario

Como administrador, quiero listar los usuarios registrados, para gestionarlos.

## Objetivo

Permitir que un administrador autenticado consulte una lista paginada de usuarios registrados, con soporte para busqueda y filtros basicos, sin exponer informacion sensible.

## Alcance

- Endpoint privado para listar usuarios.
- Acceso exclusivo para usuarios con rol `admin`.
- Bloqueo de usuarios no autenticados.
- Bloqueo de usuarios autenticados sin rol `admin`.
- Respuesta JSON con usuarios registrados.
- Paginacion mediante `page` y `per_page`.
- Busqueda opcional por `name` o `email`.
- Filtro opcional por `role`.
- Ordenamiento por fecha de creacion descendente.
- Metadata de paginacion para tablas en Angular.
- Compatibilidad con sesion por cookie HTTPOnly y `withCredentials: true`.

## Fuera De Alcance

- Crear usuarios.
- Editar usuarios.
- Eliminar usuarios.
- Cambiar roles.
- Exportar usuarios.
- Busqueda avanzada por multiples campos adicionales.
- Ordenamiento configurable desde frontend.
- Perfil del usuario autenticado.
- Gestion de permisos mas alla de `admin` y `user`.

## Reglas De Negocio

- Solo usuarios autenticados con rol `admin` pueden listar usuarios.
- Usuarios no autenticados deben recibir `401 Unauthorized`.
- Usuarios autenticados con rol distinto de `admin` deben recibir `403 Forbidden`.
- El listado debe devolver unicamente campos seguros.
- El listado no debe devolver `password`.
- El listado no debe devolver `remember_token`.
- El endpoint debe usar sesion autenticada basada en cookie HTTPOnly.
- El endpoint debe ser compatible con Angular usando `withCredentials: true`.
- La respuesta debe estar paginada.
- Si no hay usuarios, la respuesta debe devolver una lista vacia.
- La busqueda debe aplicar sobre `name` y `email`.
- El filtro por rol solo debe aceptar `admin` o `user`.
- El orden por defecto debe ser `created_at` descendente.
- `per_page` debe tener valor por defecto `10`.
- `per_page` no debe exceder `100`.

## Flujo Principal

1. El administrador inicia sesion.
2. Angular llama `GET /api/users` con credenciales habilitadas.
3. El backend valida que exista una sesion autenticada.
4. El backend valida que el usuario autenticado tenga rol `admin`.
5. El backend lee los query params opcionales `page`, `per_page`, `search` y `role`.
6. El backend valida los query params.
7. El backend consulta los usuarios aplicando filtros opcionales.
8. El backend ordena los resultados por `created_at` descendente.
9. El backend pagina los resultados.
10. El backend responde con la lista de usuarios y metadata de paginacion.
11. Angular renderiza la tabla de gestion de usuarios.

## Flujos Alternativos

- Si no hay sesion activa, el backend responde `401 Unauthorized`.
- Si el usuario autenticado tiene rol `user`, el backend responde `403 Forbidden`.
- Si `role` tiene un valor distinto de `admin` o `user`, el backend responde `422 Unprocessable Entity`.
- Si `per_page` excede `100`, el backend responde `422 Unprocessable Entity`.
- Si no hay usuarios que coincidan con los filtros, el backend responde con lista vacia.
- Si ocurre un error no controlado, el backend responde con error generico sin exponer informacion sensible.

## Validaciones

- `page` es opcional.
- `page` debe ser entero.
- `page` debe ser mayor o igual a `1`.
- `per_page` es opcional.
- `per_page` debe ser entero.
- `per_page` debe ser mayor o igual a `1`.
- `per_page` debe ser menor o igual a `100`.
- `search` es opcional.
- `search` debe ser string.
- `role` es opcional.
- `role` solo puede ser `admin` o `user`.

## Permisos Y Roles

- El endpoint requiere usuario autenticado.
- El endpoint requiere rol `admin`.
- Usuarios con rol `user` no pueden listar usuarios.
- El backend debe validar el rol; no debe depender unicamente de guards del frontend.
- Angular puede usar `RoleGuard` para UX, pero la autorizacion real ocurre en backend.

## Estados

- `authenticated_admin`: usuario autenticado con rol `admin`.
- `authenticated_user`: usuario autenticado con rol `user`.
- `guest`: usuario no autenticado.
- `validation_failed`: query params invalidos.
- `users_found`: existen usuarios para la consulta.
- `users_empty`: no existen usuarios para la consulta.
- `forbidden`: usuario autenticado sin permiso.
- `server_error`: error inesperado no controlado.

## Criterios De Aceptacion

- Dado que un administrador autenticado llama `GET /api/users`, cuando no envia filtros, entonces el backend debe responder `200 OK` con usuarios paginados.
- Dado que un administrador autenticado llama `GET /api/users`, cuando la respuesta contiene usuarios, entonces cada usuario debe incluir `id`, `name`, `email`, `role`, `created_at` y `updated_at`.
- Dado que un administrador autenticado llama `GET /api/users`, cuando el backend responde, entonces no debe incluir `password` ni `remember_token`.
- Dado que un usuario no autenticado llama `GET /api/users`, entonces el backend debe responder `401 Unauthorized`.
- Dado que un usuario autenticado con rol `user` llama `GET /api/users`, entonces el backend debe responder `403 Forbidden`.
- Dado que un administrador autenticado envia `search`, cuando existen usuarios cuyo `name` o `email` coincide, entonces el backend debe devolver solo esos usuarios.
- Dado que un administrador autenticado envia `role=admin`, cuando existen usuarios administradores, entonces el backend debe devolver solo usuarios con rol `admin`.
- Dado que un administrador autenticado envia `role=user`, cuando existen usuarios regulares, entonces el backend debe devolver solo usuarios con rol `user`.
- Dado que un administrador autenticado envia `role=invalid`, entonces el backend debe responder `422 Unprocessable Entity`.
- Dado que un administrador autenticado envia `per_page=101`, entonces el backend debe responder `422 Unprocessable Entity`.
- Dado que no existen usuarios que coincidan con los filtros, entonces el backend debe responder `200 OK` con `data` vacio y metadata de paginacion.
- Dado que Angular consume el endpoint, cuando recibe la respuesta, entonces debe contar con metadata suficiente para construir una tabla paginada.

## Casos De Error

- `401 Unauthorized`: usuario no autenticado.
- `403 Forbidden`: usuario autenticado sin rol `admin`.
- `422 Unprocessable Entity`: query params invalidos.
- `500 Internal Server Error`: error inesperado del servidor.

## Contrato De Datos Esperado

### Request

```http
GET /api/users?page=1&per_page=10&search=anthony&role=admin
```

### Response Exitosa

```json
{
  "data": [
    {
      "id": 1,
      "name": "Anthony Rengifo",
      "email": "anthony@example.com",
      "role": "admin",
      "created_at": "2026-05-17T00:00:00.000000Z",
      "updated_at": "2026-05-17T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  },
  "message": "Users retrieved successfully"
}
```

### Response Sin Resultados

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 0,
    "last_page": 1
  },
  "message": "Users retrieved successfully"
}
```

### Response No Autenticado

```json
{
  "message": "Unauthenticated."
}
```

### Response Sin Permisos

```json
{
  "message": "Forbidden."
}
```

### Response De Validacion

```json
{
  "message": "Validation failed",
  "errors": {
    "role": [
      "The selected role is invalid."
    ]
  }
}
```

## Dependencias

- Autenticacion con sesion HTTPOnly implementada.
- Endpoint `login` implementado.
- Endpoint `me` implementado.
- Tabla `users` con columna `role`.
- Middleware de autenticacion.
- Middleware de rol `admin`.
- Angular debe usar `withCredentials: true`.
- Angular debe manejar respuestas `401`, `403` y `422`.
