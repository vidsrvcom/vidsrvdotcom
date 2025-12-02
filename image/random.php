<?php

require_once 'get.php';

function getRandomImage($pdo) {
    // Filters
    $character_id = $_GET['character_id'] ?? null;
    $gender = $_GET['gender'] ?? null;
    $style = $_GET['style'] ?? null;
    $pose = $_GET['pose'] ?? null;
    $outfit = $_GET['outfit'] ?? null;
    
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
    
    if ($pose !== null) {
        $where[] = "pose = :pose";
        $params[':pose'] = $pose;
    }
    
    if ($outfit !== null) {
        $where[] = "outfit = :outfit";
        $params[':outfit'] = $outfit;
    }
    
    $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT * FROM images $where_sql ORDER BY RAND() LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $image = $stmt->fetch();
    
    if (!$image) {
        throw new Exception('No images found matching criteria');
    }
    
    return formatImage($image);
}
