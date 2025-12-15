# Build stage
FROM php:8.3-fpm-alpine AS builder

# Instalar dependencias esenciales
RUN apk add --no-cache \
    curl \
    git \
    zip \
    unzip \
    libpq-dev \
    oniguruma-dev

# Instalar extensiones PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    bcmath \
    mbstring

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar Node.js y npm
RUN apk add --no-cache nodejs npm

# Copiar archivos del proyecto
WORKDIR /app
COPY . .

# Instalar dependencias PHP
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Compilar assets
RUN npm install && npm run build

# Runtime stage
FROM php:8.3-fpm-alpine

# Instalar dependencias runtime
RUN apk add --no-cache \
    curl \
    libpq \
    oniguruma \
    nginx \
    supervisor \
    busybox-initscripts

# Instalar extensiones PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    bcmath \
    mbstring

# Copiar archivos compilados desde builder
COPY --from=builder /app /app
COPY --from=builder /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Crear directorios necesarios
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} \
    && chown -R www-data:www-data storage bootstrap/cache

# Generar APP_KEY
RUN php artisan key:generate --force 2>/dev/null || true

# Configurar Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Crear directorios de logs y run
RUN mkdir -p /var/log/nginx /run/nginx

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80

# Script de entrada
CMD ["sh", "-c", "php artisan migrate --force 2>/dev/null || true && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
