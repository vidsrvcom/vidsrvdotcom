<?php

require_once 'get.php';

function getBatchImages($pdo, $ids_string) {
    $ids = array_filter(array_map('trim', explode(',', $ids_string)));
    
    if (empty($ids)) {
        throw new Exception('No valid IDs provided');
    }
    
    if (count($ids) > 50) {
        throw new Exception('Maximum 50 IDs allowed');
    }
    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM images WHERE id IN ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $images = $stmt->fetchAll();
    
    $formatted = array_map('formatImage', $images);
    
    return [
        'data' => array_values(array_filter($formatted)),
        'requested' => count($ids),
        'found' => count($formatted)
    ];
}
