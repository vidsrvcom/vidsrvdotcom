<?php
/**
 * Get API statistics
 * GET /api.php?action=stats
 */

function getStats($pdo) {
    try {
        // Total characters
        $total = $pdo->query("SELECT COUNT(*) FROM `characters`")->fetchColumn();
        
        // By gender
        $genderStats = $pdo->query("
            SELECT `gender`, COUNT(*) as count 
            FROM `characters` 
            WHERE `gender` IS NOT NULL 
            GROUP BY `gender`
        ")->fetchAll();
        
        // By style
        $styleStats = $pdo->query("
            SELECT `style`, COUNT(*) as count 
            FROM `characters` 
            WHERE `style` IS NOT NULL 
            GROUP BY `style` 
            ORDER BY count DESC 
            LIMIT 10
        ")->fetchAll();
        
        // By visibility
        $visibilityStats = $pdo->query("
            SELECT `visibility`, COUNT(*) as count 
            FROM `characters` 
            WHERE `visibility` IS NOT NULL 
            GROUP BY `visibility`
        ")->fetchAll();
        
        // By ethnicity
        $ethnicityStats = $pdo->query("
            SELECT `ethnicity`, COUNT(*) as count 
            FROM `characters` 
            WHERE `ethnicity` IS NOT NULL AND `ethnicity` != ''
            GROUP BY `ethnicity` 
            ORDER BY count DESC 
            LIMIT 20
        ")->fetchAll();
        
        // By body_type
        $bodyTypeStats = $pdo->query("
            SELECT `body_type`, COUNT(*) as count 
            FROM `characters` 
            WHERE `body_type` IS NOT NULL AND `body_type` != ''
            GROUP BY `body_type` 
            ORDER BY count DESC
        ")->fetchAll();
        
        // By hair_color
        $hairColorStats = $pdo->query("
            SELECT `hair_color`, COUNT(*) as count 
            FROM `characters` 
            WHERE `hair_color` IS NOT NULL AND `hair_color` != ''
            GROUP BY `hair_color` 
            ORDER BY count DESC
        ")->fetchAll();
        
        // By eye_color
        $eyeColorStats = $pdo->query("
            SELECT `eye_color`, COUNT(*) as count 
            FROM `characters` 
            WHERE `eye_color` IS NOT NULL AND `eye_color` != ''
            GROUP BY `eye_color` 
            ORDER BY count DESC
        ")->fetchAll();
        
        // By skin_color
        $skinColorStats = $pdo->query("
            SELECT `skin_color`, COUNT(*) as count 
            FROM `characters` 
            WHERE `skin_color` IS NOT NULL AND `skin_color` != ''
            GROUP BY `skin_color` 
            ORDER BY count DESC
        ")->fetchAll();
        
        // Created with AI
        $aiCreated = $pdo->query("SELECT COUNT(*) FROM `characters` WHERE `created_with_ai` = 1")->fetchColumn();
        $humanCreated = $pdo->query("SELECT COUNT(*) FROM `characters` WHERE `created_with_ai` = 0 OR `created_with_ai` IS NULL")->fetchColumn();
        
        // Top liked characters
        $topLiked = $pdo->query("
            SELECT `id`, `name`, `like_count` 
            FROM `characters` 
            WHERE `like_count` > 0 
            ORDER BY `like_count` DESC 
            LIMIT 10
        ")->fetchAll();
        
        // Top by messages
        $topMessages = $pdo->query("
            SELECT `id`, `name`, `message_count` 
            FROM `characters` 
            WHERE `message_count` > 0 
            ORDER BY `message_count` DESC 
            LIMIT 10
        ")->fetchAll();
        
        // Recent characters
        $recent = $pdo->query("
            SELECT `id`, `name`, `created_at` 
            FROM `characters` 
            ORDER BY `created_at` DESC 
            LIMIT 10
        ")->fetchAll();
        
        // Approved vs not approved
        $approved = $pdo->query("SELECT COUNT(*) FROM `characters` WHERE `approved_at` IS NOT NULL")->fetchColumn();
        $notApproved = $pdo->query("SELECT COUNT(*) FROM `characters` WHERE `approved_at` IS NULL")->fetchColumn();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'total_characters' => (int)$total,
                'by_gender' => $genderStats,
                'by_style' => $styleStats,
                'by_visibility' => $visibilityStats,
                'by_ethnicity' => $ethnicityStats,
                'by_body_type' => $bodyTypeStats,
                'by_hair_color' => $hairColorStats,
                'by_eye_color' => $eyeColorStats,
                'by_skin_color' => $skinColorStats,
                'top_liked' => $topLiked,
                'top_messages' => $topMessages,
                'recent' => $recent,
                'approval_stats' => [
                    'approved' => (int)$approved,
                    'not_approved' => (int)$notApproved
                ],
                'creation_stats' => [
                    'ai_created' => (int)$aiCreated,
                    'human_created' => (int)$humanCreated
                ]
            ]
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
