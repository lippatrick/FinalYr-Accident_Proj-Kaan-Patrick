<?php
// Set up database connection
$servername = "localhost"; // Database host
$username = "root";        // Database username
$password = "";            // Database password
$dbname = "smart_accident"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if POST data exists
if (isset($_POST['userID'], $_POST['latitude'], $_POST['longitude'], $_POST['severity'])) {

    // Get the data from the POST request
    $userID = $conn->real_escape_string($_POST['userID']);
    $latitude = $conn->real_escape_string($_POST['latitude']);
    $longitude = $conn->real_escape_string($_POST['longitude']);
    $severity = $conn->real_escape_string($_POST['severity']);

    // Prepare and bind SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO accident_table (userID, latitude, longitude, severity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $userID, $latitude, $longitude, $severity);

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error: Missing required data in POST request";
}

// Close connection
$conn->close();
?>
