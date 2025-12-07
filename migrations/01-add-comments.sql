CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    node_id INT NOT NULL,
    parent_comment_id INT NULL,
    content TEXT NOT NULL,
    progress_update TINYINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comments_node FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_parent FOREIGN KEY (parent_comment_id) REFERENCES comments (id) ON DELETE CASCADE
);

CREATE INDEX idx_comments_node ON comments (node_id);

CREATE INDEX idx_comments_parent ON comments (parent_comment_id);