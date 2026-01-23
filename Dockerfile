FROM php:8.2-apache

# Change Apache to listen on 8081
RUN sed -i 's/80/8081/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:8081/g' /etc/apache2/sites-available/000-default.conf

# Enable Apache rewrite
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy app
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8081
