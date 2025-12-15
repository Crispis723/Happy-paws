# Multi-stage Dockerfile para Laravel 11 en Render
FROM php:8.3-fpm-alpine AS base

# Actualizar índice de paquetes
RUN apk update && apk add --no-cache \
    curl \
    git \
    libpq-dev \
    zip \
    unzip \
    mariadb-client \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    ctype \
    fileinfo \
    json \
    mbstring \
    openssl \
    tokenizer \
    xml

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar Node.js para assets
RUN apk add --no-cache nodejs npm

WORKDIR /app

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Instalar dependencias Node.js
RUN npm install && npm run build

# Crear directorio de caché y logs
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Generar APP_KEY si no existe
RUN php artisan key:generate --force 2>/dev/null || true

# Crear nginx stage
FROM php:8.3-fpm-alpine

RUN apk update && apk add --no-cache \
    curl \
    libpq \
    mariadb-client \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    ctype \
    fileinfo \
    json \
    mbstring \
    openssl \
    tokenizer \
    xml

# Instalar Nginx
RUN apk add --no-cache nginx

WORKDIR /app

# Copiar desde stage anterior
COPY --from=base /app /app
COPY --from=base /usr/bin/composer /usr/bin/composer

# Copiar configuración de nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configurar PHP-FPM
RUN mkdir -p /run/php && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Crear script de entrada
RUN echo '#!/bin/sh\n\
set -e\n\
php artisan migrate --force\n\
php artisan db:seed --force 2>/dev/null || true\n\
php artisan cache:clear\n\
php artisan view:clear\n\
php artisan config:clear\n\
\n\
# Iniciar PHP-FPM y Nginx\n\
php-fpm -D\n\
nginx -g "daemon off;"\n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]
