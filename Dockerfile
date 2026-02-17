FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_mysql pdo_sqlite zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Asegurar permisos correctos
RUN chown -R www-data:www-data /var/www

USER www-data

CMD ["php-fpm"]
