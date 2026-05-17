# Lexco Backend

Backend Laravel 13 preparado para desarrollar la API REST de usuarios, productos y compras.

## Requisitos

- Docker
- Docker Compose

## Entorno

El proyecto usa Laravel Sail con PostgreSQL.

Variables principales:

- `APP_URL=http://localhost`
- `FRONTEND_URL=http://localhost:4200`
- `DB_CONNECTION=pgsql`
- `DB_HOST=pgsql`
- `DB_PORT=5432`
- `DB_DATABASE=laravel`
- `DB_USERNAME=sail`
- `DB_PASSWORD=password`
- `CORS_ALLOWED_ORIGINS="http://localhost:4200,http://127.0.0.1:4200"`

## Comandos

Levantar servicios:

```bash
docker compose up -d
```

Instalar dependencias:

```bash
docker compose exec -T laravel.test composer install
```

Ejecutar migraciones:

```bash
docker compose exec -T laravel.test php artisan migrate
```

Listar rutas:

```bash
docker compose exec -T laravel.test php artisan route:list
```

Ejecutar pruebas:

```bash
docker compose exec -T laravel.test php artisan test
```

## CORS

CORS está configurado en `config/cors.php` para aceptar peticiones con credenciales desde Angular en `http://localhost:4200`.

Las rutas API base están registradas en `routes/api.php`, separadas en:

- `routes/auth.php`
- `routes/users.php`
- `routes/products.php`

Todavía no hay endpoints, modelos ni controladores implementados.
