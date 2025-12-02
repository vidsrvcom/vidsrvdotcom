<?php
/**
 * Get single character by ID
 * GET /character/{id}
 */

function getCharacter($pdo, $character_id) {
    // Optimize: Use BINARY for case-sensitive comparison on indexed column
    $sql = "SELECT * FROM `characters` WHERE `id` = :id OR `display_id` = :display_id LIMIT 1";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $character_id, ':display_id' => $character_id]);
        
        $character = $stmt->fetch();
        
        if (!$character) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Character not found'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Format output
        $formatted = formatCharacter($character);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $formatted
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Format character data for output
 */
function formatCharacter($data) {
    // Optimize: Decode JSON fields only if not empty
    if (!empty($data['tags'])) {
        $data['tags'] = json_decode($data['tags'], true) ?? [];
    } else {
        $data['tags'] = [];
    }
    
    if (!empty($data['display_image_urls'])) {
        $data['display_image_urls'] = json_decode($data['display_image_urls'], true) ?? [];
    } else {
        $data['display_image_urls'] = [];
    }
    
    if (!empty($data['initial_messages'])) {
        $decoded = json_decode($data['initial_messages'], true) ?? [];
        // Flatten if double-nested: [[{...}]] -> [{...}]
        if (is_array($decoded) && count($decoded) === 1 && is_array($decoded[0])) {
            $decoded = $decoded[0];
        }
        $data['initial_messages'] = $decoded;
    } else {
        $data['initial_messages'] = [];
    }
    
    // Optimize: Type casting in bulk
    $data['hidden'] = (bool)($data['hidden'] ?? 0);
    $data['created_with_ai'] = (bool)($data['created_with_ai'] ?? 0);
    $data['age'] = isset($data['age']) ? (int)$data['age'] : null;
    $data['message_count'] = isset($data['message_count']) ? (int)$data['message_count'] : 0;
    $data['like_count'] = isset($data['like_count']) ? (int)$data['like_count'] : 0;
    $data['estimated_message_count'] = isset($data['estimated_message_count']) ? (int)$data['estimated_message_count'] : 0;
    $data['creator_level'] = isset($data['creator_level']) ? (int)$data['creator_level'] : null;
    $data['creator_follower_count'] = isset($data['creator_follower_count']) ? (int)$data['creator_follower_count'] : 0;
    $data['creator_following_count'] = isset($data['creator_following_count']) ? (int)$data['creator_following_count'] : 0;
    
    // Remove null or empty fields to reduce payload size
    return array_filter($data, function($value) {
        return $value !== null && $value !== '';
    });
}
