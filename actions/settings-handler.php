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

    // Load existing settings to merge if necessary, or just overwrite
    $existingSettings = [];
    if (file_exists($settingsFile)) {
        $existingSettings = json_decode(file_get_contents($settingsFile), true) ?? [];
    }

    $updatedSettings = array_merge($existingSettings, $data);
    
    if (file_put_contents($settingsFile, json_encode($updatedSettings, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Settings saved successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save settings. Check permissions.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
