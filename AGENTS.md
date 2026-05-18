# AGENTS.md

Instrucciones para agentes que trabajen en el backend Laravel de esta prueba tecnica.

## Objetivo Del Backend

Construir una API REST en Laravel 13 para una aplicacion de gestion de usuarios y productos.

La API debe permitir:

- Gestion de usuarios.
- Gestion de productos.
- Compra de productos con afectacion de inventario.
- Autenticacion segura.
- Control de acceso basado en roles.

Toda implementacion debe aplicar buenas practicas de desarrollo, seguridad, arquitectura orientada a objetos y organizacion clara del proyecto.

## Stack Requerido

- Laravel 13.
- PostgreSQL.
- Git.
- Postman para pruebas de API.
- GitHub para control de versiones.

## Roles Del Sistema

La API debe manejar dos tipos de usuarios:

- `admin`: administrador del sistema.
- `user`: usuario regular.

Los usuarios regulares no deben acceder a funcionalidades administrativas.

Los administradores deben tener acceso a las funciones administrativas.

## Registro Y Rol Inicial

El flujo de registro debe contemplar la regla indicada para la aplicacion completa:

- Si no existe ningun usuario en la base de datos, el primer usuario registrado debe recibir rol `admin`.
- Todos los registros posteriores deben recibir rol `user` por defecto.
- El cambio manual de roles debe quedar restringido a administradores desde la gestion de usuarios.
- Esta regla debe vivir en backend para evitar que el frontend pueda manipular el rol inicial.

## Gestion De Usuarios

Debe existir un CRUD de usuarios con los campos minimos:

- `id`.
- `name`.
- `email`.
- `password`.
- `role` con valores `admin` o `user`.
- `created_at`.
- `updated_at`.

Funciones requeridas:

- Crear usuario.
- Listar usuarios.
- Consultar usuario.
- Actualizar usuario.
- Eliminar usuario.

Restriccion obligatoria:

- Solo administradores pueden gestionar usuarios.

## Gestion De Productos

Debe existir un CRUD de productos con los campos minimos:

- `id`.
- `name`.
- `description`.
- `price`.
- `stock`.
- `created_at`.
- `updated_at`.

Nota de integracion:

- La seccion frontend del PDF menciona `categoria` para productos.
- La seccion backend lista como campos obligatorios `name`, `description`, `price` y `stock`.
- Antes de implementar el modelo de producto, confirmar si `category` debe agregarse al backend para alinear el contrato con Angular.
- Si se agrega `category`, debe incluirse en migracion, Request Validation, respuestas JSON y documentacion Postman.

Funciones requeridas:

- Crear producto.
- Listar productos.
- Actualizar producto.
- Eliminar producto.

Restriccion obligatoria:

- Solo administradores pueden crear, editar o eliminar productos.

## Compra De Productos

Debe existir un servicio para comprar productos.

El servicio debe:

- Recibir el producto y la cantidad.
- Validar disponibilidad de inventario.
- Descontar el inventario.
- Retornar la respuesta de la compra.

Reglas obligatorias:

- Solo usuarios autenticados pueden comprar productos.
- No se puede comprar si el stock es insuficiente.
- La actualizacion del inventario debe ser segura y consistente.

## Autenticacion Y Seguridad

La autenticacion debe usar cookies HTTPOnly para manejar sesiones autenticadas.

Las contrasenas deben cumplir:

- Minimo 8 caracteres.
- Al menos una letra mayuscula.
- Al menos un numero.
- Al menos un caracter especial.

La API debe protegerse contra:

- SQL Injection.
- Peticiones invalidas.
- Acceso no autorizado.

Para esto se debe utilizar:

- Request Validation.
- Middleware.
- Sanitizacion de datos.
- Validaciones explicitas en Requests o servicios segun corresponda.

No exponer secretos, tokens, `APP_KEY`, credenciales reales ni variables sensibles en codigo, README, logs o respuestas de API.

## Middleware Requerido

El proyecto debe implementar minimo 2 middleware:

- Middleware para validar que exista una sesión válida y bloquear usuarios no autenticados.
- Middleware de control de roles.

El middleware de roles debe impedir que usuarios `user` accedan a funcionalidades administrativas.

## Arquitectura Del Proyecto

El desarrollo debe aplicar principios de Programacion Orientada a Objetos.

Organizar el codigo usando:

- Controllers.
- Services.
- Requests.
- Middleware.
- Repositories, opcional pero recomendado.

Reglas de organizacion:

