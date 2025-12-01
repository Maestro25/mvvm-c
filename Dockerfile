# Use official PHP 8.3 image with Apache
FROM php:8.3-apache

# Install necessary system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Enable Apache mod_rewrite for friendly URLs
RUN a2enmod rewrite

# Change Apache configuration to use /public as web root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory to project root
WORKDIR /var/www/html

# Copy all project files into the container
COPY . /var/www/html/

# Install PHP dependencies (production mode)
RUN composer install --no-dev --optimize-autoloader

# Expose port 80 (default for Apache)
EXPOSE 80

# Start Apache server in the foreground
CMD ["apache2-foreground"]
