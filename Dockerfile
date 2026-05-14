# Build the frontend assets in a Node build stage
FROM node:20 AS build-assets
WORKDIR /var/www/html
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Build the PHP application image
FROM php:8.2-fpm-alpine
ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /var/www/html

RUN apk add --no-cache --virtual .build-deps \
    build-base \
    autoconf \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    zlib-dev \
    libxml2-dev \
    curl \
    unzip \
    git \
 && apk add --no-cache \
    libzip \
    oniguruma \
    libpng \
    jpeg \
    freetype \
    zlib \
    libxml2 \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j1 pdo_mysql mbstring zip exif pcntl bcmath gd \
 && apk del .build-deps

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

COPY . ./
COPY --from=build-assets /var/www/html/public/build ./public/build

RUN if [ ! -f .env ]; then cp .env.example .env; fi
RUN php artisan key:generate --force

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
