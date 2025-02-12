<?php
$host = 'localhost';
$db = 'smart_accident';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    $conn->exec("SET time_zone = '+03:00'");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
