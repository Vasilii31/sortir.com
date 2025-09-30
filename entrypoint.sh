#!/bin/bash
set -e

echo $DB_HOST
echo $DB_USER
echo $DB_PASSWORD

# Attendre que MySQL soit prêt
until mysqladmin ping -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" > /dev/null 2>&1; do
  echo "Waiting for MySQL..."
  sleep 2
done

# Exécuter migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Charger fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Lancer la commande passée au container (ici CMD ["apache2-foreground"])
exec "$@"

