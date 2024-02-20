FROM php:7.4-fpm as prod

RUN apt-get update && apt-get install -y \
    curl \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-client \
    unzip \
    zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-configure zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER="1" \
    COMPOSER_NO_INTERACTION="1"

WORKDIR /app

COPY bee-health-data-portal/ /app/

RUN /usr/local/bin/composer --no-dev --no-ansi install --prefer-dist --no-progress

RUN chown www-data:www-data /app/bootstrap/cache && \
    chown --recursive www-data:www-data /app/storage

RUN php /app/artisan view:cache --verbose

RUN sed -i 's/^access.log/;access.log/' /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php-fpm/prod.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoints/app/prod.sh /usr/local/bin/docker-entrypoint.sh
COPY docker/php/prod.ini /usr/local/etc/php/php.ini

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

FROM prod as dev

COPY docker/php-fpm/dev.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoints/app/dev.sh /usr/local/bin/docker-entrypoint.sh
COPY docker/php/dev.ini /usr/local/etc/php/php.ini
