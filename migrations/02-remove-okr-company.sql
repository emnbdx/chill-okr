ALTER TABLE nodes MODIFY COLUMN type ENUM(
    'company',
    'axis',
    'okr_team',
    'okr_perso'
) NOT NULL;

DELETE FROM nodes WHERE type = 'okr_company';

