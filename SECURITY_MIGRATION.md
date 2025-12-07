# Migration de Sécurité - Ajout de l'Isolation par Company

## Contexte

Cette migration critique ajoute l'isolation stricte des nodes par company pour éviter toute fuite de données entre différentes companies.

## Modifications

### Migration 10 : `10-add-company-to-nodes.sql`

Ajoute la colonne `company_id` à la table `nodes` avec :

- Contrainte de clé étrangère vers `companies`
- Cascade de suppression
- Index pour les performances

## Migration des Données Existantes

Si vous avez déjà des données dans votre base, vous devez assigner une company_id à chaque node existant.

### Étape 1 : Appliquer la migration

```bash
mysql -u root -p okr_app < migrations/10-add-company-to-nodes.sql
```

### Étape 2 : Assigner les company_id aux nodes existants

#### Option A : Si tous vos nodes appartiennent à la même company

```sql
-- Remplacer 1 par l'ID de votre company
UPDATE nodes SET company_id = 1 WHERE company_id IS NULL;
```

#### Option B : Assigner automatiquement selon les users/teams

```sql
-- Pour les nodes avec user_id
UPDATE nodes n
JOIN users u ON n.user_id = u.id
SET n.company_id = u.company_id
WHERE n.company_id IS NULL AND u.company_id IS NOT NULL;

-- Pour les nodes avec team_id
UPDATE nodes n
JOIN teams t ON n.team_id = t.id
SET n.company_id = t.company_id
WHERE n.company_id IS NULL AND t.company_id IS NOT NULL;

-- Pour les nodes axis (sans user ni team)
-- Option 1 : Assigner à toutes les companies
INSERT INTO nodes (parent_id, type, title, description, owner, team_id, user_id, progress, company_id, created_at, updated_at)
SELECT parent_id, type, title, description, owner, team_id, user_id, progress, c.id, created_at, updated_at
FROM nodes n
CROSS JOIN companies c
WHERE n.type = 'axis' AND n.company_id IS NULL;

-- Puis supprimer les originaux
DELETE FROM nodes WHERE type = 'axis' AND company_id IS NULL;

-- Option 2 : Assigner à la première company uniquement
UPDATE nodes SET company_id = (SELECT MIN(id) FROM companies)
WHERE type = 'axis' AND company_id IS NULL;
```

### Étape 3 : Vérification

```sql
-- Vérifier qu'aucun node n'a company_id NULL
SELECT COUNT(*) FROM nodes WHERE company_id IS NULL;
-- Doit retourner 0

-- Vérifier la répartition par company
SELECT company_id, COUNT(*) as nb_nodes
FROM nodes
GROUP BY company_id;
```

### Étape 4 : Rendre la colonne NOT NULL (optionnel, recommandé)

Une fois que tous les nodes ont un company_id :

```sql
ALTER TABLE nodes MODIFY company_id INT NOT NULL;
```

## Impact sur l'Application

### Avant la Migration

- Les nodes n'étaient pas strictement isolés par company
- Risque de fuite de données entre companies
- Filtrage uniquement via users/teams

### Après la Migration

- Isolation stricte : chaque node appartient à une et une seule company
- Impossible d'accéder aux nodes d'une autre company
- Vérifications de sécurité renforcées à tous les niveaux
- Création automatique de company_id lors de la création de nodes

## Sécurité

Cette migration est **critique pour la sécurité** :

- ✅ Empêche les fuites de données entre companies
- ✅ Simplifie et renforce les vérifications de droits
- ✅ Permet de supprimer en cascade tous les nodes d'une company
- ✅ Améliore les performances des requêtes avec l'index

## Rollback (Non Recommandé)

En cas de problème, vous pouvez supprimer la colonne :

```sql
ALTER TABLE nodes DROP FOREIGN KEY fk_nodes_company;
ALTER TABLE nodes DROP INDEX idx_nodes_company;
ALTER TABLE nodes DROP COLUMN company_id;
```

**⚠️ Attention** : Le rollback réintroduit la faille de sécurité !
