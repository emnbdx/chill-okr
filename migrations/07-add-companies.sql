CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE users ADD COLUMN company_id INT NULL AFTER email;

ALTER TABLE users
ADD CONSTRAINT fk_users_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL;

CREATE INDEX idx_users_company ON users (company_id);

ALTER TABLE teams ADD COLUMN company_id INT NULL AFTER name;

ALTER TABLE teams
ADD CONSTRAINT fk_teams_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE;

CREATE INDEX idx_teams_company ON teams (company_id);
