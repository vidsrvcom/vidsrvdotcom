<?php
/**
 * Get multiple characters by IDs
 * GET /api.php?action=batch&ids=id1,id2,id3
 */

function getBatchCharacters($pdo) {
    $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
    
    if (empty($ids)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Parameter "ids" is required (comma-separated)'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // Parse IDs
    $idArray = array_map('trim', explode(',', $ids));
    $idArray = array_filter($idArray); // Remove empty values
    $idArray = array_slice($idArray, 0, 50); // Max 50 IDs
    
    if (empty($idArray)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'No valid IDs provided'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        // Build placeholders
        $placeholders = implode(',', array_fill(0, count($idArray), '?'));
        
        $sql = "SELECT * FROM `characters` WHERE `id` IN ($placeholders) OR `display_id` IN ($placeholders)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($idArray, $idArray));
        
        $characters = $stmt->fetchAll();
        
        // Format output
        require_once 'list.php';
        $formatted = array_map('formatCharacter', $characters);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $formatted,
            'requested' => count($idArray),
            'found' => count($formatted)
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
