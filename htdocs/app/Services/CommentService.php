<?php

namespace App\Services;

use App\Models\Comment;
use PDO;

class CommentService
{
    private $commentModel;

    public function __construct(PDO $db)
    {
        $this->commentModel = new Comment($db);
    }

    /**
     * Get all comments for an article
     * @param int $articleId
     * @return array
     */
    public function getCommentsByArticleId(int $articleId): array
    {
        return $this->commentModel->getByArticleId($articleId);
    }

    /**
     * Create new comment with validation
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function createComment(array $data): array
    {
        // Validate input
        $errors = $this->validateCommentData($data);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        // If parent_id is provided, check if parent comment exists
        if (!empty($data['parent_id'])) {
            if (!$this->commentModel->exists($data['parent_id'])) {
                throw new \InvalidArgumentException(json_encode([
                    'parent_id' => 'Parent comment does not exist'
                ]));
            }
        }

        // Create comment
        $id = $this->commentModel->create($data);

        // Return created comment
        return $this->commentModel->getById($id);
    }

    /**
     * Delete comment
     * @param int $id
     * @return bool
     */
    public function deleteComment(int $id): bool
    {
        return $this->commentModel->delete($id);
    }

    /**
     * Validate comment data
     * @param array $data
     * @return array Errors array
     */
    private function validateCommentData(array $data): array
    {
        $errors = [];

        // Validate all
        if (empty($data['article_id'])) {
            $errors['article_id'] = 'Article ID is required';
        }

        if (empty($data['author'])) {
            $errors['author'] = 'Author is required';
        } elseif (strlen($data['author']) > 100) {
            $errors['author'] = 'Author name must be less than 100 characters';
        }

        if (empty($data['content'])) {
            $errors['content'] = 'Content is required';
        }

        return $errors;
    }
}

