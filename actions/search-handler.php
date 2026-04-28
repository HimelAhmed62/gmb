<?php
header('Content-Type: application/json');

$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

$options = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'icon' => 'layout-dashboard', 'category' => 'Navigation'],
    ['title' => 'Leads Table', 'url' => 'leads.php', 'icon' => 'users', 'category' => 'Data'],
    ['title' => 'Upload Leads', 'url' => 'upload.php', 'icon' => 'upload-cloud', 'category' => 'Data'],
    ['title' => 'API Settings', 'url' => 'settings.php', 'icon' => 'key', 'category' => 'Settings'],
    ['title' => 'Platform Limits', 'url' => 'settings.php?tab=limits', 'icon' => 'settings', 'category' => 'Settings'],
    ['title' => 'Notifications', 'url' => 'settings.php?tab=notifications', 'icon' => 'bell', 'category' => 'Settings'],
    ['title' => 'System Logs', 'url' => 'logs.php', 'icon' => 'file-text', 'category' => 'System'],
    ['title' => 'Connect Gmail', 'url' => 'api-gmail.php', 'icon' => 'mail', 'category' => 'Integrations'],
    ['title' => 'WhatsApp API', 'url' => 'api-whatsapp.php', 'icon' => 'message-circle', 'category' => 'Integrations'],
    ['title' => 'Gemini AI API', 'url' => 'api-gemini.php', 'icon' => 'sparkles', 'category' => 'Integrations'],
];

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$results = array_filter($options, function($opt) use ($query) {
    return strpos(strtolower($opt['title']), $query) !== false || 
           strpos(strtolower($opt['category']), $query) !== false;
});

echo json_encode(array_values($results));
