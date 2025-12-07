ALTER TABLE companies ADD COLUMN owner_id INT NULL AFTER name;

ALTER TABLE companies
ADD CONSTRAINT fk_companies_owner FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE SET NULL;

CREATE INDEX idx_companies_owner ON companies (owner_id);