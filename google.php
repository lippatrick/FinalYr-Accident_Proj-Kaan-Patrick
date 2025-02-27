<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'smart_accident';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all distinct device plates from gps_data table
$plateQuery = "SELECT DISTINCT device_plate FROM gps_data";
$plateResult = $conn->query($plateQuery);

$latitude = 0.0;
$longitude = 0.0;
$location = "Not Available";

// This check runs when you load the page and fetch the latest data for each device_plate
if (isset($_GET['device_plate'])) {
    $device_plate = $_GET['device_plate'];

    // Get latest GPS coordinates for the selected device plate
    $stmt = $conn->prepare("SELECT latitude, longitude FROM gps_data WHERE device_plate = ? ORDER BY device_plate DESC LIMIT 1");
    $stmt->bind_param("s", $device_plate);
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        $row = $results->fetch_assoc();
        $latitude = floatval($row['latitude']);
        $longitude = floatval($row['longitude']);

        // Reverse Geocoding to get address from Google Maps API
        $apiKey = 'AIzaSyBkygXdlMc23xRCwvXUlRig1-LS1XFRSuU'; // Add your API Key here
        $geoUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey";

        $response = file_get_contents($geoUrl);
        $geoData = json_decode($response);

        if ($geoData && $geoData->status == "OK") {
            $location = $geoData->results[0]->formatted_address;
        } else {
            $location = "Location not found";
        }

        // Check if plate_no exists in incident_table and update the location
        $updateStmt = $conn->prepare("UPDATE incident_table SET location = ? WHERE plate_no = ?");
        $updateStmt->bind_param("ss", $location, $device_plate);
        $updateStmt->execute();
        $updateStmt->close();

    } else {
        echo "No GPS data found for device_plate: $device_plate";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS Incident Location</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBkygXdlMc23xRCwvXUlRig1-LS1XFRSuU&callback=initMap" async defer></script>

    
</head>
<body>

</body>
</html>
