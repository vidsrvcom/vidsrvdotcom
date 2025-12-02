<?php

function getPopularTags($pdo) {
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
    $type = $_GET['type'] ?? 'action'; // action, type, outfit, background, speed, quality
    
    // Validate type
    $valid_types = ['action', 'type', 'outfit', 'background', 'speed', 'quality'];
    if (!in_array($type, $valid_types)) {
        $type = 'action';
    }
    
    $field_map = [
        'action' => 'action',
        'type' => 'type',
        'outfit' => 'outfit',
        'background' => 'background',
        'speed' => 'speed',
        'quality' => 'quality'
    ];
    
    $field = $field_map[$type];
    
    $sql = "SELECT $field as tag, COUNT(*) as count 
            FROM videos 
            WHERE $field IS NOT NULL AND $field != ''
            GROUP BY $field 
            ORDER BY count DESC, $field ASC 
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    return [
        'data' => $results,
        'type' => $type,
        'total_unique_tags' => count($results)
    ];
}
