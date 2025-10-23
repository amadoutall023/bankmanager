# BankManager - API de Gestion Bancaire

Application Laravel pour la gestion des comptes bancaires avec une API REST complète.

## 🚀 Fonctionnalités Implémentées

### ✅ Base de Données
- **Migrations** : Tables `users`, `clients`, `admins`, `accounts`, `transactions`
- **Relations** : Clés étrangères et contraintes d'intégrité
- **Soft Deletes** : Suppression logique des comptes
- **UUID** : Identifiants uniques pour les comptes et transactions

### ✅ Factories & Seeders
- **Factories** : `UserFactory`, `ClientFactory`, `AdminFactory`, `AccountFactory`, `TransactionFactory`
- **Seeders** : Données de test réalistes (10 clients, 5 admins, 20 comptes, 50 transactions)
- **Base peuplée** : Commande `php artisan migrate:fresh --seed`

### ✅ API REST - Comptes Bancaires

#### Endpoint Principal
```
GET /api/v1/comptes
```

#### Paramètres de Requête
- `page` : Numéro de page (défaut: 1)
- `limit` : Nombre d'éléments par page (défaut: 10, max: 100)
- `type` : Filtrer par type (`epargne`, `cheque`)
- `statut` : Filtrer par statut (`active`, `inactive`, `closed`)
- `search` : Recherche par titulaire ou numéro de compte
- `sort` : Tri (`created_at`, `balance`, `account_number`)
- `order` : Ordre (`asc`, `desc`)

#### Headers Requis
```
Authorization: Bearer {token}
Accept: application/json
```

#### Exemples d'Utilisation

**Liste paginée basique :**
```bash
curl -X GET "http://localhost:8000/api/v1/comptes?page=1&limit=5" \
     -H "Accept: application/json"
```

**Filtrage par type et statut :**
```bash
curl -X GET "http://localhost:8000/api/v1/comptes?type=epargne&statut=active&page=1&limit=10" \
     -H "Accept: application/json"
```

**Recherche par nom :**
```bash
curl -X GET "http://localhost:8000/api/v1/comptes?search=Dupont&page=1&limit=10" \
     -H "Accept: application/json"
```

#### Format de Réponse
```json
{
  "success": true,
  "message": "Liste des comptes récupérée avec succès",
  "data": [
    {
      "id": "uuid",
      "numeroCompte": "C00123456",
      "titulaire": "Amadou Diallo",
      "type": "epargne",
      "solde": "1250000.00",
      "devise": "FCFA",
      "dateCreation": "2023-03-15T00:00:00Z",
      "statut": "active",
      "motifBlocage": null,
      "metadata": {
        "derniereModification": "2023-06-10T14:30:00Z",
        "version": 1
      }
    }
  ],
  "pagination": {
    "currentPage": 1,
    "totalPages": 3,
    "totalItems": 25,
    "itemsPerPage": 10,
    "hasNext": true,
    "hasPrevious": false
  },
  "links": {
    "self": "/api/v1/comptes?page=1",
    "next": "/api/v1/comptes?page=2",
    "first": "/api/v1/comptes?page=1",
    "last": "/api/v1/comptes?page=3"
  }
}
```

### ✅ Architecture Technique

#### Contrôleurs
- `AccountController` : Gestion des comptes avec filtres et pagination
- Utilise le trait `ApiResponseTrait` pour standardiser les réponses

#### Resources API
- `AccountResource` : Formatage des données de compte selon la spécification

#### Traits
- `ApiResponseTrait` : Méthodes utilitaires pour les réponses JSON standardisées

#### Scopes Eloquent
- `notDeleted()` : Comptes non supprimés (soft delete)
- `byNumber($number)` : Recherche par numéro de compte
- `byClient($clientId)` : Comptes d'un client spécifique

#### Middleware
- `RatingMiddleware` : Gestion des limites de taux (rate limiting)

### ✅ Configuration
- **CORS** : Configuré pour les requêtes cross-origin
- **Routes** : Groupées par version API (v1)
- **Documentation** : Routes documentées avec exemples

## 🛠 Installation & Configuration

### Prérequis
- PHP 8.1+
- Composer
- PostgreSQL
- Node.js & npm

### Installation
```bash
# Cloner le projet
git clone <repository-url>
cd BankManager

# Installer les dépendances PHP
composer install

# Installer les dépendances JS
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Configurer la base de données dans .env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=bankmanager
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Migrations et seeders
php artisan migrate:fresh --seed

# Compiler les assets
npm run build

# Démarrer le serveur
php artisan serve --host=0.0.0.0 --port=8000
```

### Tests API
```bash
# Démarrer le serveur en arrière-plan
php artisan serve --host=0.0.0.0 --port=8000 &

# Tester l'endpoint principal
curl -X GET "http://localhost:8000/api/v1/comptes?page=1&limit=5" \
     -H "Accept: application/json"

# Tester avec filtres
curl -X GET "http://localhost:8000/api/v1/comptes?type=epargne&statut=active" \
     -H "Accept: application/json"
```

## 📁 Structure du Projet

```
BankManager/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   └── AccountController.php
│   │   └── Resources/
│   │       └── AccountResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Admin.php
│   │   ├── Account.php
│   │   └── Transaction.php
│   └── ApiResponseTrait.php
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── ClientFactory.php
│   │   ├── AdminFactory.php
│   │   ├── AccountFactory.php
│   │   └── TransactionFactory.php
│   ├── migrations/
│   │   ├── *_create_users_table.php
│   │   ├── *_create_clients_table.php
│   │   ├── *_create_admins_table.php
│   │   ├── *_create_accounts_table.php
│   │   └── *_create_transactions_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── ClientSeeder.php
│       ├── AdminSeeder.php
│       ├── AccountSeeder.php
│       └── TransactionSeeder.php
├── routes/
│   └── api.php
└── README.md
```

## 🔧 Technologies Utilisées

- **Laravel 11** : Framework PHP
- **PostgreSQL** : Base de données
- **Eloquent ORM** : Mapping objet-relationnel
- **API Resource** : Formatage des réponses JSON
- **Soft Deletes** : Suppression logique
- **UUID** : Identifiants uniques
- **Rate Limiting** : Contrôle des requêtes

## 📊 Données de Test

Après exécution des seeders :
- **10 Clients** avec comptes associés
- **5 Admins** pour la gestion
- **20 Comptes** (mélange épargne/chèque, statuts variés)
- **50 Transactions** (crédits/débits, statuts variés)

## 🚀 Déploiement

```bash
# Production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# bankmanager
