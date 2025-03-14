# nathancorberan-cyna_api

## Description
**nathancorberan-cyna_api** est une API RESTful construite avec **Symfony** et utilisant **API Platform** pour la gestion des endpoints. Elle inclut des fonctionnalités d'authentification JWT, de gestion des utilisateurs, de commandes, de produits et d'abonnements.

## Prérequis
Avant d'installer et d'exécuter ce projet, assurez-vous d'avoir les outils suivants installés :
- [PHP 8.1+](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/download/)
- [Docker et Docker Compose](https://www.docker.com/get-started)
- [Symfony CLI (optionnel)](https://symfony.com/download)

## Installation
### 1. Cloner le dépôt
```sh
git clone https://github.com/votre-repo/nathancorberan-cyna_api.git
cd nathancorberan-cyna_api
```

### 2. Installer les dépendances
```sh
composer install
```

### 3. Configuration de l'environnement
Copiez le fichier `.env.dev` en `.env.local` et en `.env` et configurez vos variables d'environnement :
```sh
cp .env.dev .env.local
cp .env.dev .env
```
Modifiez `.env.local` pour définir vos paramètres de base de données et JWT si nécessaire.

### 4. Génération des clés JWT
```sh
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

### 5. Lancer les conteneurs Docker
```sh
docker-compose up -d
```

### 6. Création de la base de données et exécution des migrations
```sh
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 7. Charger les fixtures (optionnel)
```sh
php bin/console doctrine:fixtures:load
```

## Démarrage du serveur Symfony
Si vous n'utilisez pas Docker, vous pouvez exécuter :
```sh
symfony server:start
```
Ou avec PHP directement :
```sh
php -S 127.0.0.1:8000 -t public/
```
L'API sera accessible à l'adresse : [http://127.0.0.1:8000](http://127.0.0.1:8000)

## Endpoints principaux
Cette API expose plusieurs endpoints via **API Platform**. Vous pouvez explorer l'API avec **Swagger** et **GraphQL** :
- Swagger UI : [http://127.0.0.1:8000/api](http://127.0.0.1:8000/api)
- GraphQL : [http://127.0.0.1:8000/graphql](http://127.0.0.1:8000/graphql)

Quelques routes disponibles :
- `POST /api/login_check` → Authentification via JWT
- `GET /api/users` → Liste des utilisateurs
- `GET /api/products` → Liste des produits
- `GET /api/orders` → Liste des commandes
- `GET /api/subscriptions` → Liste des abonnements

## Sécurité et Authentification
L'API utilise **LexikJWTAuthenticationBundle** pour gérer l'authentification JWT.
Pour obtenir un token JWT :
```sh
curl -X POST http://127.0.0.1:8000/api/login_check -d '{"username":"user@example.com", "password":"password"}' -H "Content-Type: application/json"
```

Utilisez ce token pour accéder aux endpoints protégés :
```sh
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" http://127.0.0.1:8000/api/users
```

## Structure du projet
Le projet est organisé comme suit :
- **`src/Entity/`** : Contient les entités Doctrine
- **`src/Repository/`** : Repositories pour interagir avec la base de données
- **`src/Controller/`** : Contrôleurs pour les endpoints API
- **`src/State/`** : Gestion avancée des états API Platform
- **`migrations/`** : Scripts de migration de base de données
- **`public/`** : Contient `index.php` (point d'entrée de l'API)
- **`config/`** : Fichiers de configuration Symfony et API Platform
- **`bin/console`** : Commandes Symfony

## Tests
Pour exécuter les tests unitaires et fonctionnels :
```sh
php bin/phpunit
```

## Déploiement
### 1. Générer les assets et vider le cache
```sh
php bin/console cache:clear --env=prod
php bin/console assets:install
```

### 2. Migration de base de données
```sh
php bin/console doctrine:migrations:migrate --no-interaction
```

### 3. Lancer le serveur en mode production
```sh
APP_ENV=prod php -S 0.0.0.0:8000 -t public/
```

## Auteurs
- **Nathan Corberan** - Développement principal
- **Joris Lecharpentier** - Contributions backend et sécurité
- **Noah Barreau** - Expérience utilisateur et intégration API
- **Liova Hovakimyan** - Développement front-end

## Conclusion
Ce projet a été un excellent exercice de mise en pratique de nos compétences en développement backend et en gestion de projet. Nous espérons que cette plateforme répondra aux attentes de l'entreprise Cyna et de ses clients.

