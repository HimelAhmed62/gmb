<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$settingsFile = '../data/settings.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $dailyLimit = $data['daily_limit'] ?? '500';
    $delay = $data['outreach_delay'] ?? '15';
    $manualScript = $data['manual_audit_script'] ?? '';

    $_SESSION['daily_limit'] = $dailyLimit;
    $_SESSION['outreach_delay'] = $delay;
    $_SESSION['manual_audit_script'] = $manualScript;

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute(['daily_limit', $dailyLimit, $dailyLimit]);
    $stmt->execute(['outreach_delay', $delay, $delay]);
    $stmt->execute(['manual_audit_script', $manualScript, $manualScript]);

    foreach ($data as $key => $value) {
        // Convert booleans to strings for storage
        $valToStore = is_bool($value) ? ($value ? '1' : '0') : $value;
        $stmt->execute([$key, $valToStore, $valToStore]);
    }

    echo json_encode(['success' => true, 'message' => 'Settings saved successfully!']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
