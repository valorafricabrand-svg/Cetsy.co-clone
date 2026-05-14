# Build the frontend assets in a Node build stage
FROM node:20-alpine AS build-assets
WORKDIR /var/www/html
COPY package*.json ./
RUN npm ci --only=production
COPY . .
RUN npm run build

# Build the PHP application image
FROM php:8.2-fpm-alpine
ENV COMPOSER_MEMORY_LIMIT=-1
WORKDIR /var/www/html

RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    zlib-dev \
    libxml2-dev \
 && apk add --no-cache \
    libzip \
    oniguruma \
    libpng \
    jpeg \
    freetype \
    zlib \
    libxml2 \
    curl \
    unzip \
    git \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j1 pdo_mysql mbstring zip exif pcntl bcmath gd \
 && apk del .build-deps

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress

COPY . ./
COPY --from=build-assets /var/www/html/public/build ./public/build

RUN if [ ! -f .env ]; then cp .env.example .env; fi
RUN php artisan key:generate --force

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
