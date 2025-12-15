FROM php:8.2-apache

# Instalación de dependencias del sistema (usando apt-get en lugar de apk)
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP necesarias para Laravel + MySQL
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    bcmath \
    gd

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Instalar Composer (manejo de dependencias PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el proyecto
COPY . /var/www/html

# Establecer permisos adecuados
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Directorio de trabajo para ejecutar Composer
WORKDIR /var/www/html

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Exponer el puerto utilizado por Render
EXPOSE 10000

# Modificar configuración para que Apache escuche en el puerto 10000
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Comando final para iniciar Apache
CMD ["apache2-foreground"]
