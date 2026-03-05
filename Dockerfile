FROM php:8.2-fpm

# Install dependencies sistem & PHP extensions
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libicu-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY . /var/www
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN composer install --no-dev --optimize-autoloader

# Install Node & Build Assets (Vite)
RUN apt-get update && apt-get install -y nodejs npm && npm install && npm run build

EXPOSE 8080
CMD php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=8080
