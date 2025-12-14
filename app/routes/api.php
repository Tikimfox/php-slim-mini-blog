<?php


use App\Controllers\ArticleController;
use App\Controllers\CommentController;
use App\Services\ArticleService;
use App\Services\CommentService;
use App\Config\Database;
use Slim\Routing\RouteCollectorProxy;

// Get database connection
$db = Database::getConnection();

// Initialize services
$articleService = new ArticleService($db);
$commentService = new CommentService($db);

// Initialize controllers
$articleController = new ArticleController($articleService);
$commentController = new CommentController($commentService);

// API Routes Group
$app->group('/api', function (RouteCollectorProxy $group) use ($articleController, $commentController) {

    // Article routes
    $group->get('/articles', [$articleController, 'getAll']);
    $group->get('/articles/{id}', [$articleController, 'getById']);
    $group->post('/articles', [$articleController, 'create']);
    $group->put('/articles/{id}', [$articleController, 'update']);
    $group->delete('/articles/{id}', [$articleController, 'delete']);

    // Comment routes
    $group->get('/articles/{articleId}/comments', [$commentController, 'getByArticleId']);
    $group->post('/comments', [$commentController, 'create']);
    $group->delete('/comments/{id}', [$commentController, 'delete']);
});

// Root endpoint
$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'name' => 'Mini-blog API',
        'version' => '1.0.0',
        'status' => 'running',
        'endpoints' => [
            'health' => '/health',
            'articles' => '/api/articles',
            'article_by_id' => '/api/articles/{id}',
            'comments' => '/api/articles/{articleId}/comments',
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

// Health check endpoint
$app->get('/health', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'status' => 'ok',
        'message' => 'Mini-blog API is running',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json');
});

