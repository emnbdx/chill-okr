# Gestion des Droits et Sécurité

## Vue d'ensemble

L'application implémente deux niveaux de sécurité :

1. **Isolation par company** : Tous les nodes sont liés à une company et strictement isolés
2. **Visibilité des OKR personnels** : Les OKR perso ont une visibilité restreinte au sein d'une company

## Règles de Visibilité

Un OKR personnel (type `okr_perso`) est visible uniquement par :

1. **Le owner de la company** - L'utilisateur défini comme `owner_id` dans la table `companies`
2. **Le owner de l'OKR team parent** - L'utilisateur défini comme `user_id` dans le node parent de type `okr_team`
3. **Le owner de l'OKR perso lui-même** - L'utilisateur défini comme `user_id` dans le node `okr_perso`

## Isolation par Company

### Principe

Chaque node est obligatoirement lié à une company via `company_id`. Cette isolation garantit qu'aucune donnée ne peut fuir entre différentes companies.

### Migration

La migration `10-add-company-to-nodes.sql` ajoute :

- Colonne `company_id` dans la table `nodes`
- Contrainte de clé étrangère vers `companies` avec CASCADE
- Index sur `company_id`

### Vérifications de sécurité

Tous les accès aux nodes vérifient systématiquement :

1. L'utilisateur appartient à une company
2. Le node appartient à la même company que l'utilisateur
3. Lors de la création, le parent (si existe) appartient à la même company

## Visibilité des OKR Personnels

### Base de données

La migration `09-add-company-owner.sql` ajoute :

- Colonne `owner_id` dans la table `companies`
- Contrainte de clé étrangère vers `users`
- Index sur `owner_id`

### Repository

La méthode `NodeRepository::allByCompany()` :

- Filtre d'abord par `company_id` (isolation stricte)
- Puis applique les règles de visibilité des OKR perso

La méthode `NodeRepository::canAccessNode()` :

- Vérifie d'abord que le node appartient à la company de l'utilisateur
- Puis vérifie les droits d'accès selon le type de node

### Controllers

Tous les contrôleurs vérifient les permissions :

**ApiNodeController** :

- `create()` : Définit automatiquement `company_id`, vérifie que le parent appartient à la même company
- `list()` : Filtre automatiquement par company et selon les droits
- `update()` : Vérifie l'accès et que le node appartient à la company
- `delete()` : Vérifie l'accès et que le node appartient à la company

**ApiCommentController** et **ApiKeyResultController** :

- Toutes les opérations vérifient que le node cible appartient à la company de l'utilisateur

### Owner de Company

Lors de l'inscription, l'utilisateur créateur devient automatiquement owner de sa company.

Le owner peut être modifié via l'API `PUT /api/company` avec le champ `owner_id`.

## Exemples

### Scénario 1 : Créateur de Company

```
User A crée son compte
→ Une company est créée automatiquement
→ User A devient owner de cette company
→ User A peut voir TOUS les OKR perso de la company
```

### Scénario 2 : Manager d'équipe

```
User B est défini comme owner d'un OKR team (axis→okr_team)
→ User B peut voir les OKR perso de son équipe (enfants de son okr_team)
→ User B ne peut PAS voir les OKR perso des autres équipes
```

### Scénario 3 : Collaborateur

```
User C est un collaborateur standard
→ User C ne peut voir QUE ses propres OKR perso
→ User C ne peut PAS voir les OKR perso des autres collaborateurs
```

## Migration

Pour appliquer les droits sur une base existante :

```bash
mysql -u root -p okr_app < migrations/09-add-company-owner.sql
```

Puis définir les owners des companies existantes :

```sql
UPDATE companies c
JOIN users u ON u.company_id = c.id
SET c.owner_id = u.id
WHERE c.owner_id IS NULL
LIMIT 1;
```
