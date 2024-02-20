FROM php:7.4-fpm

ARG user
ARG uid

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    libzip-dev

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-configure zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

COPY "./.env" /
COPY "./docker-compose/app/docker-php-entrypoint" /usr/local/bin/
COPY "./docker-compose/app/laravel-worker.conf" /etc/supervisor/conf.d/
COPY "./docker-compose/app/php.ini" /usr/local/etc/php/

WORKDIR /app

# USER $user
