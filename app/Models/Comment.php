<?php

namespace App\Models;

use PDO;

class Comment
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all comments for an article with nested replies
     * @param int $articleId
     * @return array
     */
    public function getByArticleId(int $articleId): array
    {
        // Get all comments for the article
        $stmt = $this->db->prepare("
            SELECT * FROM comments
            WHERE article_id = :article_id
            ORDER BY created_at ASC
        ");
        $stmt->execute(['article_id' => $articleId]);
        $comments = $stmt->fetchAll();

        // Build nested structure
        return $this->buildCommentTree($comments);
    }

    /**
     * Build comment tree structure with one level of nesting
     * @param array $comments
     * @return array
     */
    private function buildCommentTree(array $comments): array
    {
        $tree = [];
        $indexed = [];

        // Index all comments by ID
        foreach ($comments as $comment) {
            $comment['replies'] = [];
            $indexed[$comment['id']] = $comment;
        }

        // Build tree structure
        foreach ($indexed as $id => $comment) {
            if ($comment['parent_id'] === null) {
                // Top-level comment
                $tree[] = &$indexed[$id];
            } else {
                // Reply to another comment
                if (isset($indexed[$comment['parent_id']])) {
                    $indexed[$comment['parent_id']]['replies'][] = &$indexed[$id];
                }
            }
        }

        return $tree;
    }

    /**
     * Get comment by ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM comments WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create new comment or reply
     * @param array $data
     * @return int Last insert ID
     */
    public function create(array $data): int
    {
        // Let the database set created_at via default CURRENT_TIMESTAMP
        $stmt = $this->db->prepare("
            INSERT INTO comments (article_id, author, content, parent_id)
            VALUES (:article_id, :author, :content, :parent_id)
        ");

        $stmt->execute([
            'article_id' => $data['article_id'],
            'author' => $data['author'],
            'content' => $data['content'],
            'parent_id' => $data['parent_id'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete comment
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Check if comment exists
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchColumn() > 0;
    }
}

