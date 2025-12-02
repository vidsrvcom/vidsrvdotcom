<?php

require_once 'get.php';

function getSimilarVideos($pdo, $id) {
    // Get reference video
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $reference = $stmt->fetch();
    
    if (!$reference) {
        throw new Exception('Reference video not found');
    }
    
    // Build similarity query
    $where = ["id != :id"];
    $params = [':id' => $id];
    
    // Same character gets priority
    if (!empty($reference['character_id'])) {
        $sql = "SELECT *, 
                CASE 
                    WHEN character_id = :character_id THEN 5
                    ELSE 0
                END +
                CASE 
                    WHEN type = :type THEN 3
                    ELSE 0
                END +
                CASE 
                    WHEN action = :action THEN 2
                    ELSE 0
                END +
                CASE 
                    WHEN character_style = :style THEN 2
                    ELSE 0
                END +
                CASE 
                    WHEN character_gender = :gender THEN 1
                    ELSE 0
                END as similarity_score
                FROM videos 
                WHERE id != :id 
                ORDER BY similarity_score DESC, created_at DESC 
                LIMIT 20";
        
        $params = [
            ':id' => $id,
            ':character_id' => $reference['character_id'],
            ':type' => $reference['type'] ?? '',
            ':action' => $reference['action'] ?? '',
            ':style' => $reference['character_style'] ?? '',
            ':gender' => $reference['character_gender'] ?? ''
        ];
    } else {
        $sql = "SELECT *, 
                CASE 
                    WHEN type = :type THEN 3
                    ELSE 0
                END +
                CASE 
                    WHEN action = :action THEN 2
                    ELSE 0
                END +
                CASE 
                    WHEN character_style = :style THEN 2
                    ELSE 0
                END as similarity_score
                FROM videos 
                WHERE id != :id 
                ORDER BY similarity_score DESC, created_at DESC 
                LIMIT 20";
        
        $params = [
            ':id' => $id,
            ':type' => $reference['type'] ?? '',
            ':action' => $reference['action'] ?? '',
            ':style' => $reference['character_style'] ?? ''
        ];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $videos = $stmt->fetchAll();
    
    $formatted = array_map('formatVideo', $videos);
    
    return [
        'data' => array_values(array_filter($formatted)),
        'reference_video' => [
            'id' => $reference['id'],
            'character_name' => $reference['character_name'],
            'action' => $reference['action'],
            'type' => $reference['type']
        ]
    ];
}
