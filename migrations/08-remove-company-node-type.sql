ALTER TABLE nodes
MODIFY COLUMN type ENUM(
    'axis',
    'okr_team',
    'okr_perso'
) NOT NULL;

UPDATE nodes SET parent_id = NULL WHERE type = 'axis';

DELETE FROM nodes WHERE type = 'company';
