<?php

require_once 'get.php';

function getSimilarImages($pdo, $id) {
    // Get reference image
    $stmt = $pdo->prepare("SELECT * FROM images WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $reference = $stmt->fetch();
    
    if (!$reference) {
        throw new Exception('Reference image not found');
    }
    
    // Build similarity query
    if (!empty($reference['character_id'])) {
        $sql = "SELECT *, 
                CASE 
                    WHEN character_id = :character_id THEN 5
                    ELSE 0
                END +
                CASE 
                    WHEN pose = :pose THEN 3
                    ELSE 0
                END +
                CASE 
                    WHEN outfit = :outfit THEN 2
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
                FROM images 
                WHERE id != :id 
                ORDER BY similarity_score DESC, id DESC 
                LIMIT 20";
        
        $params = [
            ':id' => $id,
            ':character_id' => $reference['character_id'],
            ':pose' => $reference['pose'] ?? '',
            ':outfit' => $reference['outfit'] ?? '',
            ':style' => $reference['character_style'] ?? '',
            ':gender' => $reference['character_gender'] ?? ''
        ];
    } else {
        $sql = "SELECT *, 
                CASE 
                    WHEN pose = :pose THEN 3
                    ELSE 0
                END +
                CASE 
                    WHEN outfit = :outfit THEN 2
                    ELSE 0
                END +
                CASE 
                    WHEN character_style = :style THEN 2
                    ELSE 0
                END as similarity_score
                FROM images 
                WHERE id != :id 
                ORDER BY similarity_score DESC, id DESC 
                LIMIT 20";
        
        $params = [
            ':id' => $id,
            ':pose' => $reference['pose'] ?? '',
            ':outfit' => $reference['outfit'] ?? '',
            ':style' => $reference['character_style'] ?? ''
        ];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $images = $stmt->fetchAll();
    
    $formatted = array_map('formatImage', $images);
    
    return [
        'data' => array_values(array_filter($formatted)),
        'reference_image' => [
            'id' => $reference['id'],
            'character_id' => $reference['character_id'],
            'pose' => $reference['pose'],
            'outfit' => $reference['outfit']
        ]
    ];
}
