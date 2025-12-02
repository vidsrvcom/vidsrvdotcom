<?php

function getStats($pdo) {
    $stats = [];
    
    // Total videos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos");
    $stats['total_videos'] = (int)$stmt->fetchColumn();
    
    // By type
    $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM videos WHERE type IS NOT NULL GROUP BY type ORDER BY count DESC");
    $stats['by_type'] = $stmt->fetchAll();
    
    // By action
    $stmt = $pdo->query("SELECT action, COUNT(*) as count FROM videos WHERE action IS NOT NULL GROUP BY action ORDER BY count DESC LIMIT 20");
    $stats['by_action'] = $stmt->fetchAll();
    
    // By gender
    $stmt = $pdo->query("SELECT character_gender as gender, COUNT(*) as count FROM videos WHERE character_gender IS NOT NULL GROUP BY character_gender ORDER BY count DESC");
    $stats['by_gender'] = $stmt->fetchAll();
    
    // By quality
    $stmt = $pdo->query("SELECT quality, COUNT(*) as count FROM videos WHERE quality IS NOT NULL GROUP BY quality ORDER BY count DESC");
    $stats['by_quality'] = $stmt->fetchAll();
    
    // By style
    $stmt = $pdo->query("SELECT character_style as style, COUNT(*) as count FROM videos WHERE character_style IS NOT NULL GROUP BY character_style ORDER BY count DESC");
    $stats['by_style'] = $stmt->fetchAll();
    
    // Average duration
    $stmt = $pdo->query("SELECT AVG(duration) as avg_duration, MIN(duration) as min_duration, MAX(duration) as max_duration FROM videos WHERE duration IS NOT NULL");
    $duration_stats = $stmt->fetch();
    $stats['duration_stats'] = [
        'average' => $duration_stats['avg_duration'] ? round((float)$duration_stats['avg_duration'], 2) : null,
        'min' => $duration_stats['min_duration'] ? (int)$duration_stats['min_duration'] : null,
        'max' => $duration_stats['max_duration'] ? (int)$duration_stats['max_duration'] : null
    ];
    
    // Top characters by video count
    $stmt = $pdo->query("SELECT character_id, character_name, COUNT(*) as video_count FROM videos WHERE character_id IS NOT NULL GROUP BY character_id, character_name ORDER BY video_count DESC LIMIT 10");
    $stats['top_characters'] = $stmt->fetchAll();
    
    // Recent videos
    $stmt = $pdo->query("SELECT id, character_name, type, action, created_at FROM videos ORDER BY created_at DESC LIMIT 10");
    $stats['recent'] = $stmt->fetchAll();
    
    // Error stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE error IS NOT NULL AND error != ''");
    $error_count = (int)$stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE error IS NULL OR error = ''");
    $success_count = (int)$stmt->fetchColumn();
    
    $stats['error_stats'] = [
        'total_errors' => $error_count,
        'total_success' => $success_count,
        'error_rate' => $stats['total_videos'] > 0 ? round(($error_count / $stats['total_videos']) * 100, 2) : 0
    ];
    
    // Enhanced stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE is_enhanced = 1");
    $enhanced_count = (int)$stmt->fetchColumn();
    $stats['enhancement_stats'] = [
        'enhanced' => $enhanced_count,
        'not_enhanced' => $stats['total_videos'] - $enhanced_count,
        'enhancement_rate' => $stats['total_videos'] > 0 ? round(($enhanced_count / $stats['total_videos']) * 100, 2) : 0
    ];
    
    return $stats;
}
