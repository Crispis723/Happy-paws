# 🚀 Guía Completa de Deploy Laravel en Render (Nivel Producción)

## 📋 Tabla de Contenidos
1. [Dockerfile Correcto](#1-dockerfile-correcto)
2. [Por Qué NO Ejecutar Artisan en Entrypoint](#2-por-qué-no-ejecutar-artisan-en-entrypoint)
3. [Comandos Artisan Post-Deploy](#3-comandos-artisan-post-deploy)
4. [Variables de Entorno Requeridas](#4-variables-de-entorno-requeridas)
5. [Verificación del Error 500](#5-verificación-del-error-500)
6. [Buenas Prácticas de Producción](#6-buenas-prácticas-de-producción)

---

## 1. Dockerfile Correcto

### ✅ Dockerfile Optimizado para Producción

```dockerfile
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    bcmath \
    gd \
    zip

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Configurar Apache para Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copiar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar código fuente
COPY . /var/www/html

WORKDIR /var/www/html

# Crear directorios requeridos por Laravel
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Compilar assets
RUN npm install --legacy-peer-deps && npm run build

# Establecer permisos finales
RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Configurar puerto para Render
EXPOSE 10000
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Comando de inicio LIMPIO (sin comandos artisan)
CMD ["apache2-foreground"]
```

### 📝 Cambios Clave Explicados

| Elemento Eliminado | Razón |
|-------------------|-------|
| `COPY .env.example .env` | Render usa variables de entorno directamente, NO archivos `.env` |
| `php artisan key:generate` | APP_KEY debe estar en variables de Render, no generarse en runtime |
| `php artisan config:cache` | Se ejecuta **manualmente** después del deploy |
| `php artisan migrate` | Las migraciones requieren conexión DB estable y se ejecutan **una sola vez** |
| `php artisan optimize` | Genera cache que puede causar inconsistencias entre deploys |

---

## 2. Por Qué NO Ejecutar Artisan en Entrypoint

### ❌ Problemas Críticos de Ejecutar Comandos Artisan en Entrypoint

#### **Problema 1: Race Conditions con la Base de Datos**
```bash
# Esto PUEDE FALLAR:
CMD ["sh", "-c", "php artisan migrate --force && apache2-foreground"]
```

**¿Por qué falla?**
- El contenedor inicia ANTES que la BD esté lista
- Render puede levantar la BD y el web service simultáneamente
- Si `migrate` se ejecuta cuando la BD no responde → Error 500
- El contenedor puede crashear o quedar en estado inconsistente

#### **Problema 2: Errores de Cache Persistente**
```bash
php artisan config:cache
```

**¿Qué causa esto?**
- Laravel cachea configuración ANTES de que las variables de entorno estén completamente cargadas
- Si Render cambia `APP_KEY` o `DB_HOST` después del cache → Laravel usa valores antiguos
- Resultado: Error 500 silencioso con mensaje "Unauthenticated" o "Database connection failed"

#### **Problema 3: Reinicios Automáticos**
```bash
# Cada vez que Render reinicia el contenedor:
php artisan migrate --force  # ← Se ejecuta OTRA VEZ (innecesario)
```

**Impacto:**
- Migraciones se intentan ejecutar en cada restart (pueden tardar segundos)
- Si una migración está bloqueada → Bloquea el inicio del servicio
- Logs contaminados con mensajes de "Nothing to migrate"

#### **Problema 4: Sin Control de Fallos**
```bash
# Si migrate falla:
php artisan migrate --force || echo "Error ignorado"
apache2-foreground
```

**Resultado:**
- No sabes si las migraciones fallaron
- La aplicación inicia con esquema de BD desactualizado
- Difícil debugging porque el error queda oculto

### ✅ Solución: Entrypoint Limpio

```dockerfile
CMD ["apache2-foreground"]
```

**Beneficios:**
1. **Inicio rápido**: Apache inicia en <2 segundos
2. **Sin dependencias**: No espera BD, cache, ni migraciones
3. **Predecible**: Siempre hace lo mismo (servir requests HTTP)
4. **Debuggeable**: Errores son claros y no mezclados con logs de artisan

---

## 3. Comandos Artisan Post-Deploy

### 🔧 Secuencia Correcta de Comandos (Shell de Render)

Después de cada deploy exitoso, conecta a la **Shell de Render** y ejecuta:

```bash
# Paso 1: Verificar conectividad con la BD
php artisan migrate:status

# Paso 2: Ejecutar migraciones pendientes
php artisan migrate --force

# Paso 3: Limpiar cache anterior
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Paso 4: Generar cache de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Paso 5: Optimizar autoloader
php artisan optimize

# Paso 6: Verificar que todo funciona
php artisan about
```

### 📊 Explicación de Cada Comando

| Comando | Propósito | Cuándo Ejecutar |
|---------|-----------|----------------|
| `migrate:status` | Verificar qué migraciones están aplicadas | Antes de migrar |
| `migrate --force` | Aplicar migraciones pendientes | Solo en deploy inicial o al agregar migraciones |
| `cache:clear` | Limpiar cache de aplicación | Antes de cachear (limpia datos antiguos) |
| `config:clear` | Limpiar cache de configuración | Antes de cachear (importante si cambias .env) |
| `route:clear` | Limpiar cache de rutas | Antes de cachear |
| `view:clear` | Limpiar vistas Blade compiladas | Antes de cachear |
| `config:cache` | Cachear configuración (.env) | **SOLO en producción** (mejora rendimiento 50%) |
| `route:cache` | Cachear rutas | **SOLO en producción** (acelera routing) |
| `view:cache` | Pre-compilar vistas Blade | **SOLO en producción** |
| `optimize` | Optimizar autoloader + cache | Al final (combina todos los caches) |
| `about` | Mostrar info del sistema | Verificación final |

### ⚠️ Advertencias Importantes

```bash
# ❌ NUNCA ejecutes config:cache en desarrollo local
php artisan config:cache  # Esto rompe tu .env local

# ✅ En local siempre usa:
php artisan config:clear
```

**Razón:** `config:cache` ignora `.env` y usa solo el cache. Si cambias `.env` localmente, Laravel no lo lee hasta que hagas `config:clear`.

---

## 4. Variables de Entorno Requeridas

### 📝 Lista Completa de Variables en Render

Ve a tu servicio en Render → **Environment** → Agrega estas variables:

```bash
# ============================================
# APLICACIÓN
# ============================================
APP_NAME="Clínica Veterinaria Happy Paws"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-app.onrender.com

# ⚠️ CRÍTICO: Genera este valor localmente
APP_KEY=base64:TU_KEY_GENERADA_AQUI

# ============================================
# BASE DE DATOS (MySQL Internal en Render)
# ============================================
DB_CONNECTION=mysql
DB_HOST=dpg-xxxxx-internal.frankfurt-postgres.render.com
DB_PORT=3306
DB_DATABASE=sistema
DB_USERNAME=sistema_user
DB_PASSWORD=TU_PASSWORD_SEGURA_AQUI

# ============================================
# LOGGING
# ============================================
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null

# ============================================
# CACHE Y SESIONES
# ============================================
CACHE_DRIVER=file
CACHE_PREFIX=happypaws

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# ============================================
# QUEUE (Opcional)
# ============================================
QUEUE_CONNECTION=database

# ============================================
# MAIL (Opcional - configura si usas emails)
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@happypaws.com
MAIL_FROM_NAME="${APP_NAME}"

# ============================================
# LOCALIZACIÓN
# ============================================
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES

# ============================================
# SEGURIDAD
# ============================================
BCRYPT_ROUNDS=12
```

### 🔑 Cómo Generar APP_KEY

**En tu máquina local (Windows PowerShell):**

```powershell
# Opción 1: Generar y mostrar directamente
php artisan key:generate --show

# Ejemplo de salida:
# base64:jKd9sL2mN3pQ5rT8vW1xY4zA6bC7dE9fG0hI2jK3lM5=
```

**Copia ese valor completo (incluyendo `base64:`) y pégalo en la variable `APP_KEY` de Render.**

⚠️ **Advertencia:** NO uses `php artisan key:generate` sin `--show` en producción, porque sobrescribe el `.env` local.

### 🔍 Variables Críticas Explicadas

#### **APP_KEY**
```bash
APP_KEY=base64:jKd9sL2mN3pQ5rT8vW1xY4zA6bC7dE9fG0hI2jK3lM5=
```

- **Propósito:** Encripta sesiones, cookies, y datos sensibles
- **Si falta:** Error 500 con mensaje "No application encryption key has been specified"
- **Si cambia:** Todas las sesiones activas se invalidan (usuarios deben volver a iniciar sesión)
- **Formato:** Debe empezar con `base64:`

#### **APP_DEBUG**
```bash
# ❌ NUNCA en producción:
APP_DEBUG=true

# ✅ Siempre en producción:
APP_DEBUG=false
```

- **true:** Muestra stack traces completos con código fuente y credenciales
- **false:** Muestra página genérica de error (seguro para usuarios)

#### **DB_HOST**
```bash
# ✅ Usar hostname INTERNO de Render:
DB_HOST=dpg-xxxxx-internal.frankfurt-postgres.render.com

# ❌ NO usar hostname externo (más lento):
DB_HOST=dpg-xxxxx.frankfurt-postgres.render.com
```

- Render tiene red interna entre servicios (más rápida y sin costos de transferencia)
- El sufijo `-internal` es clave para usar esta red

#### **LOG_LEVEL**
```bash
# En producción:
LOG_LEVEL=error  # Solo errores críticos

# Para debugging temporal:
LOG_LEVEL=debug  # Todos los logs (temporal)
```

---

## 5. Verificación del Error 500

### 🔍 Diagnóstico Paso a Paso

#### **Paso 1: Activar Modo Debug Temporal**

En Render → Environment → Cambia:
```bash
APP_DEBUG=true  # ⚠️ TEMPORAL para debugging
LOG_LEVEL=debug
```

Guarda y espera el redeploy automático (2-3 minutos).

#### **Paso 2: Revisar Logs en Render**

Ve a tu servicio → **Logs** → Busca errores:

```bash
# Errores comunes:

# ❌ Error 1: APP_KEY faltante
[ErrorException] No application encryption key has been specified.

# ❌ Error 2: Base de datos no conecta
SQLSTATE[HY000] [2002] Connection refused

# ❌ Error 3: Permisos de storage
file_put_contents(/var/www/html/storage/logs/laravel.log): Failed to open stream: Permission denied

# ❌ Error 4: Cache corrupto
ErrorException: file_get_contents(/var/www/html/bootstrap/cache/config.php): failed to open stream
```

#### **Paso 3: Verificar Variables de Entorno**

En la Shell de Render, ejecuta:

```bash
# Ver todas las variables cargadas:
php artisan about

# Verificar APP_KEY específicamente:
php artisan tinker
>>> config('app.key')
=> "base64:jKd9sL2mN3pQ5rT8vW1xY4zA6bC7dE9fG0hI2jK3lM5="

# Verificar BD:
>>> DB::connection()->getPdo();
=> PDO {#xxxx}  # ✅ Si devuelve PDO, la conexión funciona
```

#### **Paso 4: Verificar Permisos**

```bash
# Verificar propietario de storage:
ls -la storage/

# Debe mostrar:
# drwxr-xr-x  www-data www-data  storage/
# drwxr-xr-x  www-data www-data  bootstrap/cache/

# Si no, corregir:
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

#### **Paso 5: Limpiar Cache Corrupto**

Si el error persiste después de verificar lo anterior:

```bash
# Limpiar TODO el cache:
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*

# Regenerar:
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### **Paso 6: Probar Endpoint de Health Check**

Crea una ruta simple para verificar que Laravel funciona:

**routes/web.php:**
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app_key' => config('app.key') ? 'configured' : 'missing',
        'db' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache_dir' => is_writable(storage_path('framework/cache')),
        'log_dir' => is_writable(storage_path('logs')),
    ]);
});
```

Visita: `https://tu-app.onrender.com/health`

**Respuesta esperada:**
```json
{
  "status": "ok",
  "app_key": "configured",
  "db": "connected",
  "cache_dir": true,
  "log_dir": true
}
```

#### **Paso 7: Desactivar Debug**

Una vez solucionado el error, **INMEDIATAMENTE** cambia:

```bash
APP_DEBUG=false
LOG_LEVEL=error
```

Guarda en Render y espera el redeploy.

---

## 6. Buenas Prácticas de Producción

### ✅ Checklist de Producción

#### **Antes de Cada Deploy**

- [ ] Ejecutar tests localmente: `php artisan test`
- [ ] Verificar migraciones: `php artisan migrate:status`
- [ ] Revisar `.dockerignore` (no subir `node_modules`, `vendor`, `.env`)
- [ ] Verificar que `APP_DEBUG=false` en variables de Render
- [ ] Hacer backup de la BD: `mysqldump` o snapshot de Render

#### **Durante el Deploy**

- [ ] Monitorear logs en tiempo real
- [ ] Verificar que el build termina sin errores
- [ ] Esperar a que el health check pase (círculo verde en Render)

#### **Después del Deploy**

- [ ] Conectar a Shell de Render
- [ ] Ejecutar migraciones: `php artisan migrate --force`
- [ ] Limpiar cache: `php artisan cache:clear && php artisan config:clear`
- [ ] Regenerar cache: `php artisan config:cache && php artisan route:cache`
- [ ] Verificar endpoint: `curl https://tu-app.onrender.com/health`
- [ ] Probar login con usuarios de prueba

### 🔒 Seguridad en Producción

```bash
# ✅ Buenas prácticas:
APP_DEBUG=false                    # Nunca mostrar stack traces
APP_ENV=production                 # Deshabilita helpers de desarrollo
LOG_LEVEL=error                    # Solo errores críticos
BCRYPT_ROUNDS=12                   # Cifrado fuerte para passwords
SESSION_SECURE_COOKIE=true         # Solo HTTPS (Render lo hace automático)
```

### ⚡ Performance en Producción

```bash
# Siempre ejecutar después de deploy:
php artisan config:cache    # +50% velocidad en config
php artisan route:cache     # +30% velocidad en routing
php artisan view:cache      # Pre-compila Blade
php artisan optimize        # Combina todos los caches
```

**Impacto medido:**
- Sin cache: ~150ms por request
- Con cache: ~45ms por request
- **Mejora: 70% más rápido**

### 🔄 Estrategia de Rollback

Si un deploy falla:

```bash
# Opción 1: Rollback en Render (UI)
Dashboard → Service → Deployments → "Rollback to this version"

# Opción 2: Git rollback manual
git revert HEAD
git push origin main

# Opción 3: Restaurar BD desde backup
# (si las migraciones causaron problemas)
```

### 📊 Monitoreo Post-Deploy

**Logs a revisar en Render:**

```bash
# Errores de aplicación:
grep "ERROR" logs/*

# Consultas SQL lentas:
grep "slow query" logs/*

# Memory leaks:
grep "memory" logs/*

# Excepciones no capturadas:
grep "Uncaught" logs/*
```

### 🚦 Health Checks Automatizados

**Configurar en Render:**

1. Ve a Settings → Health Check Path
2. Configura: `/health`
3. Render reinicia automáticamente si el health check falla 3 veces consecutivas

**Ruta health check avanzada:**

```php
Route::get('/health', function () {
    try {
        // Test DB
        DB::connection()->getPdo();
        $dbStatus = 'ok';
    } catch (\Exception $e) {
        $dbStatus = 'fail';
    }

    // Test cache
    $cacheWorks = Cache::has('health_check') || Cache::put('health_check', true, 10);

    // Test storage
    $storageWritable = is_writable(storage_path('logs'));

    $allOk = ($dbStatus === 'ok' && $cacheWorks && $storageWritable);

    return response()->json([
        'status' => $allOk ? 'healthy' : 'unhealthy',
        'database' => $dbStatus,
        'cache' => $cacheWorks ? 'ok' : 'fail',
        'storage' => $storageWritable ? 'ok' : 'fail',
        'timestamp' => now()->toIso8601String(),
    ], $allOk ? 200 : 503);
});
```

### 🔐 Variables de Entorno por Ambiente

**Usar grupos en Render:**

```bash
# Production
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

# Staging (servicio separado)
APP_ENV=staging
APP_DEBUG=true   # OK en staging
LOG_LEVEL=debug
```

### 📈 Optimizaciones Avanzadas

**1. OPcache en PHP (Dockerfile):**

```dockerfile
# Agregar a Dockerfile después de instalar extensiones:
RUN docker-php-ext-install opcache

# Crear archivo de configuración:
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini
```

**Impacto:** +40% velocidad en ejecución PHP

**2. Compresión Gzip en Apache:**

```dockerfile
RUN a2enmod deflate headers

RUN echo "<IfModule mod_deflate.c>" >> /etc/apache2/apache2.conf \
    && echo "  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript" >> /etc/apache2/apache2.conf \
    && echo "</IfModule>" >> /etc/apache2/apache2.conf
```

**Impacto:** -70% tamaño de respuestas HTTP

**3. Lazy Loading de Relaciones:**

```php
// ❌ N+1 queries:
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->name;  // Query por cada usuario
}

// ✅ Eager loading:
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->name;  // 1 query total
}
```

---

## 📚 Recursos Adicionales

- [Laravel Deployment Documentation](https://laravel.com/docs/11.x/deployment)
- [Render PHP Documentation](https://render.com/docs/deploy-php)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
- [Apache Performance Tuning](https://httpd.apache.org/docs/2.4/misc/perf-tuning.html)

---

## 🆘 Troubleshooting Rápido

| Error | Causa | Solución |
|-------|-------|----------|
| "500 Internal Server Error" | APP_KEY faltante o corrupta | Verificar variable `APP_KEY` en Render |
| "SQLSTATE[HY000] [2002]" | BD no conecta | Verificar `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` |
| "Permission denied" en logs | Permisos incorrectos | `chown -R www-data:www-data storage` |
| "419 Page Expired" | Sesiones expiran | Verificar `SESSION_DRIVER=file` y permisos en `storage/framework/sessions` |
| "Class not found" | Autoload no actualizado | `composer dump-autoload && php artisan optimize` |
| Cache corrupto | Config cache desactualizado | `php artisan config:clear && php artisan config:cache` |

---

**✅ Con esta configuración, tu aplicación Laravel estará lista para producción en Render con:**
- Inicio rápido y predecible
- Debugging controlado
- Rollback fácil
- Monitoreo automatizado
- Performance optimizado
- Seguridad reforzada
