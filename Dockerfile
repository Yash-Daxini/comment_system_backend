# Use the official PHP image as the base image
FROM php:8-alpine

# Set the working directory in the container
WORKDIR /var/www

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql

# Copy the project files into the container
COPY . /var/www

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.1.0

# Increase Composer memory limit
RUN php -d memory_limit=-1 /usr/local/bin/composer --version

# Install project dependencies
RUN composer install --no-dev --verbose

# Expose port 8080 (not required for Render, but included for reference)
EXPOSE 8080

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080"]
