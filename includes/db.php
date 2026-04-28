<?php
// Database Connection Configuration
// Railway provides connection details via environment variables

$db_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');

if ($db_url) {
    // Railway URL based connection
    $url = parse_url($db_url);
    $db_host = $url['host'];
    $db_user = $url['user'];
    $db_pass = $url['pass'];
    $db_name = substr($url['path'], 1);
    $db_port = $url['port'] ?? '3306';
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
} else {
    // Individual variables or Local fallback
    $db_host = getenv('MYSQLHOST') ?: '127.0.0.1';
    $db_user = getenv('MYSQLUSER') ?: 'root';
    $db_pass = getenv('MYSQLPASSWORD') ?: '';
    $db_name = getenv('MYSQLDATABASE') ?: 'gmb_audit';
    $db_port = getenv('MYSQLPORT') ?: '3306';
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed. Error: " . $e->getMessage() . " (Host: $db_host, Port: $db_port)");
}
?>
