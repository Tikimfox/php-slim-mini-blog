<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;
    private static array $config;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::$config = require __DIR__ . '/config.php';

            try {
                $dbConfig = self::$config['db'];

                if ($dbConfig['driver'] === 'mysql') {
                    $dsn = sprintf(
                        'mysql:host=%s;port=%s;dbname=%s',
                        $dbConfig['host'],
                        $dbConfig['port'],
                        $dbConfig['database']
                    );

                    self::$connection = new PDO(
                        $dsn,
                        $dbConfig['username'],
                        $dbConfig['password'],
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]
                    );
                } else {
                    // SQLite fallback
                    $dbPath = __DIR__ . '/../../storage/database.sqlite';
                    $storageDir = dirname($dbPath);
                    if (!is_dir($storageDir)) {
                        mkdir($storageDir, 0777, true);
                    }

                    self::$connection = new PDO('sqlite:' . $dbPath);
                    self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                }

                // Initialize database schema
                self::initializeSchema();
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

    private static function initializeSchema(): void
    {
        $driver = self::$config['db']['driver'];

        if ($driver === 'mysql') {
            $tables = [
                "CREATE TABLE IF NOT EXISTS articles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    content TEXT NOT NULL,
                    author VARCHAR(100) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS comments (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            ];

            foreach ($tables as $sql) {
                self::$connection->exec($sql);
            }
        } else {
            $sql = "
                CREATE TABLE IF NOT EXISTS articles (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    content TEXT NOT NULL,
                    author TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE TABLE IF NOT EXISTS comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    article_id INTEGER NOT NULL,
                    parent_id INTEGER NULL,
                    author TEXT NOT NULL,
                    content TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
                );

                CREATE INDEX IF NOT EXISTS idx_comments_article ON comments(article_id);
                CREATE INDEX IF NOT EXISTS idx_comments_parent ON comments(parent_id);
            ";

            self::$connection->exec($sql);
        }
    }

    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}

