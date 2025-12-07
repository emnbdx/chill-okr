CREATE DATABASE okr_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE okr_app;

CREATE TABLE nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    type ENUM(
        'company',
        'axis',
        'okr_team',
        'okr_perso'
    ) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    owner VARCHAR(255) NULL,
    progress TINYINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_nodes_parent FOREIGN KEY (parent_id) REFERENCES nodes (id) ON DELETE CASCADE
);

CREATE INDEX idx_nodes_parent ON nodes (parent_id);

CREATE INDEX idx_nodes_type ON nodes(type);