- Los Controllers deben coordinar la peticion y respuesta, no contener logica de negocio pesada.
- La logica de negocio debe vivir en Services.
- Las validaciones de entrada deben vivir en Form Requests cuando aplique.
- El acceso a datos puede aislarse en Repositories si ayuda a mantener claridad.
- Mantener respuestas JSON consistentes para exito y error.

## Organizacion De Rutas

Las rutas deben estar separadas por dominio:

- `routes/auth.php` para autenticacion.
- `routes/users.php` para usuarios.
- `routes/products.php` para productos.

Al menos un grupo de rutas debe registrarse desde la configuracion central de rutas de Laravel.

Las rutas API deben estar preparadas para consumo desde un frontend Angular.

## Base De Datos

La base de datos requerida es PostgreSQL.

Debe incluir:

- Migraciones.
- Relaciones necesarias.
- Seeders cuando aporten valor al arranque o pruebas.

Las migraciones deben representar correctamente los campos requeridos por el PDF.

## Variables De Entorno

La aplicacion debe utilizar correctamente variables definidas en `.env` para:

- Conexion a base de datos.
- Configuracion de CORS.
- Configuracion de cookies.
- URLs del backend y frontend.

No hardcodear configuraciones que deban venir del entorno.

Mantener `.env.example` actualizado con valores de ejemplo seguros.

## CORS

Debe existir configuracion CORS para permitir peticiones desde el frontend.

La configuracion debe soportar credenciales cuando se usen cookies HTTPOnly.

Validar que las respuestas CORS incluyan los headers correctos para el origen del frontend permitido.

## Contrato Con Frontend Angular

El backend debe disenarse para ser consumido por una SPA Angular 21.

Reglas generales de integracion:

- El frontend local esperado es `http://localhost:4200`.
- La API local esperada es `http://localhost/api` cuando se usa Sail/Compose con puerto 80.
- Las rutas API deben responder JSON.
- No devolver vistas Blade desde endpoints API.
- No depender de estado almacenado en el cliente para permisos, roles o stock.
- El frontend debe poder usar `withCredentials: true` para enviar cookies de sesion.
- CORS debe permitir el origen del frontend y credenciales.
- Las respuestas deben ser estables para que Angular pueda tiparlas con interfaces.

Autenticacion esperada por Angular:

- Login con email o identificador equivalente y password.
- Register con name, email y password segura.
- Sesion manejada por cookie HTTPOnly.
- Endpoint para obtener el usuario autenticado actual.
- Endpoint para cerrar sesion.
- El frontend no debe recibir ni almacenar tokens sensibles si la autenticacion se basa en cookies.
- Las cookies deben configurarse mediante variables de entorno para ambiente local y produccion.

Roles esperados por Angular:

- El frontend usara `AuthGuard` para bloquear rutas privadas.
- El frontend usara `RoleGuard` para bloquear rutas admin.
- El backend debe ser la fuente real de autorizacion; los guards del frontend solo mejoran UX.
- Cualquier endpoint administrativo debe validar rol `admin` en backend.
- Cualquier endpoint de compra debe validar usuario autenticado en backend.

Pantallas frontend que dependen del backend:

- Login requiere endpoint de autenticacion.
- Register requiere endpoint de registro con regla de primer usuario administrador.
- Dashboard admin requiere datos de usuarios y productos.
- User Management requiere CRUD completo de usuarios y cambio de roles.
- Product Management requiere CRUD completo de productos.
- Profile requiere endpoint para consultar y actualizar datos del usuario autenticado.
- Catalogo requiere listar productos disponibles para usuarios autenticados.
- Carrito requiere productos con precio y stock para calcular totales en frontend.
- Compra requiere endpoint que valide stock y descuente inventario.

Formato recomendado de respuestas JSON:

```json
{
    "data": {},
    "message": "Operation completed successfully"
}
```

Formato recomendado de errores JSON:

```json
{
    "message": "Validation failed",
    "errors": {
        "field": ["Error message"]
    }
}
```

Codigos HTTP esperados:

- `200 OK` para consultas y actualizaciones exitosas.
- `201 Created` para creacion exitosa.
- `204 No Content` para eliminacion o logout sin cuerpo.
- `400 Bad Request` para peticiones mal formadas cuando no aplique `422`.
- `401 Unauthorized` para usuarios no autenticados.
- `403 Forbidden` para usuarios autenticados sin permisos.
- `404 Not Found` para recursos inexistentes.
- `409 Conflict` para conflictos de negocio como stock insuficiente si se decide no usar `422`.
- `422 Unprocessable Entity` para errores de validacion.
- `500 Internal Server Error` solo para errores no controlados.

Paginacion y listados:

