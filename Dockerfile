## Image PHP avec Apache
#FROM php:8.2-apache
#
## Installer les extensions nécessaires
#RUN apt-get update && apt-get install -y \
#    git unzip libicu-dev libonig-dev libxml2-dev libzip-dev zip \
#    && docker-php-ext-install intl pdo pdo_mysql mbstring zip opcache
#
## Installer Composer
#COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
#
## Config Apache
#RUN a2enmod rewrite
#COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf
#
## Copier le projet
#WORKDIR /var/www/html
#COPY . .
#
## Créer le dossier var si nécessaire et donner les droits
#RUN mkdir -p var var/cache var/log && \
#    chown -R www-data:www-data var
#
## Installer dépendances Symfony
#RUN composer install --no-dev --optimize-autoloader
##RUN APP_ENV=prod php bin/console cache:clear
#
#
#EXPOSE 80
#CMD ["apache2-foreground"]
# Image PHP avec Apache
# --- Stage 0 : Build ---
# --- Stage 0 : Build ---
FROM php:8.2-apache AS build

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libxml2-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_mysql mbstring zip opcache \
    && a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le projet
WORKDIR /var/www/html
COPY . .

# Créer un utilisateur non-root pour Composer
RUN useradd -ms /bin/bash symfonyuser
USER symfonyuser

# Installer les dépendances sans exécuter les scripts auto (on les fera après)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Générer cache et autoload pour prod
RUN APP_ENV=prod php bin/console cache:clear --no-warmup
RUN APP_ENV=prod php bin/console cache:warmup

# --- Stage 1 : Production ---
FROM php:8.2-apache

# Installer extensions nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev libonig-dev libxml2-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_mysql mbstring zip opcache \
    && a2enmod rewrite

WORKDIR /var/www/html

# Copier le projet et vendor depuis le build
COPY --from=build /var/www/html /var/www/html

# Copier le vhost Apache si nécessaire
COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf

# Assurer les bons droits pour Apache
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

EXPOSE 80
CMD ["apache2-foreground"]
