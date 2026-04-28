<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$settingsFile = '../data/settings.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($data as $key => $value) {
        // Convert booleans to strings for storage
        $valToStore = is_bool($value) ? ($value ? '1' : '0') : $value;
        $stmt->execute([$key, $valToStore, $valToStore]);
    }

    echo json_encode(['success' => true, 'message' => 'Settings saved successfully!']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
