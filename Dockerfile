# Build the frontend assets in a Node build stage
FROM node:20-alpine AS build-assets
WORKDIR /var/www/html
COPY package*.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# Build the PHP application image
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Install PHP runtime dependencies and build tools for extensions, then remove build deps
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev zlib-dev libxml2-dev mariadb-dev && \
    apk add --no-cache libzip libpng jpeg freetype zlib libxml2 curl unzip git && \
    docker-php-ext-configure gd --with-jpeg --with-freetype && \
    docker-php-ext-install -j1 pdo_mysql mbstring zip exif pcntl bcmath gd && \
    apk del .build-deps

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files and install dependencies
COPY composer.* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress

# Copy application code
COPY . ./
COPY --from=build-assets /var/www/html/public/build ./public/build

# Setup environment
RUN if [ ! -f .env ]; then cp .env.example .env; fi
RUN php artisan key:generate --force

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]