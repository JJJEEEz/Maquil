# syntax=docker/dockerfile:1

# ---- Composer (PHP deps) ----
FROM composer:2 AS composer
WORKDIR /app

# Install PHP dependencies (no dev)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

# Copy full app and optimize autoloader
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ---- Node (Vite build) ----
FROM node:18-alpine AS assets
WORKDIR /app

# Install JS deps
COPY package.json package-lock.json* ./
RUN npm ci || npm i

# Copy vendor from composer stage (for Ziggy and other PHP packages referenced in JS)
COPY --from=composer /app/vendor vendor

# Copy only what Vite needs and build
COPY resources resources
COPY vite.config.js .
COPY tailwind.config.js .
COPY postcss.config.js .
COPY jsconfig.json .
COPY public public
RUN npm run build

# ---- Runtime (NGINX + PHP-FPM) ----
FROM richarvey/nginx-php-fpm:latest
WORKDIR /var/www/html

# Copy application source
COPY --chown=www-data:www-data . .

# Copy vendor from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy built assets
COPY --from=assets /app/public/build ./public/build

# NGINX config for Laravel - overwrite default sites
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Permissions for storage and cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy and setup custom entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Set environment to production
ENV APP_ENV=production
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=0
ENV PHP_MEMORY_LIMIT=512M

EXPOSE 80
# Use custom entrypoint that caches at runtime
CMD ["/entrypoint.sh"]
