FROM php:8.2-apache

# =========================
# Paquetes del sistema
# =========================
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

# =========================
# Extensiones PHP necesarias
# =========================
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    bcmath \
    gd \
    zip

# =========================
# Apache
# =========================
RUN a2enmod rewrite

# DocumentRoot a /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf \
 && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# =========================
# Composer
# =========================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# =========================
# Copiar proyecto
# =========================
COPY . /var/www/html
WORKDIR /var/www/html

# =========================
# Directorios requeridos por Laravel
# =========================
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/build \
 && chown -R www-data:www-data storage bootstrap/cache

# =========================
# Dependencias PHP
# =========================
RUN composer install --no-dev --optimize-autoloader --no-interaction

# =========================
# Generar APP_KEY temporal para compilar assets
# =========================
RUN php artisan key:generate --force 2>/dev/null || true

# =========================
# Assets Frontend (Vite)
# =========================
RUN npm install --legacy-peer-deps && npm run build

# =========================
# Permisos finales
# =========================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# =========================
# Puerto para Render
# =========================
EXPOSE 10000
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf