<?php

function getVideo($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $video = $stmt->fetch();
    
    if (!$video) {
        throw new Exception('Video not found');
    }
    
    return formatVideo($video);
}

function formatVideo($video) {
    if (empty($video)) {
        return null;
    }
    
    // Type casting
    $video['width'] = isset($video['width']) ? (int)$video['width'] : null;
    $video['height'] = isset($video['height']) ? (int)$video['height'] : null;
    $video['duration'] = isset($video['duration']) ? (int)$video['duration'] : null;
    $video['is_enhanced'] = isset($video['is_enhanced']) ? (bool)$video['is_enhanced'] : null;
    
    // Remove null and empty values
    return array_filter($video, function($value) {
        return $value !== null && $value !== '';
    });
}
