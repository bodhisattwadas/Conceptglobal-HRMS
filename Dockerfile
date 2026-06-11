FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build

FROM php:8.3-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        unzip \
        zip \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        sqlite-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pcntl \
        pdo \
        pdo_mysql \
        zip \
        gd \
        xml \
    && rm -rf /var/cache/apk/*

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=8080

CMD ["sh", "-c", "php artisan config:clear >/dev/null 2>&1 || true && php artisan serve --host=0.0.0.0 --port=${PORT}"]
