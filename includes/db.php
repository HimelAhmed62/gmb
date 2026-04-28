<?php
// Database Connection Configuration
// Railway provides connection details via environment variables

$db_host = getenv('MYSQLHOST');
$db_user = getenv('MYSQLUSER');
$db_pass = getenv('MYSQLPASSWORD');
$db_name = getenv('MYSQLDATABASE');
$db_port = getenv('MYSQLPORT') ?: '3306';

if ($db_host) {
    // Live Server (Railway)
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
} else {
    // Local Server (XAMPP)
    $db_host = '127.0.0.1';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'gmb_audit';
    $dsn = "mysql:host=$db_host;port=3306;dbname=$db_name;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    if (!$db_host && getenv('MYSQLHOST')) {
        die("Database Connection Failed: Railway variables are set but the app cannot reach the DB. Please check 'Public Networking' in MySQL settings.");
    }
    die("Database Connection Failed: " . $e->getMessage());
}
?>
