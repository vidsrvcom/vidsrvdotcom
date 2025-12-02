<?php

function getTrendingImages($pdo) {
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $days = min(90, max(1, (int)($_GET['days'] ?? 7)));
    
    // Get recent images (last N days) - use id DESC for chronological order
    $sql = "SELECT * FROM images 
            ORDER BY id DESC 
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll();
    
    require_once 'get.php';
    $formatted = array_map('formatImage', $images);
    
    return [
        'data' => array_values(array_filter($formatted)),
        'period_days' => $days,
        'count' => count($formatted)
    ];
}
