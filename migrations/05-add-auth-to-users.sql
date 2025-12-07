ALTER TABLE users
ADD COLUMN email VARCHAR(255) UNIQUE NULL AFTER last_name;

ALTER TABLE users ADD COLUMN password VARCHAR(255) NULL AFTER email;

ALTER TABLE users
ADD COLUMN reset_token VARCHAR(255) NULL AFTER password;

ALTER TABLE users
ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token;

CREATE INDEX idx_users_email ON users (email);

CREATE INDEX idx_users_reset_token ON users (reset_token);