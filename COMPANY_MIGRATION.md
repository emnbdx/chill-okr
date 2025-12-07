# Migration: Nodes Company → Company Entity

## Changements effectués

### 1. Suppression du type "company" des nodes

- Le type `company` n'existe plus dans l'arborescence des nodes
- La hiérarchie commence maintenant directement avec les **axis**
- Migration SQL créée : `08-remove-company-node-type.sql`

### 2. Company = Entité indépendante

- La `company` est maintenant une table séparée (table `companies`)
- Chaque utilisateur appartient à une company (`users.company_id`)
- Chaque team appartient à une company (`teams.company_id`)
- Les nodes sont filtrés par company via les users/teams

### 3. Interface Company dédiée

- Nouveau bouton **🏢 Company** dans le header
- Modale dédiée avec :
  - Nom de la company (éditable)
  - Liste des membres de l'équipe
  - Invitation de nouveaux utilisateurs avec envoi d'email automatique

### 4. Nouvelle hiérarchie

```
Company (entité séparée)
  └── Axis (root nodes)
      └── OKR Team
          └── OKR Perso
```

## Migrations à exécuter

```bash
# Migration 1 : Ajouter la table companies et les liens
mysql -u root -p okr_app < migrations/07-add-companies.sql

# Migration 2 : Supprimer le type company des nodes
mysql -u root -p okr_app < migrations/08-remove-company-node-type.sql
```

⚠️ **IMPORTANT** : La migration 08 supprime tous les nodes de type "company" existants !

## Fonctionnalités Company

### Via l'interface

1. Cliquer sur **🏢 Company** dans le header
2. Modifier le nom de la company
3. Inviter des utilisateurs (prénom, nom, email)
4. Voir la liste des membres et leur statut d'invitation

### Via l'API

- `GET /api/company` - Infos de la company
- `PATCH /api/company` - Modifier le nom
- `GET /api/company/users` - Liste des users
- `POST /api/company/invite` - Inviter un user

### Invitations

- Les invitations envoient un email avec un lien pour définir le mot de passe
- Le lien est valable 7 jours
- Les users invités apparaissent avec le badge "Invitation pending"

## Isolement des données

Toutes les données sont maintenant **isolées par company** :

- ✅ Nodes (via users/teams)
- ✅ Teams (company_id)
- ✅ Users (company_id)
- ✅ Comments (via nodes)
- ✅ Key Results (via nodes)

Un utilisateur ne voit QUE les données de sa company.

