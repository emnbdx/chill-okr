CREATE TABLE key_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    node_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    progress TINYINT UNSIGNED NULL,
    weight TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_key_results_node FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE,
    CONSTRAINT chk_key_results_weight CHECK (
        weight >= 1
        AND weight <= 10
    )
);

CREATE INDEX idx_key_results_node ON key_results (node_id);