<?php
// Debug version of list.php
require_once '../config/database.php';

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$filters = [];
$params = [];

if (!empty($searchTerm)) {
    $filters[] = "(`name` LIKE :search OR `description` LIKE :search)";
    $params['search'] = '%' . $searchTerm . '%';
}

$where = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
$sql = "SELECT * FROM `characters` $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

echo "SQL: " . $sql . "\n\n";
echo "Params: " . print_r($params, true) . "\n\n";
echo "Search term: " . $searchTerm . "\n\n";

// Try to execute
try {
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        echo "Binding :$key => $value\n";
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', 5, PDO::PARAM_INT);
    $stmt->bindValue(':offset', 0, PDO::PARAM_INT);
    
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    echo "\n\nResults: " . count($results) . " found\n";
    foreach ($results as $row) {
        echo "- " . $row['name'] . " (hidden: " . $row['hidden'] . ")\n";
    }
} catch (PDOException $e) {
    echo "\n\nError: " . $e->getMessage();
}
