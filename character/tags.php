<?php
/**
 * Get popular tags
 * GET /api.php?action=tags
 */

function getPopularTags($pdo) {
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 50;
    
    try {
        // Get all characters with tags
        $stmt = $pdo->query("SELECT `tags` FROM `characters` WHERE `tags` IS NOT NULL AND `tags` != ''");
        $characters = $stmt->fetchAll();
        
        // Count tags
        $tagCounts = [];
        foreach ($characters as $character) {
            $tags = json_decode($character['tags'], true);
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                        if (!isset($tagCounts[$tag])) {
                            $tagCounts[$tag] = 0;
                        }
                        $tagCounts[$tag]++;
                    }
                }
            }
        }
        
        // Sort by count
        arsort($tagCounts);
        
        // Limit results
        $tagCounts = array_slice($tagCounts, 0, $limit, true);
        
        // Format output
        $formatted = [];
        foreach ($tagCounts as $tag => $count) {
            $formatted[] = [
                'tag' => $tag,
                'count' => $count
            ];
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $formatted,
            'total_unique_tags' => count($tagCounts)
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
