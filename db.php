<?php
$host = 'localhost';
$db = 'smart_accident';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<?php
// $host = 'localhost';
// $db = 'smart_accident';
// $user = 'root';
// $password = '';

// // Create connection
// $conn = new mysqli($host, $user, $password, $db);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// echo "Connected successfully";
?>
