<?php

require_once 'get.php';

function getRandomVideo($pdo) {
    // Filters
    $character_id = $_GET['character_id'] ?? null;
    $type = $_GET['type'] ?? null;
    $action = $_GET['action_filter'] ?? null;
    $gender = $_GET['gender'] ?? null;
    $quality = $_GET['quality'] ?? null;
    $style = $_GET['style'] ?? null;
    $no_errors = isset($_GET['no_errors']) ? filter_var($_GET['no_errors'], FILTER_VALIDATE_BOOLEAN) : false;
    
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
    
    if ($style !== null) {
        $where[] = "character_style = :style";
        $params[':style'] = $style;
    }
    
    if ($no_errors) {
        $where[] = "(error IS NULL OR error = '')";
    }
    
    $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT * FROM videos $where_sql ORDER BY RAND() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $video = $stmt->fetch();
    
    if (!$video) {
        throw new Exception('No videos found matching criteria');
    }
    
    return formatVideo($video);
}
