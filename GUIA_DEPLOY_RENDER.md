# 🚀 Guía de Despliegue a Render

## 📋 ¿Qué es Render?

**Render** es una plataforma PaaS moderna que:
- ✅ Soporta Docker nativo
- ✅ Base de datos PostgreSQL incluida
- ✅ Deploy automático desde GitHub
- ✅ SSL/TLS gratis
- ✅ Variables de entorno gestionadas
- ✅ Mejor que Heroku (deprecado)
- ✅ Más flexible que Vercel para PHP/Laravel

---

## 🎯 Requisitos Previos

1. **Cuenta en Render**: [render.com](https://render.com) (gratis)
2. **Repository GitHub**: Tu código en GitHub
3. **Git instalado** en tu máquina
4. **Variables de entorno** listas

---

## 📦 Archivos Necesarios (Ya Creados)

```
proyecto/
├── Dockerfile                    ✅ Multi-stage build
├── docker/
│   └── nginx.conf               ✅ Configuración web
├── render.yaml                   ✅ Configuración de Render
├── .dockerignore                ✅ Archivos a ignorar
└── .env.example                 ✅ Variables de entorno
```

---

## 🔧 Paso a Paso: Deploy en Render

### PASO 1: Preparar el Repositorio

```bash
# 1. Inicializar Git (si aún no está)
cd c:\Proyecto\sistema
git init
git add .
git commit -m "Initial commit: Dashboard modular tipo Odoo"

# 2. Crear repositorio en GitHub
# https://github.com/new
# Nombre: sistema-clinica
# Descripción: Sistema de gestión veterinaria

# 3. Agregar remoto y push
git remote add origin https://github.com/TU_USUARIO/sistema-clinica.git
git branch -M main
git push -u origin main
```

### PASO 2: Crear Cuenta en Render

1. Ir a [render.com](https://render.com)
2. Registrarse con GitHub (más fácil)
3. Autorizar Render para acceder a tu repositorio

### PASO 3: Conectar Repository

1. Dashboard Render → **New** → **Web Service**
2. Seleccionar tu repositorio `sistema-clinica`
3. Render detectará automáticamente el `Dockerfile`

### PASO 4: Configurar Variables de Entorno

En la pantalla de creación del servicio web, agregar las variables:

```
APP_KEY=                    # Se genera automáticamente
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-app.onrender.com

DB_CONNECTION=pgsql
DB_HOST=<generado automáticamente>
DB_PORT=5432
DB_DATABASE=sistema_db
DB_USERNAME=postgres
DB_PASSWORD=<generado automáticamente>

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

**⚠️ IMPORTANTE:**
- Render crea la BD automáticamente
- Las credenciales las genera Render
- Cópialas cuando Render las muestre

### PASO 5: Crear Base de Datos PostgreSQL

1. Render → **Databases**
2. **New PostgreSQL**
3. Nombre: `sistema-db`
4. Region: Oregon (o tu región preferida)
5. Plan: Free (para pruebas) o Starter (producción)
6. Crear

Render vinculará automáticamente la BD con el servicio web.

### PASO 6: Configurar Build y Deploy

En la sección **Build Command**:

```bash
composer install --no-interaction --optimize-autoloader
npm install
npm run build
php artisan migrate:fresh --seed --force
```

En **Start Command**:

```bash
/entrypoint.sh
```

### PASO 7: Deploy

Render hace deploy automático cuando haces push:

```bash
# 1. Hacer cambios localmente
# 2. Commit y push
git add .
git commit -m "Cambios"
git push origin main

# 3. Render detecta el push automáticamente
# 4. Inicia el build (5-10 minutos)
# 5. Deploy en vivo
```

---

## ✅ Verificar Deploy

1. Ir a tu servicio en Render
2. Copiar la URL: `https://tu-app.onrender.com`
3. Visitar en el navegador
4. Ver logs en tiempo real:
   ```
   Render → Logs → Live tail
   ```

---

## 🔑 Generar APP_KEY

Si Render no genera automáticamente la `APP_KEY`:

```bash
# Localmente
php artisan key:generate
# Copiar el valor de .env APP_KEY=...

# En Render Dashboard
# Settings → Environment → APP_KEY → Pegar valor
```

---

## 🗄️ Base de Datos PostgreSQL

### Diferencias vs MySQL

| Característica | PostgreSQL | MySQL |
|----------------|-----------|-------|
| Soporte Render | ✅ Nativo | ⚠️ Externo |
| Confiabilidad | ✅ Superior | ⚠️ Buena |
| Performance | ✅ Mejor | ⚠️ Buena |
| Setup | ✅ Automático | ❌ Manual |
| Costo | ✅ Gratis | ⚠️ Gratis |

### Migrar de MySQL a PostgreSQL

Si tenías MySQL localmente, Render usará PostgreSQL automáticamente:

```bash
# Las migraciones de Laravel funcionan igual
# Render ejecuta:
php artisan migrate:fresh --seed --force
```

**Nota:** Las migraciones son agnósticas de BD.

---

## 📊 Monitorear Aplicación

### Logs

```
Render Dashboard → Logs → Live tail
```

Ver errores en tiempo real:
- Errores de PHP
- Migraciones fallidas
- Seeders
- Nginx

### Métricas

```
Render Dashboard → Metrics
```

Monitorear:
- CPU usage
- Memory usage
- Requests/sec
- Response times

---

## 🚨 Problemas Comunes

### ❌ Error: "Connection refused"

**Causa:** BD no está lista  
**Solución:**
```bash
# En Render, esperar 30 segundos después de crear la BD
# Logs pueden mostrar "Connection refused" temporalmente
```

### ❌ Error: "OutOfMemory"

**Causa:** Insuficiente RAM  
**Solución:**
- Cambiar plan de servicio web
- Cambiar plan de base de datos
- Optimizar queries

### ❌ Error: "No space left on device"

**Causa:** Disco lleno  
**Solución:**
- Limpiar logs: `php artisan log:clear`
- Aumentar espacio en disco
- Cambiar plan

### ❌ Assets no cargan (CSS, JS)

**Causa:** Rutas mal configuradas  
**Solución:**
```php
// config/app.php
'url' => env('APP_URL', 'http://localhost'),
'asset_url' => env('APP_URL'),

// Ejecutar localmente:
php artisan storage:link
```

### ❌ Migraciones no ejecutan

**Causa:** BD no está lista  
**Solución:**
```bash
# Ejecutar manualmente en Render shell:
# Render → Shell → 
php artisan migrate --force
php artisan db:seed --force
```

---

## 🔄 Despliegue Continuo (CI/CD)

Render hace CI/CD automático:

```
GitHub Push
    ↓
Webhook → Render
    ↓
Build (composer, npm, etc.)
    ↓
Tests (opcional)
    ↓
Deploy
    ↓
Live en producción
```

### Agregar Tests al Build

En `render.yaml`:

```yaml
buildCommand: |
  composer install
  npm install && npm run build
  php artisan test
```

---

## 🌍 Custom Domain

Para usar tu propio dominio:

1. Render Dashboard → Settings → Custom Domains
2. Agregar dominio: `clinica.com`
3. Copiar registros CNAME:
   ```
   Name: @
   Type: CNAME
   Value: tu-app.onrender.com
   ```
4. Ir a tu proveedor DNS (GoDaddy, Namecheap, etc.)
5. Agregar registro CNAME
6. Esperar 24h a que propague

---

## 📈 Escalabilidad

### Plan Gratuito (Free)

```
- 750 horas/mes de servicio
- 1 vCPU compartida
- 512 MB RAM
- BD PostgreSQL 256 MB
- Apagado automático después de 15 min inactividad
```

**Ideal para:** Desarrollo, pruebas

### Plan Starter

```
- CPU dedicada
- 1 GB RAM
- BD hasta 2 GB
- Sin apagado automático
- $7/mes servicio + $15/mes BD
```

**Ideal para:** Producción pequeña

### Plan Pro+

```
- Mejor soporte
- Escalabilidad automática
- Más GB de almacenamiento
- Backup automático
```

---

## 🔐 Seguridad en Producción

### HTTPS/SSL

✅ Automático en Render (Let's Encrypt)

### Variables Sensibles

```bash
# ✅ BIEN: En Environment Variables de Render
APP_KEY=xxx
DB_PASSWORD=xxx
MAIL_PASSWORD=xxx

# ❌ MAL: En .env o committeadas
# .env.example no tiene secretos reales
```

### Backups

PostgreSQL en Render:
- Backups automáticos diarios
- Retención de 7 días (plan gratuito)
- Exportación manual disponible

```
Render → Database → Backups
```

---

## 📱 Monitorar en Móvil

Descarga la app Render:
- [iOS](https://apps.apple.com/us/app/render/id1565551048)
- [Android](https://play.google.com/store/apps/details?id=com.render.mobile)

Permite:
- Ver logs
- Reiniciar servicios
- Monitorear métricas
- Recibir notificaciones

---

## ✨ Resumen de Archivos

### Dockerfile (Multi-stage)

```dockerfile
# Stage 1: Build (PHP + Composer + Node)
# - Instala dependencias PHP
# - Instala node_modules y compila assets
# - Genera APP_KEY

# Stage 2: Runtime (PHP + Nginx)
# - Copia desde Stage 1
# - Inicia PHP-FPM y Nginx
# - Ejecuta migraciones en startup
```

### docker/nginx.conf

```nginx
# Configuración de Nginx
- Maneja peticiones HTTP
- Proxea a PHP-FPM
- Cache estático (JS, CSS, imágenes)
- Health check en /up
```

### render.yaml

```yaml
# Servicio Web
- Runtime: Docker
- BD: PostgreSQL (integrada)
- Variables de entorno automáticas
- Build y deploy automático desde GitHub
```

### .env.example

```env
# PostgreSQL en Render (no MySQL)
# CACHE_DRIVER=file (no Redis)
# SESSION_DRIVER=file (no database)
```

---

## 🎯 Checklist Final

```
✅ Código en GitHub
✅ Dockerfile creado
✅ render.yaml creado
✅ .env.example actualizado
✅ Cuenta Render creada
✅ Servicio web conectado
✅ Base de datos PostgreSQL creada
✅ Variables de entorno configuradas
✅ Deploy automático funcionando
✅ HTTPS/SSL habilitado
✅ Logs monitoreados
✅ Dominio custom (opcional)
✅ Backups configurados
```

---

## 🔗 Links Útiles

- [Render Documentation](https://render.com/docs)
- [Laravel on Render](https://render.com/docs/deploy-laravel)
- [PostgreSQL on Render](https://render.com/docs/postgresql)
- [Environment Variables](https://render.com/docs/environment-variables)
- [Troubleshooting](https://render.com/docs/troubleshooting)

---

## 📞 Soporte

Si tienes problemas:

1. **Logs de Render**: Ver errores exactos
2. **GitHub Issues**: Documentar el problema
3. **Render Support**: support@render.com
4. **Laravel Docs**: laravel.com/docs

---

**¡Tu aplicación está lista para Render! 🚀**

Próximo paso: `git push origin main` y monitorear el deploy en Render Dashboard.
