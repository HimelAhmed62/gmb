<?php
require_once 'includes/config.php';
try {
    $stmt = $pdo->query("DESCRIBE leads");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['columns' => $columns]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
