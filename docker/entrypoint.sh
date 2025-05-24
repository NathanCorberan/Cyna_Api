#!/bin/sh

echo "⏳ Attente de la base de données..."

until php -r "try {
    new PDO(
        'mysql:host=${MYSQL_HOST:-database};port=${MYSQL_PORT:-3306};dbname=${MYSQL_DATABASE:-cyna_api}',
        '${MYSQL_USER:-admin}',
        '${MYSQL_PASSWORD:-caribou}'
    );
    exit(0);
} catch (Exception \$e) { exit(1); }" 2>/dev/null; do
  sleep 2
  echo "DB pas encore dispo, on attend..."
done

echo "✅ Base de données dispo, migration en cours..."
php bin/console doctrine:migration:migrate --no-interaction

echo "🚀 Migration terminée, lancement de PHP-FPM"
exec php-fpm
