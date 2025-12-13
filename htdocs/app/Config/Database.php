<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dbPath = __DIR__ . '/../../storage/database.sqlite';

                // Create storage directory if it doesn't exist
                $storageDir = dirname($dbPath);
                if (!is_dir($storageDir)) {
                    mkdir($storageDir, 0777, true);
                }

                self::$connection = new PDO('sqlite:' . $dbPath);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

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
        $sql = "
            -- Articles table
            CREATE TABLE IF NOT EXISTS articles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                content TEXT NOT NULL,
                author TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- Comments table
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

            -- Index for better performance
            CREATE INDEX IF NOT EXISTS idx_comments_article ON comments(article_id);
            CREATE INDEX IF NOT EXISTS idx_comments_parent ON comments(parent_id);
        ";

        self::$connection->exec($sql);
    }

    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}