- Los listados de usuarios y productos deben prepararse para paginacion.
- Si se implementan filtros o busqueda, deben recibirse por query params.
- Mantener nombres de campos consistentes para facilitar interfaces TypeScript en Angular.

Carrito y compra:

- El contador del carrito y total en tiempo real viven en frontend con Signals o BehaviorSubjects.
- El backend no debe confiar en totales enviados por el frontend.
- El backend debe recalcular precio, disponibilidad y stock desde la base de datos al comprar.
- El endpoint de compra debe recibir producto y cantidad, no aceptar stock ni precio como fuente de verdad desde Angular.

Perfil de usuario:

- Debe existir soporte backend para visualizar y editar datos del usuario autenticado.
- La edicion de perfil no debe permitir escalar privilegios modificando `role` desde endpoints de usuario regular.

Validaciones compartidas con frontend:

- El frontend puede validar password para UX, pero backend debe repetir la validacion obligatoriamente.
- Backend debe devolver errores `422` suficientemente claros para ser mostrados por formularios reactivos.
- La edicion dinamica del frontend requiere endpoints que devuelvan datos actuales del recurso antes de editar.

Consideraciones para cookies en SPA:

- Usar cookies HTTPOnly para sesion autenticada.
- Mantener `SESSION_HTTP_ONLY=true`.
- Configurar `SESSION_SAME_SITE` segun ambiente.
- Para desarrollo local con mismo host y puertos distintos, `lax` suele ser suficiente.
- Si frontend y backend quedan en dominios distintos con HTTPS, evaluar `same_site=none` y cookie secure.
- CORS debe tener `supports_credentials=true`; no usar `allowed_origins=*` con credenciales.

Contrato de datos minimo esperado por frontend:

Usuario:

```json
{
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "role": "user",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

Producto:

```json
{
    "id": 1,
    "name": "Product Name",
    "description": "Product description",
    "price": 1000,
    "stock": 10,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

Compra:

```json
{
    "product_id": 1,
    "quantity": 2
}
```

Reglas de compatibilidad:

- No cambiar nombres de campos de respuesta sin actualizar frontend, README y Postman.
- No retornar `password`, `remember_token` ni informacion sensible en respuestas de usuario.
- Documentar cualquier cambio de contrato API antes de implementarlo.

## Documentacion Del Codigo

Las funciones relevantes deben estar documentadas con PHPDoc, incluyendo:

- Descripcion.
- Parametros.
- Valor de retorno.
- Autor.
- Fecha `@since`.

Ejemplo esperado:

```php
/**
 * Create a new product.
 *
 * @param array $data
 * @return Product
 * @author Su nombre
 * @since 2024/01
 */
public function createProduct(array $data)
{
    //
}
```

## Pruebas De API

Las pruebas de API deben realizarse con Postman.

Debe incluirse:

- Coleccion de Postman exportada.
- Ejemplos de requests.
- Ejemplos de responses.

Cuando se implementen endpoints, validar casos de exito, validacion fallida, acceso no autorizado y restricciones por rol.

## README Y Entregables

El `README.md` debe mantenerse actualizado con:

- Instrucciones de instalacion.
- Configuracion del proyecto.
- Variables de entorno necesarias sin exponer secretos reales.
- Como ejecutar migraciones.
- Como probar la API.

Entregables esperados:

- Repositorio en GitHub.
- Codigo fuente completo.
- Migraciones de base de datos.
- Coleccion de Postman.
- README completo.

## Control De Versiones

El proyecto debe subirse a GitHub.

Debe aplicarse flujo Gitflow:

- Rama `main`.
- Rama `develop`.
- Ramas `feature/*`.

La prueba requiere minimo 7 commits.

No crear commits automaticamente a menos que el usuario lo solicite explicitamente.

## Criterios De Evaluacion

Priorizar:

- Calidad del codigo.
- Buenas practicas.
- Organizacion del proyecto.
- Seguridad de la aplicacion.
- Uso adecuado de Laravel.
- Implementacion correcta de control de roles.
- Aplicacion correcta de Gitflow.
- Claridad en la documentacion.

## Reglas Para Agentes

- Trabajar solo dentro de `backend/` salvo instruccion explicita del usuario.
- No implementar modelos, APIs, autenticacion ni logica de negocio si la tarea solo pide configuracion o documentacion.
- No modificar archivos generados o de dependencias en `vendor/` salvo reparacion del entorno solicitada o necesaria.
- No exponer secretos en documentacion, ejemplos, respuestas o commits.
- Ejecutar validaciones relevantes despues de cambios de configuracion o codigo.
- Preferir cambios pequenos, claros y alineados con Laravel.
- Mantener la estructura preparada para Angular como cliente frontend.
