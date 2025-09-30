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
FROM php:8.2-apache

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libxml2-dev libzip-dev zip \
    && docker-php-ext-install intl pdo pdo_mysql mbstring zip opcache \
    && a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le projet
WORKDIR /var/www/html
COPY . .

# Créer les dossiers nécessaires pour Symfony
RUN mkdir -p var/cache var/log var/sessions \
    && chown -R www-data:www-data var

# Installer les dépendances Symfony
RUN composer install --no-dev --optimize-autoloader

# Générer le cache et autoload pour l'environnement prod
RUN APP_ENV=prod php bin/console cache:clear --no-warmup
RUN APP_ENV=prod php bin/console cache:warmup

# Définir le propriétaire du projet
RUN chown -R www-data:www-data .

# Copier le fichier vhost pour Apache si nécessaire
COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf

# Exposer le port
EXPOSE 80

# Commande pour lancer Apache
CMD ["apache2-foreground"]
