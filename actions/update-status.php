<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? '';
    $newStatus = $data['status'] ?? '';
    
    if (!$id || !$newStatus) {
        echo json_encode(['success' => false, 'message' => 'Missing ID or status']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
    if ($stmt->execute([$newStatus, $id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update lead status']);
    }
    exit;
}
