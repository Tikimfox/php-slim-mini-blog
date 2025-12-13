<?php

namespace App\Controllers;

use App\Services\CommentService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CommentController
{
    private $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Get all comments
     * GET /api/articles/{articleId}/comments
     */
    public function getByArticleId(Request $request, Response $response, array $args): Response
    {
        try {
            $articleId = (int) $args['articleId'];
            $comments = $this->commentService->getCommentsByArticleId($articleId);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $comments
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));

            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Create new comment or reply
     * POST /api/comments
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $comment = $this->commentService->createComment($data);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Comment created successfully',
                'data' => $comment
            ]));

            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'errors' => json_decode($e->getMessage(), true)
            ]));

            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));

            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Delete comment
     * DELETE /api/comments/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $result = $this->commentService->deleteComment($id);

            if (!$result) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'error' => 'Comment not found'
                ]));

                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));

            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}

