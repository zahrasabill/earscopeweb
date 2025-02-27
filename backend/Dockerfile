FROM dunglas/frankenphp:latest

# Install PHP Extensions
RUN install-php-extensions \
    pcntl \
    zip \
    bcmath \
    pdo_mysql \
    mysqli

# Install Composer secara manual
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set Working directory
WORKDIR /app

# Copy all files
COPY . /app

# Set Environment Production
ENV APP_ENV=production
ENV APP_DEBUG=true

# Install dependencies
RUN composer install --ignore-platform-reqs --no-dev -a

# Install Octane dan set servernya ke FrankenPHP
RUN echo "yes" | php artisan octane:install --server=frankenphp

# Set permission untuk Laravel storage & cache
RUN chmod -R 777 storage bootstrap/cache

# Expose port yang digunakan Laravel Octane
EXPOSE 8010

# Define run Container
ENTRYPOINT ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8010"]
