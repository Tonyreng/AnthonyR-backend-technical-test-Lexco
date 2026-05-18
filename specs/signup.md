# Spec: Registro De Usuario

## Historia De Usuario

Como usuario, quiero registrarme en la aplicacion, para poder acceder a las funcionalidades privadas segun mi rol.

## Objetivo

Permitir que un visitante cree una cuenta en la aplicacion mediante un registro publico, validando sus datos, asignando el rol correspondiente y creando una sesion autenticada segura.

## Alcance

- Registro publico de usuarios no autenticados.
- Validacion de nombre, email, contrasena y confirmacion de contrasena.
- Validacion de email unico.
- Validacion de contrasena segura.
- Asignacion automatica de rol.
- Primer usuario registrado como `admin`.
- Usuarios posteriores como `user`.
- Inicio de sesion automatico despues del registro exitoso.
- Respuesta JSON con datos basicos del usuario registrado.
- Manejo de sesion mediante cookies seguras HTTPOnly.
- Respuestas de error claras para formularios Angular.

## Fuera De Alcance

- Recuperacion de contrasena.
- Verificacion de correo electronico.
- Seleccion manual de rol durante el registro publico.
- Cambio de roles desde el registro.
- Gestion de usuarios por administrador.
- Login manual.
- Logout.
- Perfil de usuario.
- Redirecciones implementadas desde backend.
- Interfaz visual del formulario.

## Reglas De Negocio

- El registro debe estar disponible para visitantes no autenticados.
- El usuario debe proporcionar `name`, `email`, `password` y confirmacion de contrasena.
- El email debe ser unico en el sistema.
- La contrasena debe tener minimo 8 caracteres.
- La contrasena debe incluir al menos una letra mayuscula.
- La contrasena debe incluir al menos un numero.
- La contrasena debe incluir al menos un caracter especial.
- La confirmacion de contrasena debe coincidir con la contrasena.
- Si no existe ningun usuario en la base de datos, el nuevo usuario debe recibir rol `admin`.
- Si ya existe al menos un usuario, el nuevo usuario debe recibir rol `user`.
- El rol no debe poder ser enviado ni manipulado desde el registro publico.
- El backend debe ser la unica fuente de verdad para la asignacion de roles.
- Despues de un registro exitoso, el usuario debe quedar autenticado automaticamente.
- El backend no debe retornar `password`, `remember_token` ni informacion sensible.
- El registro no debe crear flujos de recuperacion de contrasena.
- El registro no debe requerir verificacion de email.

## Flujo Principal

1. El visitante abre el formulario de registro en Angular.
2. El visitante ingresa nombre, email, contrasena y confirmacion de contrasena.
3. Angular envia la solicitud de registro al backend.
4. El backend valida los campos recibidos.
5. El backend verifica que el email no exista.
6. El backend determina si ya existen usuarios registrados.
7. Si no existen usuarios, asigna rol `admin`.
8. Si ya existen usuarios, asigna rol `user`.
9. El backend crea el usuario.
10. El backend inicia sesion automaticamente usando cookie HTTPOnly.
11. El backend responde con los datos basicos del usuario registrado.
12. Angular recibe la respuesta y redirige segun el rol del usuario.

## Flujos Alternativos

- Si el email ya existe, el backend rechaza el registro con error de validacion.
- Si la contrasena no cumple las reglas, el backend devuelve errores por campo.
- Si la confirmacion de contrasena no coincide, el backend devuelve error de validacion.
- Si faltan campos obligatorios, el backend devuelve error de validacion.
- Si ocurre un error no controlado, el backend devuelve una respuesta de error generica sin exponer detalles sensibles.

## Validaciones

- `name` es obligatorio.
- `name` debe ser texto valido.
- `email` es obligatorio.
- `email` debe tener formato valido.
- `email` debe ser unico.
- `password` es obligatorio.
- `password` debe tener minimo 8 caracteres.
- `password` debe incluir al menos una mayuscula.
- `password` debe incluir al menos un numero.
- `password` debe incluir al menos un caracter especial.
- `password_confirmation` es obligatorio.
- `password_confirmation` debe coincidir con `password`.

## Permisos Y Roles

- El endpoint de registro debe ser publico.
- Solo visitantes no autenticados deberian usar el registro.
- El usuario no puede definir su propio rol.
- El primer usuario del sistema recibe rol `admin`.
- Los usuarios posteriores reciben rol `user`.
- La asignacion de rol debe ocurrir en backend.
- El frontend puede usar el rol retornado para decidir la navegacion posterior.
- El backend no debe confiar en roles enviados desde el frontend.

## Estados

- `registered`: usuario creado correctamente.
- `authenticated`: usuario con sesion activa despues del registro.
- `validation_failed`: solicitud rechazada por errores de validacion.
- `email_already_exists`: solicitud rechazada porque el email ya esta registrado.
- `server_error`: error inesperado no controlado.

## Criterios De Aceptacion

- Dado que no existe ningun usuario en la base de datos, cuando un visitante se registra correctamente, entonces el usuario creado debe tener rol `admin`.
- Dado que ya existe al menos un usuario en la base de datos, cuando un visitante se registra correctamente, entonces el usuario creado debe tener rol `user`.
- Dado que un visitante intenta registrarse con un email existente, cuando envia el formulario, entonces el backend debe responder con error de validacion para `email`.
- Dado que un visitante intenta registrarse con una contrasena insegura, cuando envia el formulario, entonces el backend debe responder con errores de validacion para `password`.
- Dado que un visitante intenta enviar un rol dentro del registro, cuando el backend procesa la solicitud, entonces debe ignorar ese rol y asignar el rol segun la regla del primer usuario.
- Dado que el registro es exitoso, cuando el backend responde, entonces debe devolver los datos basicos del usuario sin incluir `password` ni `remember_token`.
- Dado que el registro es exitoso, cuando el backend responde, entonces debe dejar al usuario autenticado mediante cookie HTTPOnly.
- Dado que el usuario registrado tiene rol `admin`, cuando Angular recibe la respuesta, entonces podra redirigirlo al area administrativa.
- Dado que el usuario registrado tiene rol `user`, cuando Angular recibe la respuesta, entonces podra redirigirlo al catalogo de productos.

## Casos De Error

- `422 Unprocessable Entity`: campos obligatorios faltantes.
- `422 Unprocessable Entity`: email invalido.
- `422 Unprocessable Entity`: email ya registrado.
- `422 Unprocessable Entity`: contrasena insegura.
- `422 Unprocessable Entity`: confirmacion de contrasena incorrecta.
- `500 Internal Server Error`: error inesperado del servidor.

## Contrato De Datos Esperado

### Request

```json
{
  "name": "Anthony Rengifo",
  "email": "anthony@example.com",
  "password": "Password*123",
  "password_confirmation": "Password*123"
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
  "message": "User registered successfully"
}
```

### Response De Validacion

```json
{
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password must contain at least one uppercase letter, one number, and one special character."
    ]
  }
}
```

## Dependencias

- Tabla `users` con columna `role`.
- Configuracion de sesiones con cookies HTTPOnly.
- Configuracion CORS compatible con Angular y credenciales.
- Frontend Angular con formulario de registro.
- Validacion frontend opcional para mejorar UX.
- Validacion backend obligatoria como fuente de verdad.
