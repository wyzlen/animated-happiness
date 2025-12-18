# Pin to a specific PHP version (8.3 is current stable as of late 2025)
FROM php:8.3-apache

# Enable rewrite module (common need)
RUN a2enmod rewrite

# Install common extensions (add more if your index.php needs them, e.g., pdo_mysql)
RUN docker-php-ext-install pdo_mysql mysqli gd

# Copy your files (adjust if your files are in a subfolder)
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && a2ensite 000-default.conf \
    && a2dissite default-ssl.conf  # Disable SSL if not needed

# Expose port 80 (required for docs)
EXPOSE 80
