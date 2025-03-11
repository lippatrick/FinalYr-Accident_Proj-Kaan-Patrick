<?php

// Define database constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'smart_accident');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);

    // Set time zone
    $conn->exec("SET time_zone = '+03:00'");
} catch (PDOException $e) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $e->getMessage()]));
}
