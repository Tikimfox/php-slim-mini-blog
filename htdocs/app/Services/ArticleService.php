<?php

namespace App\Services;

use App\Models\Article;
use PDO;

class ArticleService
{
    private $articleModel;

    public function __construct(PDO $db)
    {
        $this->articleModel = new Article($db);
    }

    /**
     * Get all articles
     * @return array
     */
    public function getAllArticles(): array
    {
        return $this->articleModel->getAll();
    }

    /**
     * Get article by ID
     * @param int $id
     * @return array|null
     */
    public function getArticleById(int $id)
    {
        return $this->articleModel->getById($id);
    }

    /**
     * Create new article with validation
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function createArticle(array $data): array
    {
        // Validate input
        $errors = $this->validateArticleData($data);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        // Create article
        $id = $this->articleModel->create($data);

        // Return created article
        return $this->articleModel->getById($id);
    }

    /**
     * Update article with validation
     * @param int $id
     * @param array $data
     * @return array|null
     * @throws \InvalidArgumentException
     */
    public function updateArticle(int $id, array $data)
    {
        // Check if article exists
        $article = $this->articleModel->getById($id);
        if (!$article) {
            return null;
        }

        // Validate input
        $errors = $this->validateArticleData($data);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        // Update article
        $this->articleModel->update($id, $data);

        // Return updated article
        return $this->articleModel->getById($id);
    }

    /**
     * Delete article
     * @param int $id
     * @return bool
     */
    public function deleteArticle(int $id): bool
    {
        return $this->articleModel->delete($id);
    }

    /**
     * Validate article data
     * @param array $data
     * @return array Errors array
     */
    private function validateArticleData(array $data): array
    {
        $errors = [];

        // Validate all data
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be less than 255 characters';
        }

        if (empty($data['content'])) {
            $errors['content'] = 'Content is required';
        }

        if (empty($data['author'])) {
            $errors['author'] = 'Author is required';
        } elseif (strlen($data['author']) > 100) {
            $errors['author'] = 'Author name must be less than 100 characters';
        }

        return $errors;
    }
}

