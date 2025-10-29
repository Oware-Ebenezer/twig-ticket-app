# Use official PHP-Apache image
FROM php:8.2-apache

# Set working directory to Apache's web root
WORKDIR /var/www/html

# Copy all files into the container
COPY . .

# Install system dependencies for Composer
RUN apt-get update && apt-get install -y unzip git curl

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies (Slim, Twig, etc.)
RUN composer install --no-interaction --optimize-autoloader
RUN composer dump-autoload -o

# Enable Apache rewrite module (for clean URLs)
RUN a2enmod rewrite

# Update Apache configuration to use the `src` directory as the web root
RUN sed -i 's|/var/www/html|/var/www/html/src|g' /etc/apache2/sites-available/000-default.conf

# Expose HTTP port
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
