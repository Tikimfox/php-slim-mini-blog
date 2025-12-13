-- MySQL Database Schema for Mini-Blog

-- Create database
CREATE DATABASE IF NOT EXISTS miniblog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE miniblog;

-- Articles table
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    parent_id INT NULL,
    author VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_comments_article (article_id),
    INDEX idx_comments_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--Insert sample data
INSERT INTO articles (title, content, author) VALUES
('Welcome to Mini-Blog', 'This is your first article. You can edit or delete it, or create new articles.', 'Admin'),
('Getting Started', 'Learn how to use this mini-blog system. Add articles, comments, and replies to comments.', 'Admin');

INSERT INTO comments (article_id, parent_id, author, content) VALUES
(1, NULL, 'John Doe', 'Great article! Thanks for sharing.'),
(1, 1, 'Admin', 'Thank you for your feedback!'),
(1, NULL, 'Jane Smith', 'Very informative content.');

