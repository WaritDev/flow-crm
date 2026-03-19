# Stage 1: Build frontend assets (Vite)
FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# Stage 2: PHP production image
FROM php:8.4-apache AS production

RUN a2enmod rewrite headers

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
        libpng-dev \
        libzip-dev \
        libpq-dev \
        libonig-dev \
        git \
        unzip \
        curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        mbstring \
        pcntl \
        bcmath \
        zip \
        opcache

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.save_comments=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

COPY . .

COPY --from=frontend-builder /app/public/build ./public/build

RUN composer dump-autoload --optimize

# Set Apache DocumentRoot to /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i '/<\/VirtualHost>/i \\t<Directory /var/www/html/public>\n\t\tOptions -Indexes +FollowSymLinks\n\t\tAllowOverride All\n\t\tRequire all granted\n\t</Directory>' \
        /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
