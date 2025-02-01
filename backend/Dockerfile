
# Stage 1: Build dependencies
FROM composer:latest AS builder

# Set working directory for the build stage
WORKDIR /app

# Copy application files
COPY . .

# Install dependencies without development packages and optimize the autoloader
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Final production image
FROM php:8.3-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    zip \
    libzip-dev \
    unzip \
    default-mysql-client \
    redis-server \
    libxml2-dev \
    build-essential && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install pdo_mysql gd zip simplexml

# Install the PHP Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Set the working directory for the final image
WORKDIR /var/www/html

# Copy the application from the builder stage
COPY --from=builder /app /var/www/html

# Set permissions for Laravel directories
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Update the Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -i -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# Enable necessary Apache modules and suppress ServerName warning
RUN a2enmod rewrite && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose the default HTTP port
EXPOSE 80

# Run Laravel migrations and then start Apache
CMD php artisan migrate --force || echo "Migrations failed" && apache2-foreground
