# Système d'authentification Chill OKR

## Installation

### 1. Installer les dépendances

```bash
composer install
```

### 2. Configurer l'environnement

Copiez le fichier `.env.example` en `.env` et configurez vos paramètres :

```bash
cp .env.example .env
```

Éditez le fichier `.env` :

```
DB_HOST=127.0.0.1
DB_NAME=okr_app
DB_USER=root
DB_PASS=

BREVO_API_KEY=votre_clé_api_brevo
BREVO_SENDER_EMAIL=noreply@votredomaine.com
BREVO_SENDER_NAME="Chill OKR"

APP_URL=http://localhost:8000
APP_NAME="Chill OKR"
```

### 3. Exécuter les migrations

Exécutez le fichier de migration SQL pour ajouter les champs d'authentification :

```bash
mysql -u root okr_app < migrations/05-add-auth-to-users.sql
```

## Configuration Brevo

1. Créez un compte sur [Brevo](https://www.brevo.com/)
2. Allez dans **Settings** > **SMTP & API** > **API Keys**
3. Créez une nouvelle clé API
4. Copiez la clé dans votre fichier `.env`

## Fonctionnalités

### Landing Page avec Modales (`/`)

L'authentification se fait via des modales sur la landing page :

**Inscription (Modal Register)**

- Formulaire avec prénom, nom, email et mot de passe
- Validation de l'email
- Mot de passe minimum 8 caractères
- Envoi d'un email de bienvenue
- Connexion automatique après inscription
- Redirection vers `/dashboard`

**Connexion (Modal Login)**

- Authentification par email/mot de passe
- Gestion de session PHP
- Redirection vers `/dashboard` après connexion

**Mot de passe oublié (Modal Forgot)**

- Saisie de l'email
- Génération d'un token unique
- Envoi d'un email avec lien de réinitialisation
- Token valide pendant 1 heure

### Réinitialisation (`/?reset-token=XXX`)

- Page dédiée accessible via lien email
- Formulaire de nouveau mot de passe
- Validation du token
- Mise à jour du mot de passe
- Redirection vers `/` après succès

### Déconnexion (`/auth/logout`)

- Destruction de la session
- Redirection vers `/`

## API Endpoints

### POST `/auth/register`

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "motdepasse123"
}
```

### POST `/auth/login`

```json
{
  "email": "john@example.com",
  "password": "motdepasse123"
}
```

### POST `/auth/forgot-password`

```json
{
  "email": "john@example.com"
}
```

### POST `/auth/reset-password`

```json
{
  "token": "abc123...",
  "password": "nouveaumotdepasse"
}
```

## Middleware d'authentification

### Protection automatique des routes

Par défaut, **toutes les routes sont protégées** sauf les routes d'authentification :

Routes publiques (accessibles sans connexion) :

- `GET /` - Landing page avec modales d'authentification
- `GET /login` - Redirige vers `/`
- `GET /register` - Redirige vers `/`
- `GET /forgot-password` - Redirige vers `/`
- Modal de réinitialisation sur la home (via lien email avec paramètre reset-token)
- `POST /auth/login` - API connexion
- `POST /auth/register` - API inscription
- `POST /auth/logout` - API déconnexion
- `POST /auth/forgot-password` - API demande réinitialisation
- `POST /auth/reset-password` - API réinitialisation

Routes protégées (nécessitent une connexion) :

- `GET /dashboard` - Application OKR → Redirige vers `/` si non connecté
- Toutes les routes `/api/*` → Retournent `{"error":"Non authentifié"}` avec statut 401 si non connecté

### Comportement du middleware

**Pour les pages web** : Redirection vers `/`
**Pour les API** : Retour JSON avec erreur 401

### Ajouter la protection à une nouvelle route

```php
use App\Middleware\AuthMiddleware;

$app->get('/protected', function($req, $res) {
  return $res->getBody()->write('Page protégée');
})->add(new AuthMiddleware());
```

## Services disponibles

### AuthService

Gère la logique d'authentification :

- `register()` - Inscription
- `login()` - Connexion
- `requestPasswordReset()` - Demande de réinitialisation
- `resetPassword()` - Réinitialisation
- `startSession()` - Démarrer une session
- `endSession()` - Terminer une session
- `getCurrentUser()` - Récupérer l'utilisateur courant
- `isAuthenticated()` - Vérifier si l'utilisateur est connecté

### MailService

Gère l'envoi d'emails via Brevo :

- `sendResetPasswordEmail()` - Email de réinitialisation
- `sendWelcomeEmail()` - Email de bienvenue

### UserRepository

Accès à la table users avec méthodes d'authentification :

- `findByEmail()` - Trouver par email
- `findByResetToken()` - Trouver par token
- `updatePassword()` - Mettre à jour le mot de passe
- `setResetToken()` - Définir un token de réinitialisation
- `clearResetToken()` - Effacer le token

## Structure de la table users

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NULL,
    password VARCHAR(255) NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expires DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (first_name, last_name)
);
```

## Sécurité

- Les mots de passe sont hashés avec `password_hash()` (bcrypt)
- Les tokens de réinitialisation sont générés avec `random_bytes()`
- Les tokens expirent après 1 heure
- Les sessions PHP sont utilisées pour maintenir l'état de connexion
- Validation des entrées utilisateur
- Protection contre les injections SQL via PDO prepared statements

## Personnalisation des emails

Modifiez les templates dans `app/Services/MailService.php` :

- `sendResetPasswordEmail()` - Template de réinitialisation
- `sendWelcomeEmail()` - Template de bienvenue
