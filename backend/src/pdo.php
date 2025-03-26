<?php

require_once BASE_DIR . 'config.php';
$dbname = 'task';

try {
    // Create PDO instance with dynamic values
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    // Create the PDO object
    $pdo = new PDO($dsn, $username, $password);
    // Set the error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
