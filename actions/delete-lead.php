<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$leadsFile = '../data/leads.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idsToDelete = $data['ids'] ?? [];

    if (empty($idsToDelete)) {
        echo json_encode(['success' => false, 'message' => 'No leads selected for deletion.']);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
    $stmt = $pdo->prepare("DELETE FROM leads WHERE id IN ($placeholders)");
    
    if ($stmt->execute($idsToDelete)) {
        echo json_encode([
            'success' => true, 
            'message' => count($idsToDelete) . ' leads deleted successfully.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete leads from database.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
