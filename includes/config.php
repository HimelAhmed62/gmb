<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

// Load global settings and connection statuses from Database
try {
    $dbSettings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Sync to session for easy access in UI
    foreach ($dbSettings as $key => $val) {
        // Special handling for boolean-like connection flags
        if (strpos($key, '_connected') !== false) {
            $_SESSION[$key] = (bool)$val;
        } else {
            $_SESSION[$key] = $val;
        }
    }

    // Ensure defaults if keys are missing
    $defaults = [
        'gmail_connected' => false,
        'whatsapp_connected' => false,
        'gemini_connected' => false,
        'chatgpt_connected' => false,
        'engine_gemini' => true,
        'engine_chatgpt' => true,
        'engine_manual' => true,
        'dark_theme' => false
    ];
    foreach ($defaults as $key => $val) {
        if (!isset($_SESSION[$key])) $_SESSION[$key] = $val;
    }

} catch (Exception $e) {
    // If DB fails, use offline defaults
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
