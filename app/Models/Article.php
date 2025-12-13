<?php

namespace App\Models;

use PDO;

class Article
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all articles with short description
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                title,
                SUBSTR(content, 1, 200) as description,
                author,
                created_at,
                updated_at
            FROM articles
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get article by ID with full content
     * @param int $id
     * @return array|null
     */
    public function getById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM articles WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create new article
     * @param array $data
     * @return int Last insert ID
     */
    public function create(array $data): int
    {
        // Let the database set created_at/updated_at via defaults
        $stmt = $this->db->prepare("
            INSERT INTO articles (title, content, author)
            VALUES (:title, :content, :author)
        ");

        $stmt->execute([
            'title' => $data['title'],
            'content' => $data['content'],
            'author' => $data['author']
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update article
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE articles
            SET title = :title,
                content = :content,
                author = :author,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'content' => $data['content'],
            'author' => $data['author']
        ]);
    }

    /**
     * Delete article
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
