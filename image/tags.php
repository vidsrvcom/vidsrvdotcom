<?php

function getPopularTags($pdo) {
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
    $type = $_GET['type'] ?? 'pose'; // pose, outfit, background
    
    // Validate type
    $valid_types = ['pose', 'outfit', 'background'];
    if (!in_array($type, $valid_types)) {
        $type = 'pose';
    }
    
    $sql = "SELECT $type as tag, COUNT(*) as count 
            FROM images 
            WHERE $type IS NOT NULL AND $type != ''
            GROUP BY $type 
            ORDER BY count DESC, $type ASC 
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
