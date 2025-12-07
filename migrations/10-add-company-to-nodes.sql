ALTER TABLE nodes ADD COLUMN company_id INT NULL AFTER user_id;

ALTER TABLE nodes
ADD CONSTRAINT fk_nodes_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE;

CREATE INDEX idx_nodes_company ON nodes (company_id);