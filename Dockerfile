# Use the official PHP image as the base image
FROM php:8.2-apache

# Enable Apache modules and set document root
RUN a2enmod rewrite \
    && service apache2 restart \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && service apache2 restart

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql

# Copy the project files into the container
COPY . /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.1.0

# Increase Composer memory limit
RUN php -d memory_limit=-1 /usr/local/bin/composer --version

# Install project dependencies
RUN cd /var/www/html && /usr/local/bin/composer install --no-dev --verbose

# Expose port 8000 for web server
EXPOSE 8000

# Start Apache when the container starts
CMD ["php", "-S", "127.0.0.1:8000"]
