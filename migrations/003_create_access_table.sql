CREATE TABLE access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,      
    granted_to_id INT NOT NULL,  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_to_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_access (owner_id, granted_to_id)
);