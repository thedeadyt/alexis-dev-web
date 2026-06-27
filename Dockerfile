FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx supervisor curl \
    freetype-dev libjpeg-turbo-dev libpng-dev \
    libzip-dev zip unzip \
    oniguruma-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_mysql mbstring zip gd opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN mkdir -p /run/nginx /var/log/supervisor \
  && chown -R www-data:www-data /var/www

EXPOSE 80
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
