<?php

function listImages($pdo) {
    // Query parameters
    $character_id = $_GET['character_id'] ?? null;
    $gender = $_GET['gender'] ?? null;
    $style = $_GET['style'] ?? null;
    $width = isset($_GET['width']) ? (int)$_GET['width'] : null;
    $height = isset($_GET['height']) ? (int)$_GET['height'] : null;
    $pose = $_GET['pose'] ?? null;
    $outfit = $_GET['outfit'] ?? null;
    $background = $_GET['background'] ?? null;
    $allowed_for_community = $_GET['allowed_for_community'] ?? null;
    $search = $_GET['search'] ?? null;
    $sort_by = $_GET['sort_by'] ?? 'created_at';
    $sort_order = strtoupper($_GET['sort_order'] ?? 'DESC');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50000, max(1, (int)($_GET['limit'] ?? 20)));
    
    // Validate sort order
    if (!in_array($sort_order, ['ASC', 'DESC'])) {
        $sort_order = 'DESC';
    }
    
    // Validate sort_by
    $valid_sort_fields = ['created_at', 'character_id', 'width', 'height', 'pose', 'outfit', 'background', 'character_gender', 'character_style', 'allowed_for_community'];
    if (!in_array($sort_by, $valid_sort_fields)) {
        $sort_by = 'created_at';
    }
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $where = [];
    $params = [];
    
    if ($character_id !== null) {
        $where[] = "character_id = :character_id";
        $params[':character_id'] = $character_id;
    }
    
    if ($gender !== null) {
        $where[] = "character_gender = :gender";
        $params[':gender'] = $gender;
    }
    
    if ($style !== null) {
        $where[] = "character_style = :style";
        $params[':style'] = $style;
    }
    
    if ($width !== null) {
        $where[] = "width = :width";
        $params[':width'] = $width;
    }
    
    if ($height !== null) {
        $where[] = "height = :height";
        $params[':height'] = $height;
    }
    
    if ($pose !== null) {
        $where[] = "pose = :pose";
        $params[':pose'] = $pose;
    }
    
    if ($outfit !== null) {
        $where[] = "outfit = :outfit";
        $params[':outfit'] = $outfit;
    }
    
    if ($background !== null) {
        $where[] = "background = :background";
        $params[':background'] = $background;
    }
    
    if ($allowed_for_community !== null) {
        $where[] = "allowed_for_community = :allowed_for_community";
        $params[':allowed_for_community'] = $allowed_for_community;
    }
    
    if ($search !== null && $search !== '') {
        $where[] = "(caption LIKE :search OR prompt LIKE :search2 OR custom_prompt LIKE :search3)";
        $search_term = '%' . $search . '%';
        $params[':search'] = $search_term;
        $params[':search2'] = $search_term;
        $params[':search3'] = $search_term;
    }
    
    $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count first
    $count_sql = "SELECT COUNT(*) FROM images $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = (int)$count_stmt->fetchColumn();
    
    // Get images - use id for sorting when created_at to avoid filesort on text field
    $order_by = $sort_by === 'created_at' ? 'id' : $sort_by;
    $sql = "SELECT * FROM images $where_sql ORDER BY $order_by $sort_order LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $images = $stmt->fetchAll();
    
    // Format images
    $formatted_images = array_values(array_filter(array_map('formatImage', $images)));
    
    return [
        'data' => $formatted_images,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ];
}

function formatImage($image) {
    if (empty($image)) {
        return null;
    }
    
    // Type casting
    $image['width'] = isset($image['width']) ? (int)$image['width'] : null;
    $image['height'] = isset($image['height']) ? (int)$image['height'] : null;
    
    // Remove null and empty values
    return array_filter($image, function($value) {
        return $value !== null && $value !== '';
    });
}
