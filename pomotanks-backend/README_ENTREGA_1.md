# PomoTanks Backend — Entrega 1 de 3

## Qué contiene esta entrega

- ✅ Configuración de Sanctum + CORS para SPA Vue
- ✅ Migraciones de todas las tablas
- ✅ Modelos con relaciones y accessor `esPremium`
- ✅ Controllers: Auth, Configuracion, Materia, Sesion, Progreso, Hito
- ✅ Rutas en `api.php`
- ✅ Seeder con usuario de prueba y datos de los últimos 7 días

---

## PASO 0 — Verificar Sanctum

Comprueba si existe `config/sanctum.php` en tu proyecto:

```bash
ls config/sanctum.php
```

- **Si existe** → sáltate el paso de publicar.
- **Si NO existe**, publícalo:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

---

## PASO 1 — Copiar archivos

Descomprime el ZIP y copia cada carpeta sobre tu proyecto Laravel:

```
config/cors.php           → reemplaza el existente
config/sanctum.php        → reemplaza el existente (o créalo si no existía)
database/migrations/      → copia los 7 archivos nuevos (no borres los de Laravel)
database/seeders/DatabaseSeeder.php → reemplaza el existente
app/Models/               → copia los 6 modelos nuevos + reemplaza User.php
app/Http/Controllers/     → copia los 6 controllers
routes/api.php            → reemplaza el existente
```

---

## PASO 2 — Modificar .env

Abre tu `.env` y añade / modifica estas líneas
(el fichero `ENV_ADDITIONS.txt` las tiene listas para copiar):

```env
APP_URL=http://localhost:8000

SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIE=false

SANCTUM_STATEFUL_DOMAINS=localhost:5173
FRONTEND_URL=http://localhost:5173
```

---

## PASO 3 — Verificar Kernel.php

Abre `app/Http/Kernel.php` y asegúrate de que en el grupo `api` está el middleware de Sanctum:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

Si no está la línea de `EnsureFrontendRequestsAreStateful`, añádela **al principio** del array `api`.

---

## PASO 4 — Instalar dependencia doctrine/dbal (para renombrar columna)

La migración que renombra `name` → `nombre` en la tabla users necesita esta librería:

```bash
composer require doctrine/dbal
```

---

## PASO 5 — Ejecutar migraciones y seeder

```bash
# Ejecutar SOLO las migraciones nuevas (no borra datos existentes)
php artisan migrate

# Si es base de datos fresca y quieres empezar de cero:
php artisan migrate:fresh --seed

# Si ya tienes datos y solo quieres el seeder:
php artisan db:seed
```

---

## PASO 6 — Limpiar caché de configuración

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## PASO 7 — Verificar que las rutas están bien

```bash
php artisan route:list --path=api
```

Deberías ver todas las rutas de PomoTanks listadas.

---

## PASO 8 — Levantar el servidor

```bash
php artisan serve
# Escucha en http://localhost:8000
```

---

## Credenciales del usuario de prueba

```
Email:    test@pomotanks.dev
Password: password
```

---

## Mensaje de commit

```
feat: backend entrega 1/3 — sanctum, migraciones, modelos y controllers base

- Configura Sanctum SPA con CORS para localhost:5173
- Añade campo nombre y es_premium a tabla users
- Crea tablas: configuraciones, materias, sesiones, periodos, progresos, hitos
- Modelos con relaciones y accessor esPremium (camelCase en JSON)
- Controllers: Auth (login/register/logout/user), Configuracion, Materia,
  Sesion (con hitos automáticos), Progreso (gráficos por periodo), Hito
- Rutas api.php completas con middleware auth:sanctum
- Seeder con usuario test y 7 días de datos de prueba
```
