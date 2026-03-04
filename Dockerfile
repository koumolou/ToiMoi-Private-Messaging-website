FROM php:8.2-cli

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project files first
COPY project/ /app/

# Copy composer files
COPY composer.json composer.lock /app/

# Install PHP dependencies inside /app
RUN composer install --no-dev --optimize-autoloader

EXPOSE ${PORT:-80}

CMD php -S 0.0.0.0:${PORT:-80} -t /app