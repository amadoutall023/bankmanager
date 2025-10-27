<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

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

## 🚀 Déploiement

### Déploiement Local avec Docker

```bash
# Démarrer PostgreSQL et PGAdmin
docker-compose up -d postgres pgadmin

# Construire et démarrer l'application
docker build -t bankmanager .
docker run -p 8000:80 --env-file BankManager/.env bankmanager
```

### Déploiement sur Render

#### Méthode 1 : Configuration Automatique (Recommandée)

1. **Pousser le code sur GitHub** avec tous les fichiers :
   - `Dockerfile`
   - `render.yaml`
   - `.dockerignore`
   - Code source complet

2. **Connecter Render à GitHub** :
   - Aller sur [Render Dashboard](https://dashboard.render.com)
   - Cliquer "New +" → "Blueprint"
   - Sélectionner votre repository GitHub

3. **Render détectera automatiquement** :
   - Le fichier `render.yaml`
   - Créera le service web + base de données
   - Configurera toutes les variables d'environnement

4. **Déployer** : Un clic suffit !

#### Méthode 2 : Configuration Manuelle

1. **Service Web** :
   - Runtime : Docker
   - Dockerfile Path : `./Dockerfile`

2. **Base de données** :
   - PostgreSQL (plan gratuit)
   - Nom : `bankmanager-db`

3. **Variables d'environnement** (auto-configurées via `render.yaml`)

#### Vérification du Déploiement

Une fois déployé, testez l'API :
```bash
curl -X GET "https://votre-app.onrender.com/api/v1/comptes?page=1&limit=5" \
     -H "Accept: application/json"
```

### Vérification du Déploiement

```bash
# Tester l'endpoint principal
curl -X GET "https://votre-app.onrender.com/api/v1/comptes?page=1&limit=5" \
     -H "Accept: application/json"
```

## 📖 API Documentation - Tests avec Postman

### Configuration de Base
- **Base URL** : `http://localhost:8000/api/v1` (local) ou `https://votre-app.onrender.com/api/v1` (production)
- **Headers communs** :
  - `Content-Type: application/json`
  - `Accept: application/json`

### 🆕 Endpoint : Création de Compte Bancaire

#### **POST /api/v1/comptes**
Crée un nouveau compte bancaire. Si le client n'existe pas, il est créé automatiquement avec génération de mot de passe et code de vérification.

#### ✅ Exemple 1 : Création avec nouveau client
```json
{
  "method": "POST",
  "url": "http://localhost:8000/api/v1/comptes",
  "headers": {
    "Content-Type": "application/json",
    "Accept": "application/json"
  },
  "body": {
    "type": "cheque",
    "soldeInitial": 500000,
    "devise": "FCFA",
    "solde": 10000,
    "client": {
      "titulaire": "Hawa BB Wane",
      "email": "hawa.wane@example.com",
      "telephone": "+221771234567",
      "adresse": "Dakar, Sénégal"
    }
  }
}
```

**Réponse attendue (201 Created)** :
```json
{
  "success": true,
  "message": "Compte créé avec succès",
  "data": {
    "id": "uuid-generated",
    "numeroCompte": "C00123456",
    "titulaire": "Hawa BB Wane",
    "type": "cheque",
    "solde": 10000,
    "devise": "FCFA",
    "dateCreation": "2025-10-27T10:30:00Z",
    "statut": "actif",
    "metadata": {
      "derniereModification": "2025-10-27T10:30:00Z",
      "version": 1
    }
  }
}
```

#### ✅ Exemple 2 : Création avec client existant
```json
{
  "method": "POST",
  "url": "http://localhost:8000/api/v1/comptes",
  "headers": {
    "Content-Type": "application/json",
    "Accept": "application/json"
  },
  "body": {
    "type": "epargne",
    "soldeInitial": 100000,
    "devise": "FCFA",
    "solde": 25000,
    "client": {
      "id": 1
    }
  }
}
```

#### ❌ Exemple 3 : Erreur de validation (solde insuffisant)
```json
{
  "method": "POST",
  "url": "http://localhost:8000/api/v1/comptes",
  "headers": {
    "Content-Type": "application/json",
    "Accept": "application/json"
  },
  "body": {
    "type": "cheque",
    "soldeInitial": 5000,
    "devise": "FCFA",
    "solde": 5000,
    "client": {
      "titulaire": "Test User",
      "email": "test@example.com",
      "telephone": "+221781234567",
      "adresse": "Dakar, Sénégal"
    }
  }
}
```

**Réponse d'erreur (400 Bad Request)** :
```json
{
  "success": false,
  "message": "Les données fournies sont invalides",
  "errors": {
    "soldeInitial": ["Le solde initial doit être supérieur ou égal à 10000"],
    "solde": ["Le solde doit être supérieur ou égal à 10000"]
  }
}
```

#### ❌ Exemple 4 : Erreur téléphone invalide
```json
{
  "method": "POST",
  "url": "http://localhost:8000/api/v1/comptes",
  "headers": {
    "Content-Type": "application/json",
    "Accept": "application/json"
  },
  "body": {
    "type": "cheque",
    "soldeInitial": 15000,
    "devise": "FCFA",
    "solde": 15000,
    "client": {
      "titulaire": "Test User",
      "email": "test@example.com",
      "telephone": "+221123456789",
      "adresse": "Dakar, Sénégal"
    }
  }
}
```

**Réponse d'erreur** :
```json
{
  "success": false,
  "message": "Les données fournies sont invalides",
  "errors": {
    "client.telephone": ["Le numéro de téléphone doit être un numéro sénégalais valide (+22177XXXXXXX, +22178XXXXXXX, etc.)"]
  }
}
```

### 📋 Règles de Validation

| Champ | Règle | Description |
|-------|-------|-------------|
| `type` | `required\|in:epargne,cheque` | Type de compte obligatoire |
| `soldeInitial` | `required\|numeric\|min:10000` | Minimum 10 000 FCFA |
| `devise` | `required\|in:FCFA` | Uniquement FCFA |
| `solde` | `required\|numeric\|min:10000` | Minimum 10 000 FCFA |
| `client.id` | `nullable\|exists:clients,id` | ID client existant (optionnel) |
| `client.titulaire` | `required_if:client.id,null\|string\|max:255` | Nom requis si nouveau client |
| `client.email` | `required_if:client.id,null\|email\|unique:users,email` | Email unique requis |
| `client.telephone` | `required_if:client.id,null\|regex:/^\+221(77\|78\|70\|76\|75\|33)[0-9]{7}$/` | Téléphone sénégalais unique |
| `client.adresse` | `required_if:client.id,null\|string\|max:500` | Adresse requise |

### 🔧 Téléphones Sénégalais Valides
- `+22177XXXXXXX` (Orange)
- `+22178XXXXXXX` (Free)
- `+22170XXXXXXX` (Expresso)
- `+22176XXXXXXX` (Promobile)
- `+22175XXXXXXX` (Chaka)
- `+22133XXXXXXX` (fixe Dakar)

### 📊 Autres Endpoints Disponibles

#### **GET /api/v1/comptes** - Lister les comptes
```bash
curl -X GET "http://localhost:8000/api/v1/comptes?page=1&limit=10&type=cheque&statut=actif" \
     -H "Accept: application/json"
```

#### **GET /api/v1/comptes/{id}** - Détails d'un compte
```bash
curl -X GET "http://localhost:8000/api/v1/comptes/uuid-here" \
     -H "Accept: application/json"
```

#### **PUT /api/v1/comptes/{id}** - Modifier un compte
```bash
curl -X PUT "http://localhost:8000/api/v1/comptes/uuid-here" \
     -H "Content-Type: application/json" \
     -d '{"type": "epargne", "balance": 50000}' \
     -H "Accept: application/json"
```

#### **DELETE /api/v1/comptes/{id}** - Supprimer un compte
```bash
curl -X DELETE "http://localhost:8000/api/v1/comptes/uuid-here" \
     -H "Accept: application/json"
```

### Optimisations pour la Production

```bash
# Caches pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
