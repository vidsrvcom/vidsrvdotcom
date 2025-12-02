<?php

function getSuggestions($pdo) {
    $query = $_GET['q'] ?? '';
    $field = $_GET['field'] ?? 'character_name'; // character_name or action
    $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
    
    if (empty($query)) {
        throw new Exception('Query parameter "q" is required');
    }
    
    // Validate field
    if (!in_array($field, ['character_name', 'action'])) {
        $field = 'character_name';
    }
    
    $search_term = $query . '%';
    
    if ($field === 'character_name') {
        $sql = "SELECT DISTINCT character_name as suggestion, character_id, character_gender, character_style 
                FROM videos 
                WHERE character_name LIKE :query AND character_name IS NOT NULL
                ORDER BY character_name ASC 
                LIMIT :limit";
    } else {
        $sql = "SELECT DISTINCT action as suggestion, COUNT(*) as count 
                FROM videos 
                WHERE action LIKE :query AND action IS NOT NULL
                GROUP BY action
                ORDER BY count DESC, action ASC 
                LIMIT :limit";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':query', $search_term, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    return [
        'data' => $results,
        'query' => $query,
        'field' => $field,
        'count' => count($results)
    ];
}
