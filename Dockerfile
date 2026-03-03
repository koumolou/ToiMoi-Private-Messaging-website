FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy project folder into Apache root
COPY project/ /var/www/html/

# Enable rewrite (good practice)
RUN a2enmod rewrite

EXPOSE 80