# Build the frontend assets in a Node build stage
FROM node:20-alpine AS build-assets
WORKDIR /var/www/html
COPY package*.json ./
RUN npm ci --only=production --no-audit --no-fund
COPY . .
RUN npm run build

# Build the PHP application image
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Install minimal required packages
RUN apk add --no-cache libzip libpng jpeg freetype zlib libxml2 curl unzip git mysql-client

# Install PHP extensions one by one to reduce memory usage
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install zip
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install bcmath
RUN apk add --no-cache --virtual .gd-deps libpng-dev jpeg-dev freetype-dev && docker-php-ext-configure gd --with-jpeg --with-freetype && docker-php-ext-install gd && apk del .gd-deps

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy composer files and install dependencies
COPY composer.* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress --no-scripts

# Copy application code
COPY . ./
COPY --from=build-assets /var/www/html/public/build ./public/build

# Setup environment
RUN if [ ! -f .env ]; then cp .env.example .env; fi
RUN php artisan key:generate --force

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]