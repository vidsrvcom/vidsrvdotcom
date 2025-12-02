<?php
/**
 * Get random character
 * GET /api.php?action=random
 */

function getRandomCharacter($pdo) {
    // Filters
    $filters = [];
    $params = [];
    
    // Filter by gender
    if (isset($_GET['gender'])) {
        $filters[] = "`gender` = :gender";
        $params['gender'] = $_GET['gender'];
    }
    
    // Filter by style
    if (isset($_GET['style'])) {
        $filters[] = "`style` = :style";
        $params['style'] = $_GET['style'];
    }
    
    // Filter by ethnicity
    if (isset($_GET['ethnicity'])) {
        $filters[] = "`ethnicity` = :ethnicity";
        $params['ethnicity'] = $_GET['ethnicity'];
    }
    
    // Filter by body_type
    if (isset($_GET['body_type'])) {
        $filters[] = "`body_type` = :body_type";
        $params['body_type'] = $_GET['body_type'];
    }
    
    // Filter by hair_color
    if (isset($_GET['hair_color'])) {
        $filters[] = "`hair_color` = :hair_color";
        $params['hair_color'] = $_GET['hair_color'];
    }
    
    // Filter by eye_color
    if (isset($_GET['eye_color'])) {
        $filters[] = "`eye_color` = :eye_color";
        $params['eye_color'] = $_GET['eye_color'];
    }
    
    // Filter by visibility
    if (isset($_GET['visibility'])) {
        $filters[] = "`visibility` = :visibility";
        $params['visibility'] = $_GET['visibility'];
    }
    
    // Filter by approved
    if (isset($_GET['approved']) && ($_GET['approved'] === 'true' || $_GET['approved'] === '1')) {
        $filters[] = "`approved_at` IS NOT NULL";
    }
    
    // Build WHERE clause
    $where = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
    
    $sql = "SELECT * FROM `characters` $where ORDER BY RAND() LIMIT 1";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $character = $stmt->fetch();
        
        if (!$character) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'No character found matching criteria'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Format output (use function from get.php)
        require_once 'get.php';
        $formatted = formatCharacter($character);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $formatted
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
