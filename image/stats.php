<?php

function getStats($pdo) {
    $stats = [];
    
    // Total images
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM images");
    $stats['total_images'] = (int)$stmt->fetchColumn();
    
    // By gender
    $stmt = $pdo->query("SELECT character_gender as gender, COUNT(*) as count FROM images WHERE character_gender IS NOT NULL GROUP BY character_gender ORDER BY count DESC");
    $stats['by_gender'] = $stmt->fetchAll();
    
    // By style
    $stmt = $pdo->query("SELECT character_style as style, COUNT(*) as count FROM images WHERE character_style IS NOT NULL GROUP BY character_style ORDER BY count DESC");
    $stats['by_style'] = $stmt->fetchAll();
    
    // By pose
    $stmt = $pdo->query("SELECT pose, COUNT(*) as count FROM images WHERE pose IS NOT NULL GROUP BY pose ORDER BY count DESC LIMIT 20");
    $stats['by_pose'] = $stmt->fetchAll();
    
    // By outfit
    $stmt = $pdo->query("SELECT outfit, COUNT(*) as count FROM images WHERE outfit IS NOT NULL GROUP BY outfit ORDER BY count DESC LIMIT 20");
    $stats['by_outfit'] = $stmt->fetchAll();
    
    // Resolution stats
    $stmt = $pdo->query("SELECT AVG(width) as avg_width, AVG(height) as avg_height, MIN(width) as min_width, MAX(width) as max_width, MIN(height) as min_height, MAX(height) as max_height FROM images WHERE width IS NOT NULL AND height IS NOT NULL");
    $resolution = $stmt->fetch();
    $stats['resolution_stats'] = [
        'avg_width' => $resolution['avg_width'] ? round((float)$resolution['avg_width'], 2) : null,
        'avg_height' => $resolution['avg_height'] ? round((float)$resolution['avg_height'], 2) : null,
        'min_width' => $resolution['min_width'] ? (int)$resolution['min_width'] : null,
        'max_width' => $resolution['max_width'] ? (int)$resolution['max_width'] : null,
        'min_height' => $resolution['min_height'] ? (int)$resolution['min_height'] : null,
        'max_height' => $resolution['max_height'] ? (int)$resolution['max_height'] : null
    ];
    
    // Top characters by image count
    $stmt = $pdo->query("SELECT character_id, COUNT(*) as image_count FROM images WHERE character_id IS NOT NULL GROUP BY character_id ORDER BY image_count DESC LIMIT 10");
    $stats['top_characters'] = $stmt->fetchAll();
    
    // Recent images
    $stmt = $pdo->query("SELECT id, character_id, pose, outfit, width, height, created_at FROM images ORDER BY id DESC LIMIT 10");
    $stats['recent'] = $stmt->fetchAll();
    
    return $stats;
}
