<?php
// Database Connection Configuration
// Railway provides connection details via environment variables

$db_host = getenv('MYSQLHOST');
$db_user = getenv('MYSQLUSER');
$db_pass = getenv('MYSQLPASSWORD');
$db_name = getenv('MYSQLDATABASE');
$db_port = getenv('MYSQLPORT') ?: '3306';

// Fallback for local development
if (!$db_host) {
    $db_host = '127.0.0.1';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'gmb_audit';
}

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // If connection fails, we can handle it gracefully
    die("Database Connection Failed: " . $e->getMessage());
}
?>
