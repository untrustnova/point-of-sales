# Stage 1: Build frontend assets
FROM oven/bun:latest AS frontend-build
WORKDIR /app

# Install dependencies first for better caching
COPY package.json bun.lock ./
RUN bun install --frozen-lockfile

# Copy application source code and build
COPY . .
RUN bun run build

# Stage 2: PHP Application
FROM php:8.5-fpm-alpine AS php-base

# Add OCI metadata
LABEL org.opencontainers.image.title="POS Nova" \
      org.opencontainers.image.description="Sistem Kasir & Manajemen Toko Modern — Laravel 13 + React + Inertia.js" \
      org.opencontainers.image.source="https://github.com/untrustnova/point-of-sales" \
      org.opencontainers.image.vendor="untrustnova" \
      org.opencontainers.image.version="0.1.0-beta"

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    linux-headers \
    curl \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql pdo_pgsql pcntl bcmath gd intl zip opcache \
    && apk del $PHPIZE_DEPS

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies (caching step)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application source code
COPY . .

# Copy built frontend assets from stage 1
COPY --from=frontend-build /app/public/build ./public/build

# Finish composer setup and set permissions
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Healthcheck to verify the application is up and running
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:8000/up || exit 1

EXPOSE 8000

# Default command to start the Laravel application
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
