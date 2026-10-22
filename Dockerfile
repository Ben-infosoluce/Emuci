# Stage 1: Build frontend assets
# Version épinglée volontairement : 24.22.1 corrige CVE-2026-111497 (décodeur
# Brotli de Node.js). Ne pas revenir à un tag flottant (node:24-alpine) : le
# correctif serait perdu silencieusement au prochain rebuild.
FROM node:24.22.1-alpine AS build-stage

# Garde-fou : le tag doit réellement correspondre à la version corrigée.
RUN [ "$(node -p 'process.versions.node')" = "24.22.1" ]

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Production PHP & Nginx
# Version épinglée volontairement : 8.3.36 corrige CVE-2026-109618 / CVE-2026-109619.
# Ne pas revenir à un tag flottant (php:8.3-fpm-alpine) : le patch serait perdu
# silencieusement au prochain rebuild.
FROM php:8.3.36-fpm-alpine

# Garde-fou : le tag doit réellement correspondre à la version corrigée.
RUN php -r 'exit(PHP_VERSION === "8.3.36" ? 0 : 1);'

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    unzip \
    git \
    bash \
    icu-dev \
    oniguruma-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mbstring zip exif pcntl bcmath intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Copy built assets from build-stage
COPY --from=build-stage /app/public/build ./public/build

# Copy configurations
COPY docker/php.ini /usr/local/etc/php/conf.d/app-php.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose port 80
EXPOSE 80

# Start Nginx and PHP-FPM
CMD ["sh", "-c", "nginx && php-fpm"]