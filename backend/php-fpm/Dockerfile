# Use the official PHP image with FPM (FastCGI Process Manager)
FROM php:8.1-fpm

# Install necessary PHP extensions (example: mysqli, pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy custom php.ini (if you want to tweak settings)
COPY php.ini /usr/local/etc/php/

# Set working directory
WORKDIR /var/www/html
