<?php
/**
 * Get search suggestions/autocomplete
 * GET /api.php?action=suggest&q=xxx
 */

function getSuggestions($pdo) {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? min(20, max(1, (int)$_GET['limit'])) : 10;
    
    if (empty($query)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Query parameter "q" is required'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        $sql = "SELECT `id`, `display_id`, `name`, `short_description`, `like_count` 
                FROM `characters` 
                WHERE `name` LIKE :query 
                ORDER BY `like_count` DESC, `name` ASC 
                LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':query', $query . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $suggestions = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $suggestions,
            'query' => $query,
            'count' => count($suggestions)
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
