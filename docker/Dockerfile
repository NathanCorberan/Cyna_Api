#############################################
# Dockerfile ultra-léger pour une API Symfony 6+
#############################################

# ---------- 1) Build stage: Composer ----------
FROM composer:2.8 AS builder

WORKDIR /app

# Copie les fichiers Composer d'abord (pour le cache Docker)
COPY composer.json ./

# Copie tout le code du projet (Symfony en a besoin pour composer install)
COPY . .

# Installe les dépendances prod, optimise l'autoloader
RUN composer install

# Passe l'environnement en prod (utile si tu utilises symfony/dotenv)
RUN composer dump-env prod

# ---------- 2) Production stage: PHP-FPM (Alpine) ----------
FROM php:8.4-fpm-alpine AS production

# Installe toutes les dépendances nécessaires
RUN apk add --no-cache \
      icu-libs icu-dev \
      libzip libzip-dev \
      libpq libpq-dev \
      oniguruma-dev \
      libpng-dev libjpeg-turbo-dev freetype-dev \
      autoconf g++ make \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install intl zip pdo_mysql pdo_pgsql opcache gd bcmath \
  && pecl install apcu \
  && docker-php-ext-enable apcu opcache \
  && apk del autoconf g++ make \
  && rm -rf /var/cache/apk/*

# Travaille toujours dans /var/www
WORKDIR /var/www

# Copie tout le projet et les vendors déjà générés depuis le builder
COPY --from=builder /app /var/www

# Fixe les permissions pour tous les sous-dossiers de /assets/images
RUN mkdir -p /var/www/public/assets/images \
    && chown -R app:app /var/www/public/assets/images \
    && chmod -R 755 /var/www/public/assets/images

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Crée un utilisateur non-root pour la sécurité
RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /sbin/nologin -D app \
    && chown -R app:app /var/www

USER app

# Expose le port PHP-FPM standard (pas 8000 !)
EXPOSE 9000

# Installe les assets Symfony
RUN php bin/console assets:install public

# Commande d'entrée
CMD ["/entrypoint.sh"]
