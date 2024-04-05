# Stage 1: Build Stage
FROM php:8.2-rc-apache AS build
ARG DOCKER_USERID="1000"

# Install required dependencies
RUN apt-get update \
    && apt-get install -y zlib1g-dev libzip-dev libmcrypt-dev libmemcached-dev memcached \
    && apt-get install -y default-mysql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install pdo mysqli pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set COMPOSER_ALLOW_SUPERUSER to enable plugins in non-interactive mode
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install dependencies using Composer
RUN composer install --ignore-platform-reqs

# Set permissions
RUN chown -R www-data:www-data /var/www/html/var

# die zu Synfony passende Configuration
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 8000