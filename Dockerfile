# Stage 1: Build Stage
FROM php:8.2-rc-apache AS build
ARG DOCKER_USERID="1000"

# Install required dependencies
RUN apt-get update \
    && apt-get install -y libmcrypt4 libmcrypt-dev zlib1g-dev libzip-dev \
    && apt-get install -y libmemcached-dev memcached libmemcached11


# Install required PHP extensions
RUN docker-php-ext-install pdo zip

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
COPY docker/apache/default-config.conf /etc/apache2/sites-available/default-config.conf

EXPOSE 8000