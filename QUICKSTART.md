# Guide de démarrage rapide

## 🚀 Installation rapide

### 1. Installer les dépendances

```bash
composer install
```

### 2. Configurer l'environnement

```bash
cp .env.example .env
nano .env
```

Configurez au minimum :

- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `BREVO_API_KEY` (pour l'envoi d'emails)

### 3. Créer la base de données

```bash
mysql -u root -e "CREATE DATABASE okr_app"
```

### 4. Exécuter les migrations

```bash
./migrate.sh
```

Ou manuellement :

```bash
mysql -u root okr_app < migrations/00-init.sql
mysql -u root okr_app < migrations/01-add-comments.sql
mysql -u root okr_app < migrations/02-remove-okr-company.sql
mysql -u root okr_app < migrations/03-add-teams-and-users.sql
mysql -u root okr_app < migrations/04-add-key-results.sql
mysql -u root okr_app < migrations/05-add-auth-to-users.sql
```

### 5. Démarrer le serveur

```bash
php -S localhost:8000 -t public
```

## ✅ Tester l'authentification

### 1. Accéder à l'application

Ouvrez votre navigateur : http://localhost:8000

Vous verrez la landing page d'OKR Flow avec des modales d'authentification.

### 2. Créer un compte

1. Cliquez sur "Get Started" ou "Sign Up"
2. Remplissez le formulaire d'inscription dans la modale
3. Vous serez automatiquement connecté et redirigé vers `/dashboard`

### 3. Se connecter

1. Cliquez sur "Sign In"
2. Utilisez vos identifiants dans la modale
3. Redirection automatique vers `/dashboard` après connexion

### 4. Tester l'API

**Sans authentification** (retourne une erreur) :

```bash
curl http://localhost:8000/api/users
# {"error":"Non authentifié"}
```

**Après connexion** (fonctionne) :

```bash
curl -X POST http://localhost:8000/auth/register \
  -d "first_name=John&last_name=Doe&email=john@example.com&password=password123"

curl http://localhost:8000/api/users \
  -H "Cookie: PHPSESSID=..."
```

### 5. Mot de passe oublié

1. Cliquez sur "Forgot password?" dans la modale de connexion
2. Entrez votre email
3. Vous recevrez un email avec un lien de réinitialisation (via Brevo)
4. Cliquez sur le lien et définissez un nouveau mot de passe dans le modal qui s'ouvre sur la home

## 🔐 Sécurité

### Sessions

Les sessions PHP sont utilisées pour maintenir l'état de connexion.

### Mots de passe

Tous les mots de passe sont hashés avec `password_hash()` (bcrypt).

### Tokens de réinitialisation

- Générés avec `random_bytes(32)` (64 caractères hexadécimaux)
- Valides pendant 1 heure
- À usage unique

## 🚨 Dépannage

### "Cannot use object of type DI\Container as array"

Le problème est déjà corrigé. Si vous le rencontrez, vérifiez que vous utilisez bien :

```php
$container->get('pdo')  // ✅ Correct
$container['pdo']       // ❌ Incorrect
```

### "Call to a member function set() on null"

Le container DI n'est pas initialisé. Vérifiez que `bootstrap/app.php` crée bien un `new Container()`.

### Les emails ne sont pas envoyés

1. Vérifiez votre clé API Brevo dans `.env`
2. Vérifiez que votre compte Brevo est actif
3. Consultez les logs Brevo pour voir les erreurs

### La page d'accueil ne charge pas

1. Vérifiez que le serveur PHP est démarré
2. Vérifiez que la base de données est accessible
3. Consultez les logs du serveur PHP

## 📚 Pour aller plus loin

- Voir [AUTHENTICATION.md](AUTHENTICATION.md) pour la documentation complète
- Voir [README.md](README.md) pour plus d'informations sur l'API
