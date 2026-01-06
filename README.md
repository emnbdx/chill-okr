# Chill OKR - Transform Your Goals Into Measurable Results

## Setup

### 1. Installation des dépendances

```bash
composer install
```

### 2. Configuration de l'environnement

Copiez `.env.example` vers `.env` et configurez vos paramètres :

```bash
cp .env.example .env
```

Éditez `.env` avec vos paramètres de base de données et votre clé API Brevo.

### 3. Création de la base de données

```bash
mysql -u root -e "CREATE DATABASE okr_app"
```

### 4. Exécution des migrations

```bash
mysql -u root okr_app < migrations/00-init.sql
mysql -u root okr_app < migrations/01-add-comments.sql
mysql -u root okr_app < migrations/02-remove-okr-company.sql
mysql -u root okr_app < migrations/03-add-teams-and-users.sql
mysql -u root okr_app < migrations/04-add-key-results.sql
mysql -u root okr_app < migrations/05-add-auth-to-users.sql
mysql -u root okr_app < migrations/06-fix-users-unique-constraint.sql
mysql -u root okr_app < migrations/07-add-companies.sql
mysql -u root okr_app < migrations/08-remove-company-node-type.sql
mysql -u root okr_app < migrations/09-add-company-owner.sql
mysql -u root okr_app < migrations/10-add-company-to-nodes.sql
```

**⚠️ Migration de données existantes** : Si vous avez déjà des nodes dans votre base, consultez [SECURITY_MIGRATION.md](SECURITY_MIGRATION.md) pour assigner correctement les `company_id`.

### 5. Démarrage du serveur

```bash
php -S localhost:8000 -t public
```

Accédez à http://localhost:8000

## Structure de l'application

- **Landing Page** (`/`) - Page d'accueil avec présentation et modales d'authentification intégrées
- **Dashboard** (`/dashboard`) - Application OKR principale (nécessite authentification)
- **Reset Password** (modal sur `/`) - Réinitialisation de mot de passe (via lien email)

L'authentification (login, register, forgot password) se fait via les modales de la landing page. Les anciennes URLs `/login`, `/register` et `/forgot-password` redirigent automatiquement vers la landing page.

Voir [AUTHENTICATION.md](AUTHENTICATION.md) pour la documentation complète.

## Sécurité et Gestion des Droits

L'application implémente deux niveaux de sécurité :

### 1. Isolation par Company

Tous les nodes sont strictement isolés par company. Aucune donnée ne peut fuiter entre différentes companies.

### 2. Visibilité des OKR Personnels

Les OKR personnels ont une visibilité restreinte. Ils ne sont visibles que par :

- Le owner de la company
- Le owner de l'OKR team parent
- Le owner de l'OKR perso lui-même

Voir [OKR_PERMISSIONS.md](OKR_PERMISSIONS.md) pour la documentation complète.

## API Endpoints

### Authentication

- POST `/auth/register` - Inscription
- POST `/auth/login` - Connexion
- POST `/auth/logout` - Déconnexion
- POST `/auth/forgot-password` - Demande de réinitialisation
- POST `/auth/reset-password` - Réinitialisation

### Nodes (OKR)

- GET `/api/nodes` - Liste des nodes
- POST `/api/nodes` - Créer un node
- PATCH `/api/nodes/{id}` - Modifier un node
- DELETE `/api/nodes/{id}` - Supprimer un node

### Comments

- GET `/api/nodes/{node_id}/comments` - Liste des commentaires
- POST `/api/nodes/{node_id}/comments` - Ajouter un commentaire

### Key Results

- GET `/api/nodes/{node_id}/key-results` - Liste des key results
- POST `/api/nodes/{node_id}/key-results` - Créer un key result
- PATCH `/api/key-results/{id}` - Modifier un key result
- DELETE `/api/key-results/{id}` - Supprimer un key result

### Teams & Users

- GET `/api/teams` - Liste des équipes
- POST `/api/teams` - Créer une équipe
- GET `/api/users` - Liste des utilisateurs
- POST `/api/users` - Créer un utilisateur
