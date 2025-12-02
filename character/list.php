<?php
/**
 * List characters with filtering, sorting, and pagination
 * GET /character
 */

function listCharacters($pdo) {
    // Get query parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
    $offset = ($page - 1) * $limit;
    
    // Filters
    $filters = [];
    $params = [];
    
    // Search across all text columns including new fields
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchTerm = '%' . trim($_GET['search']) . '%';
        $searchColumns = ['name', 'description', 'short_description', 'personality', 'personality_details', 'scenario', 'occupation', 'relationship', 'hobby', 'fetish', 'extra_details', 'gender', 'style', 'visibility', 'ethnicity', 'eye_color', 'hair_color', 'hair_style', 'body_type', 'skin_color'];
        $searchConditions = [];
        foreach ($searchColumns as $i => $column) {
            $paramKey = "search{$i}";
            $searchConditions[] = "`{$column}` LIKE :{$paramKey}";
            $params[$paramKey] = $searchTerm;
        }
        $filters[] = '(' . implode(' OR ', $searchConditions) . ')';
    }
    
    // Filter by visibility
    if (isset($_GET['visibility'])) {
        $filters[] = "`visibility` = :visibility";
        $params['visibility'] = $_GET['visibility'];
    }
    
    // Filter by hidden
    if (isset($_GET['hidden'])) {
        $filters[] = "`hidden` = :hidden";
        $params['hidden'] = (int)(bool)$_GET['hidden'];
    }
    
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
    
    // Filter by tag
    if (isset($_GET['tag']) && !empty($_GET['tag'])) {
        $filters[] = "JSON_CONTAINS(`tags`, :tag, '$')";
        $params['tag'] = json_encode($_GET['tag']);
    }
    
    // Filter by approved
    if (isset($_GET['approved'])) {
        if ($_GET['approved'] === 'true' || $_GET['approved'] === '1') {
            $filters[] = "`approved_at` IS NOT NULL";
        } else {
            $filters[] = "`approved_at` IS NULL";
        }
    }
    
    // Sorting
    $allowed_sort = ['created_at', 'name', 'message_count', 'like_count', 'age', 'gender', 'style', 'visibility', 'hidden', 'approved_at', 'estimated_message_count', 'id'];
    $sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowed_sort) ? $_GET['sort_by'] : 'created_at';
    $sort_order = isset($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'ASC' ? 'ASC' : 'DESC';
    
    // Build WHERE clause
    $where = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
    
    // Build ORDER BY clause
    $orderBy = "ORDER BY `$sort_by` $sort_order";
    
    try {
        // Get total count first (separate query for better performance)
        $countSql = "SELECT COUNT(*) as total FROM `characters` $where";
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(":$key", $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();
        
        // Main query without SQL_CALC_FOUND_ROWS
        $sql = "SELECT * FROM `characters` $where $orderBy LIMIT :limit OFFSET :offset";
        
        // Get characters
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $characters = $stmt->fetchAll();
        
        // Format characters
        $formatted = array_map('formatCharacter', $characters);
        
        // Calculate pagination
        $total_pages = ceil($total / $limit);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $formatted,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int)$total,
                'total_pages' => $total_pages,
                'has_more' => $page < $total_pages
            ]
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

/**
 * Format character data for output (optimized)
 */
function formatCharacter($data) {
    // Decode JSON fields efficiently
    $data['tags'] = !empty($data['tags']) ? json_decode($data['tags'], true) ?? [] : [];
    $data['display_image_urls'] = !empty($data['display_image_urls']) ? json_decode($data['display_image_urls'], true) ?? [] : [];
    $data['initial_messages'] = !empty($data['initial_messages']) ? json_decode($data['initial_messages'], true) ?? [] : [];
    
    // Type conversions
    $data['hidden'] = (bool)($data['hidden'] ?? 0);
    $data['created_with_ai'] = (bool)($data['created_with_ai'] ?? 0);
    $data['age'] = isset($data['age']) ? (int)$data['age'] : null;
    $data['message_count'] = isset($data['message_count']) ? (int)$data['message_count'] : 0;
    $data['like_count'] = isset($data['like_count']) ? (int)$data['like_count'] : 0;
    $data['estimated_message_count'] = isset($data['estimated_message_count']) ? (int)$data['estimated_message_count'] : 0;
    $data['creator_level'] = isset($data['creator_level']) ? (int)$data['creator_level'] : null;
    $data['creator_follower_count'] = isset($data['creator_follower_count']) ? (int)$data['creator_follower_count'] : 0;
    $data['creator_following_count'] = isset($data['creator_following_count']) ? (int)$data['creator_following_count'] : 0;
    
    // Remove null/empty fields to reduce payload
    return array_filter($data, function($value) {
        return $value !== null && $value !== '';
    });
}
