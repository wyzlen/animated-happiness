# Use official PHP with Apache
FROM php:8.3-apache

# Install any needed extensions (add more as required)
# Common ones: pdo_mysql, gd, zip, intl, opcache, etc.
RUN docker-php-ext-install pdo_mysql mysqli

# Enable Apache mod_rewrite (useful for pretty URLs)
RUN a2enmod rewrite

# Set recommended PHP.ini settings for production (optional but good)
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Ensure proper permissions (optional, for production)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Apache runs in foreground by default
