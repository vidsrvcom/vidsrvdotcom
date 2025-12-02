<?php
/**
 * Get similar/related characters
 * GET /api.php?action=similar&id=xxx
 */

function getSimilarCharacters($pdo, $character_id) {
    try {
        // First, get the character
        $stmt = $pdo->prepare("SELECT * FROM `characters` WHERE `id` = :id OR `display_id` = :id LIMIT 1");
        $stmt->execute(['id' => $character_id]);
        $character = $stmt->fetch();
        
        if (!$character) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Character not found'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Build similarity query
        $conditions = [];
        $params = ['excluded_id' => $character['id']];
        $score_parts = [];
        
        // Match by style
        if (!empty($character['style'])) {
            $conditions[] = "`style` = :style";
            $params['style'] = $character['style'];
            $score_parts[] = "IF(`style` = :style, 3, 0)";
        }
        
        // Match by gender
        if (!empty($character['gender'])) {
            $conditions[] = "`gender` = :gender";
            $params['gender'] = $character['gender'];
            $score_parts[] = "IF(`gender` = :gender, 2, 0)";
        }
        
        // Match by ethnicity
        if (!empty($character['ethnicity'])) {
            $conditions[] = "`ethnicity` = :ethnicity";
            $params['ethnicity'] = $character['ethnicity'];
            $score_parts[] = "IF(`ethnicity` = :ethnicity, 2, 0)";
        }
        
        // Match by body_type
        if (!empty($character['body_type'])) {
            $conditions[] = "`body_type` = :body_type";
            $params['body_type'] = $character['body_type'];
            $score_parts[] = "IF(`body_type` = :body_type, 1, 0)";
        }
        
        // Match by hair_color
        if (!empty($character['hair_color'])) {
            $conditions[] = "`hair_color` = :hair_color";
            $params['hair_color'] = $character['hair_color'];
            $score_parts[] = "IF(`hair_color` = :hair_color, 1, 0)";
        }
        
        // Match by eye_color
        if (!empty($character['eye_color'])) {
            $conditions[] = "`eye_color` = :eye_color";
            $params['eye_color'] = $character['eye_color'];
            $score_parts[] = "IF(`eye_color` = :eye_color, 1, 0)";
        }
        
        // Match by tags (at least one common tag)
        if (!empty($character['tags'])) {
            $tags = json_decode($character['tags'], true);
            if (!empty($tags) && is_array($tags)) {
                foreach ($tags as $idx => $tag) {
                    $score_parts[] = "IF(JSON_CONTAINS(`tags`, :tag$idx, '$'), 1, 0)";
                    $params["tag$idx"] = json_encode($tag);
                }
            }
        }
        
        // Build score calculation
        $score_calc = !empty($score_parts) ? implode(' + ', $score_parts) : '0';
        
        $where = !empty($conditions) ? '(' . implode(' OR ', $conditions) . ') AND' : '';
        
        $sql = "SELECT *, ($score_calc) as similarity_score 
                FROM `characters` 
                WHERE $where `id` != :excluded_id 
                HAVING similarity_score > 0
                ORDER BY similarity_score DESC, `like_count` DESC 
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $similar = $stmt->fetchAll();
        
        // Format output
        require_once 'list.php';
        $formatted = array_map('formatCharacter', $similar);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $formatted,
            'reference_character' => [
                'id' => $character['id'],
                'name' => $character['name']
            ]
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
