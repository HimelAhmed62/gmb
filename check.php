<?php
require_once 'includes/config.php';

header('Content-Type: text/plain');
echo "=== AuditAI Network Diagnostics ===\n\n";

function test_url($name, $url) {
    echo "Testing $name ($url)...\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    
    $start = microtime(true);
    $response = curl_exec($ch);
    $time = round(microtime(true) - $start, 2);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
    
    if ($response !== false) {
        echo "✅ SUCCESS: Received HTTP $httpCode in {$time}s\n\n";
    } else {
        echo "❌ FAILED: Code $errno - $error\n\n";
    }
}

echo "Server IP: " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "cURL Version: " . (function_exists('curl_version') ? curl_version()['version'] : 'Not Installed') . "\n\n";

test_url("Google (Basic)", "https://www.google.com");
test_url("OpenAI API", "https://api.openai.com/v1/models");
test_url("Gemini API", "https://generativelanguage.googleapis.com/v1/models");

echo "=== Diagnostics Complete ===";
?>
