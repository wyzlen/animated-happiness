# Use the official PHP image with Apache
FROM php:8.2-apache
# Copy application files to the container
COPY . /var/www/html
# Enable recommended production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
