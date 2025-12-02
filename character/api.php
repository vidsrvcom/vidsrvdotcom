<?php
/**
 * Characters API - Main Endpoint
 * Handles routing for character operations
 */

// Enable gzip compression for better performance
if (!ob_start('ob_gzhandler')) ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$db_config = [
    'host' => 'localhost',
    'database' => 'our',
    'username' => 'root',
    'password' => 'Abc123123',
    'charset' => 'utf8mb4'
];

// Connect to database
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Get action from query parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$character_id = isset($_GET['id']) ? $_GET['id'] : null;

// Route requests
try {
    switch ($action) {
        case 'get':
            if (!$character_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Character ID is required'
                ]);
                exit();
            }
            // Get single character
            require_once 'get.php';
            getCharacter($pdo, $character_id);
            break;
            
        case 'stats':
            // Get statistics
            require_once 'stats.php';
            getStats($pdo);
            break;
            
        case 'random':
            // Get random character
            require_once 'random.php';
            getRandomCharacter($pdo);
            break;
            
        case 'similar':
            if (!$character_id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Character ID is required'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            require_once 'similar.php';
            getSimilarCharacters($pdo, $character_id);
            break;
            
        case 'suggest':
            require_once 'suggest.php';
            getSuggestions($pdo);
            break;
            
        case 'tags':
            require_once 'tags.php';
            getPopularTags($pdo);
            break;
            
        case 'trending':
            require_once 'trending.php';
            getTrendingCharacters($pdo);
            break;
            
        case 'batch':
            require_once 'batch.php';
            getBatchCharacters($pdo);
            break;
            
        case 'list':
        default:
            // Get all characters with filters
            require_once 'list.php';
            listCharacters($pdo);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
