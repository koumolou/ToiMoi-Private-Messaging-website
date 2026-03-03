FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Fix Apache MPM conflict - disable all then enable only prefork
RUN apt-get update && apt-get install -y apache2 \
    && a2dismod mpm_event mpm_worker mpm_prefork \
    && a2enmod mpm_prefork \
    && a2enmod rewrite

# Copy project files
COPY project/ /var/www/html/

EXPOSE 80

CMD ["apache2-foreground"]