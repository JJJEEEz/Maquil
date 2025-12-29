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
FROM richarvey/nginx-php-fpm:3.1.6
WORKDIR /var/www/html

# Set environment variables BEFORE copying files
ENV SKIP_COMPOSER=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV ERRORS=1
ENV APP_ENV=production

# Copy application source
COPY --chown=nginx:nginx . .

# Copy vendor from composer stage
COPY --chown=nginx:nginx --from=composer /app/vendor ./vendor

# Copy built assets
COPY --chown=nginx:nginx --from=assets /app/public/build ./public/build

# NGINX config for Laravel
COPY docker/nginx.conf /etc/nginx/sites-available/default.conf
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default

# Permissions for Laravel
RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy and setup custom entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
