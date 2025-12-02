<?php

function getTrendingVideos($pdo) {
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $days = min(90, max(1, (int)($_GET['days'] ?? 7)));
    
    // Get recent videos (last N days)
    $sql = "SELECT * FROM videos 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
            AND (error IS NULL OR error = '')
            ORDER BY created_at DESC 
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $videos = $stmt->fetchAll();
    
    require_once 'get.php';
    $formatted = array_map('formatVideo', $videos);
    
    return [
        'data' => array_values(array_filter($formatted)),
        'period_days' => $days,
        'count' => count($formatted)
    ];
}
