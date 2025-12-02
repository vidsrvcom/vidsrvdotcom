<?php

function getSuggestions($pdo) {
    $query = $_GET['q'] ?? '';
    $field = $_GET['field'] ?? 'pose'; // pose or outfit
    $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
    
    if (empty($query)) {
        throw new Exception('Query parameter "q" is required');
    }
    
    // Validate field
    if (!in_array($field, ['pose', 'outfit', 'background'])) {
        $field = 'pose';
    }
    
    $search_term = $query . '%';
    
    $sql = "SELECT DISTINCT $field as suggestion, COUNT(*) as count 
            FROM images 
            WHERE $field LIKE :query AND $field IS NOT NULL
            GROUP BY $field
            ORDER BY count DESC, $field ASC 
            LIMIT :limit";
    
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
