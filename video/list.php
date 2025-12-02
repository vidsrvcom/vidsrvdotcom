<?php

function listVideos($pdo) {
    // Query parameters
    $character_id = $_GET['character_id'] ?? null;
    $type = $_GET['type'] ?? null;
    $action = $_GET['action_filter'] ?? null; // Renamed to avoid conflict with api.php $action
    $gender = $_GET['gender'] ?? null;
    $quality = $_GET['quality'] ?? null;
    $speed = $_GET['speed'] ?? null;
    $style = $_GET['style'] ?? null;
    $width = isset($_GET['width']) ? (int)$_GET['width'] : null;
    $height = isset($_GET['height']) ? (int)$_GET['height'] : null;
    $pose = $_GET['pose'] ?? null;
    $outfit = $_GET['outfit'] ?? null;
    $background = $_GET['background'] ?? null;
    $performance_mode = $_GET['performance_mode'] ?? null;
    $has_error = isset($_GET['has_error']) ? filter_var($_GET['has_error'], FILTER_VALIDATE_BOOLEAN) : null;
    $is_enhanced = isset($_GET['is_enhanced']) ? filter_var($_GET['is_enhanced'], FILTER_VALIDATE_BOOLEAN) : null;
    $min_duration = isset($_GET['min_duration']) ? (int)$_GET['min_duration'] : null;
    $max_duration = isset($_GET['max_duration']) ? (int)$_GET['max_duration'] : null;
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
    $valid_sort_fields = ['created_at', 'character_name', 'character_id', 'type', 'action', 'duration', 'quality', 'speed', 'width', 'height', 'pose', 'outfit', 'background', 'character_gender', 'character_style', 'performance_mode', 'is_enhanced'];
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
    
    if ($type !== null) {
        $where[] = "type = :type";
        $params[':type'] = $type;
    }
    
    if ($action !== null) {
        $where[] = "action = :action";
        $params[':action'] = $action;
    }
    
    if ($gender !== null) {
        $where[] = "character_gender = :gender";
        $params[':gender'] = $gender;
    }
    
    if ($quality !== null) {
        $where[] = "quality = :quality";
        $params[':quality'] = $quality;
    }
    
    if ($speed !== null) {
        $where[] = "speed = :speed";
        $params[':speed'] = $speed;
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
    
    if ($performance_mode !== null) {
        $where[] = "performance_mode = :performance_mode";
        $params[':performance_mode'] = $performance_mode;
    }
    
    if ($has_error !== null) {
        if ($has_error) {
            $where[] = "error IS NOT NULL AND error != ''";
        } else {
            $where[] = "(error IS NULL OR error = '')";
        }
    }
    
    if ($is_enhanced !== null) {
        $where[] = "is_enhanced = :is_enhanced";
        $params[':is_enhanced'] = $is_enhanced ? 1 : 0;
    }
    
    if ($min_duration !== null) {
        $where[] = "duration >= :min_duration";
        $params[':min_duration'] = $min_duration;
    }
    
    if ($max_duration !== null) {
        $where[] = "duration <= :max_duration";
        $params[':max_duration'] = $max_duration;
    }
    
    if ($search !== null && $search !== '') {
        $where[] = "(character_name LIKE :search OR action LIKE :search2 OR custom_prompt LIKE :search3)";
        $search_term = '%' . $search . '%';
        $params[':search'] = $search_term;
        $params[':search2'] = $search_term;
        $params[':search3'] = $search_term;
    }
    
    $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count first (separate query to avoid temp table issues)
    $count_sql = "SELECT COUNT(*) FROM videos $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = (int)$count_stmt->fetchColumn();
    
    // Get videos - use id for sorting when created_at to avoid filesort on text field
    $order_by = $sort_by === 'created_at' ? 'id' : $sort_by;
    $sql = "SELECT * FROM videos $where_sql ORDER BY $order_by $sort_order LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $videos = $stmt->fetchAll();
    
    // Format videos
    $formatted_videos = array_values(array_filter(array_map('formatVideo', $videos)));
    
    return [
        'data' => $formatted_videos,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ];
}

function formatVideo($video) {
    if (empty($video)) {
        return null;
    }
    
    // Type casting
    $video['width'] = isset($video['width']) ? (int)$video['width'] : null;
    $video['height'] = isset($video['height']) ? (int)$video['height'] : null;
    $video['duration'] = isset($video['duration']) ? (int)$video['duration'] : null;
    $video['is_enhanced'] = isset($video['is_enhanced']) ? (bool)$video['is_enhanced'] : null;
    
    // Remove null and empty values
    return array_filter($video, function($value) {
        return $value !== null && $value !== '';
    });
}
