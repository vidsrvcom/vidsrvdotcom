<?php

function getImage($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM images WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $image = $stmt->fetch();
    
    if (!$image) {
        throw new Exception('Image not found');
    }
    
    return formatImage($image);
}

function formatImage($image) {
    if (empty($image)) {
        return null;
    }
    
    // Type casting
    $image['width'] = isset($image['width']) ? (int)$image['width'] : null;
    $image['height'] = isset($image['height']) ? (int)$image['height'] : null;
    
    // Remove null and empty values
    return array_filter($image, function($value) {
        return $value !== null && $value !== '';
    });
}
