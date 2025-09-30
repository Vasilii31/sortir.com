# Image PHP avec Apache
FROM php:8.2-apache

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libxml2-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_mysql mbstring zip opcache

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Config Apache
RUN a2enmod rewrite
COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf

# Copier le projet
WORKDIR /var/www/html
COPY . .

# Donner droits corrects
RUN chown -R www-data:www-data var

# Installer dépendances Symfony
RUN composer install --no-dev --optimize-autoloader --no-scripts
#RUN APP_ENV=prod php bin/console cache:clear


EXPOSE 80
CMD ["apache2-foreground"]
