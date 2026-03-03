FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Disable conflicting MPM modules and enable prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# Enable rewrite
RUN a2enmod rewrite

# Copy project files
COPY project/ /var/www/html/

EXPOSE 80