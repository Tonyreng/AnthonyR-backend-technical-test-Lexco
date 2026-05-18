# Spec: Inicio De Sesion

## Historia De Usuario

Como usuario, quiero iniciar sesion dentro de la aplicacion, para ser redirigido segun mi rol.

## Objetivo

Permitir que un usuario registrado acceda a la aplicacion mediante email y contrasena, creando una sesion segura con cookie HTTPOnly y devolviendo la informacion necesaria para que Angular redirija al usuario segun su rol.

## Alcance

- Login publico para usuarios no autenticados.
- Inicio de sesion usando `email` y `password`.
- Validacion de campos obligatorios.
- Validacion de formato de email.
- Autenticacion contra usuarios registrados.
- Creacion de sesion mediante cookie HTTPOnly.
- Regeneracion de sesion tras login exitoso.
- Respuesta JSON con datos basicos del usuario autenticado.
- Inclusion del `role` en la respuesta.
- Compatibilidad con Angular usando `withCredentials: true`.
- Errores claros para validacion.
- Error generico para credenciales invalidas.

## Fuera De Alcance

- Registro de usuarios.
- Recuperacion de contrasena.
- Verificacion de correo electronico.
- Login con nombre de usuario.
- Login con proveedores externos.
- Opcion recordarme.
- Redireccion ejecutada desde backend.
- Gestion de roles.
- Perfil de usuario.
- Logout.

## Reglas De Negocio

- El login debe estar disponible para usuarios no autenticados.
- El usuario debe proporcionar `email` y `password`.
- El email debe tener formato valido.
- El login debe autenticar contra el email registrado.
- No se debe permitir login con `name` ni otros identificadores.
- Si las credenciales son correctas, el backend debe iniciar sesion con cookie HTTPOnly.
- Si las credenciales son incorrectas, el backend debe responder con error generico.
- El error de credenciales no debe revelar si el email existe o si la contrasena es incorrecta.
- Despues de login exitoso, el backend debe regenerar la sesion para prevenir session fixation.
- Si el usuario ya tenia una sesion activa, el login exitoso reemplaza/regenera la sesion actual.
- El backend debe devolver los datos basicos del usuario autenticado.
- El backend debe incluir el `role` del usuario para permitir redireccion desde Angular.
- El backend no debe retornar `password`, `remember_token` ni informacion sensible.
- El backend no debe hacer redirecciones HTTP; la redireccion pertenece al frontend.

## Flujo Principal

1. El usuario abre el formulario de login en Angular.
2. El usuario ingresa email y contrasena.
3. Angular envia la solicitud de login al backend usando credenciales/cookies habilitadas.
4. El backend valida que `email` y `password` esten presentes.
5. El backend valida que `email` tenga formato valido.
6. El backend intenta autenticar las credenciales.
7. Si las credenciales son correctas, el backend regenera la sesion.
8. El backend deja al usuario autenticado mediante cookie HTTPOnly.
9. El backend responde con los datos basicos del usuario autenticado.
10. Angular lee el `role`.
11. Si el rol es `admin`, Angular redirige al dashboard administrativo.
12. Si el rol es `user`, Angular redirige al catalogo de productos.

## Flujos Alternativos

- Si falta el email, el backend responde con error de validacion.
- Si falta la contrasena, el backend responde con error de validacion.
- Si el email tiene formato invalido, el backend responde con error de validacion.
- Si las credenciales son incorrectas, el backend responde con error generico de autenticacion.
- Si el usuario ya tenia una sesion activa, el backend regenera la sesion y mantiene solo la nueva sesion actual.
- Si ocurre un error no controlado, el backend responde con error generico sin exponer informacion sensible.

## Validaciones

- `email` es obligatorio.
- `email` debe ser string.
- `email` debe tener formato valido.
- `password` es obligatorio.
- `password` debe ser string.

## Permisos Y Roles

- El endpoint de login debe ser publico.
- Solo usuarios registrados pueden iniciar sesion exitosamente.
- El backend debe autenticar al usuario antes de devolver datos.
- El backend debe devolver el `role` para que Angular decida la navegacion.
- El backend no debe confiar en roles enviados por el frontend.
- Angular podra redirigir a usuarios `admin` al dashboard administrativo.
- Angular podra redirigir a usuarios `user` al catalogo de productos.

## Estados

- `validation_failed`: solicitud rechazada por errores de validacion.
- `invalid_credentials`: credenciales incorrectas.
- `authenticated`: usuario autenticado correctamente.
- `session_regenerated`: sesion regenerada tras login exitoso.
- `server_error`: error inesperado no controlado.

## Criterios De Aceptacion

- Dado que un usuario registrado ingresa email y contrasena correctos, cuando envia el formulario, entonces el backend debe autenticarlo y responder con `200 OK`.
- Dado que el login es exitoso, cuando el backend responde, entonces debe devolver los datos basicos del usuario autenticado sin incluir `password` ni `remember_token`.
- Dado que el login es exitoso, cuando el backend responde, entonces debe incluir el `role` del usuario.
- Dado que el usuario autenticado tiene rol `admin`, cuando Angular recibe la respuesta, entonces podra redirigirlo al dashboard administrativo.
- Dado que el usuario autenticado tiene rol `user`, cuando Angular recibe la respuesta, entonces podra redirigirlo al catalogo de productos.
- Dado que el usuario envia credenciales invalidas, cuando el backend responde, entonces debe devolver `401 Unauthorized` con mensaje generico.
- Dado que el usuario envia un email inexistente, cuando el backend responde, entonces no debe revelar que el email no existe.
- Dado que el usuario envia contrasena incorrecta, cuando el backend responde, entonces no debe revelar que la contrasena es incorrecta.
- Dado que falta el email, cuando el usuario envia el formulario, entonces el backend debe devolver `422 Unprocessable Entity`.
- Dado que falta la contrasena, cuando el usuario envia el formulario, entonces el backend debe devolver `422 Unprocessable Entity`.
- Dado que el login es exitoso, cuando se crea la sesion, entonces la sesion debe ser regenerada para prevenir session fixation.
- Dado que el frontend usa Angular, cuando llama al endpoint, entonces debe poder enviar y recibir cookies usando `withCredentials: true`.

## Casos De Error

- `422 Unprocessable Entity`: email faltante.
- `422 Unprocessable Entity`: email invalido.
- `422 Unprocessable Entity`: password faltante.
- `401 Unauthorized`: credenciales invalidas.
- `500 Internal Server Error`: error inesperado del servidor.

## Contrato De Datos Esperado

### Request

```json
{
  "email": "anthony@example.com",
  "password": "Password*123"
}
```

### Response Exitosa

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
  "message": "User authenticated successfully"
}
```

### Response De Validacion

```json
{
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

### Response De Credenciales Invalidas

```json
{
  "message": "Invalid credentials"
}
```

## Dependencias

- Tabla `users`.
- Usuarios registrados previamente.
- Contrasenas almacenadas de forma segura usando hashing.
- Configuracion de sesiones con cookies HTTPOnly.
- Configuracion CORS compatible con Angular y credenciales.
- Frontend Angular con formulario de login.
- Angular debe usar `withCredentials: true`.
- Validacion backend obligatoria como fuente de verdad.
