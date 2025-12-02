<?php
// Enable gzip compression for better performance
if (!ob_start('ob_gzhandler')) ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database configuration
$host = 'localhost';
$dbname = 'our';
$username = 'root';
$password = 'Abc123123';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            require_once 'list.php';
            $result = listImages($pdo);
            break;
            
        case 'get':
            require_once 'get.php';
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Image ID is required');
            }
            $result = getImage($pdo, $id);
            break;
            
        case 'stats':
            require_once 'stats.php';
            $result = getStats($pdo);
            break;
            
        case 'random':
            require_once 'random.php';
            $result = getRandomImage($pdo);
            break;
            
        case 'similar':
            require_once 'similar.php';
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('Image ID is required');
            }
            $result = getSimilarImages($pdo, $id);
            break;
            
        case 'suggest':
            require_once 'suggest.php';
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                throw new Exception('Query parameter "q" is required');
            }
            $result = getSuggestions($pdo);
            break;
            
        case 'tags':
            require_once 'tags.php';
            $result = getPopularTags($pdo);
            break;
            
        case 'trending':
            require_once 'trending.php';
            $result = getTrendingImages($pdo);
            break;
            
        case 'batch':
            require_once 'batch.php';
            $ids = $_GET['ids'] ?? '';
            if (empty($ids)) {
                throw new Exception('IDs parameter is required');
            }
            $result = getBatchImages($pdo, $ids);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $result['data'] ?? $result,
        ...(isset($result['pagination']) ? ['pagination' => $result['pagination']] : []),
        ...(isset($result['reference_image']) ? ['reference_image' => $result['reference_image']] : []),
        ...(isset($result['query']) ? ['query' => $result['query']] : []),
        ...(isset($result['field']) ? ['field' => $result['field']] : []),
        ...(isset($result['count']) ? ['count' => $result['count']] : []),
        ...(isset($result['requested']) ? ['requested' => $result['requested']] : []),
        ...(isset($result['found']) ? ['found' => $result['found']] : []),
        ...(isset($result['period_days']) ? ['period_days' => $result['period_days']] : []),
        ...(isset($result['total_unique_tags']) ? ['total_unique_tags' => $result['total_unique_tags']] : [])
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
