<?php
// Set up database connection
$servername = "localhost"; // Database host
$username = "root";        // Database username
$password = "";            // Database password (empty for XAMPP by default)
$dbname = "smart_accident"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if POST data exists
if (isset($_POST['device_plate'], $_POST['latitude'], $_POST['longitude'], $_POST['status'])) {

    // Get the data from the POST request
    $device_plate = $conn->real_escape_string($_POST['device_plate']);
    $latitude = $conn->real_escape_string($_POST['latitude']);
    $longitude = $conn->real_escape_string($_POST['longitude']);
    $status = $conn->real_escape_string($_POST['status']);

    // Prepare and bind SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO gps_data (device_plate, latitude, longitude, status) VALUES (?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing the SQL statement: " . $conn->error);
    }

    $stmt->bind_param("ssss", $device_plate, $latitude, $longitude, $status);

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error executing SQL: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error: Missing required data in POST request";
}

// Close connection
$conn->close();
?>
