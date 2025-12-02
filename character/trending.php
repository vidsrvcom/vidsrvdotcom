<?php
/**
 * Get trending characters
 * GET /api.php?action=trending
 */

function getTrendingCharacters($pdo) {
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;
    $days = isset($_GET['days']) ? min(90, max(1, (int)$_GET['days'])) : 7;
    
    try {
        // Calculate trending score based on likes and messages
        // Prioritize recent characters with high engagement
        $sql = "SELECT `id`, `display_id`, `name`, `short_description`, 
                       `like_count`, `message_count`, `created_at`,
                       (`like_count` * 2 + `message_count`) / DATEDIFF(NOW(), `created_at`) as trending_score
                FROM `characters` 
                WHERE `created_at` >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  AND (`like_count` > 0 OR `message_count` > 0)
                ORDER BY trending_score DESC, `like_count` DESC
                LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $trending = $stmt->fetchAll();
        
        // Format trending score
        foreach ($trending as &$item) {
            $item['trending_score'] = round($item['trending_score'], 2);
            $item['like_count'] = (int)$item['like_count'];
            $item['message_count'] = (int)$item['message_count'];
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $trending,
            'period_days' => $days,
            'count' => count($trending)
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
