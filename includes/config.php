<?php
session_start();

// Load global settings
$settingsFile = __DIR__ . '/../data/settings.json';
if (file_exists($settingsFile)) {
    $_SESSION['settings'] = json_decode(file_get_contents($settingsFile), true) ?? [];
} else {
    $_SESSION['settings'] = [
        'daily_limit' => 500,
        'delay_between_messages' => 45,
        'email_notifications' => true,
        'whatsapp_notifications' => true,
        'browser_notifications' => true,
        'low_credit_alert' => true
    ];
}

// Initialize default connection statuses if not set
if (!isset($_SESSION['gmail_connected'])) {
    $_SESSION['gmail_connected'] = true; // Default state
}
if (!isset($_SESSION['whatsapp_connected'])) {
    $_SESSION['whatsapp_connected'] = true; // Default state
}
if (!isset($_SESSION['gemini_connected'])) {
    $_SESSION['gemini_connected'] = false; // Default state
}

// Flash message handling
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}
?>
