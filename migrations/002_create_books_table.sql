CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY ,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT, 
    file_path VARCHAR(255),
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_books (user_id, is_deleted)

);