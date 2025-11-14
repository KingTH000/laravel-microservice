# Start from the official PHP 8.3 image
FROM php:latest

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (we need pdo_mysql for our new DB)
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the existing application code into the container
COPY . .

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose the port our app will run on
EXPOSE 8000

# The command to run when the container starts
# Use 0.0.0.0 to make it accessible outside the container
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]