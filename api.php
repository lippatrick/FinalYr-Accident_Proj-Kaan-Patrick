<?php
// api.php

header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "smart_accident";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get incident_id from the request
$incident_id = isset($_GET['incident_id']) ? intval($_GET['incident_id']) : 0;

if ($incident_id > 0) {
    $sql = "SELECT latitude, longitude FROM gps_data WHERE incident_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $incident_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No data found for the given incident ID']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid incident ID']);
}

$conn->close();
?>
