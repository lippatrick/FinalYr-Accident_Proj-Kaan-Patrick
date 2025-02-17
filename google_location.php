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

    <script>
        function initMap() {
            var location = {
                lat: <?php echo $latitude ?: 0; ?>, 
                lng: <?php echo $longitude ?: 0; ?>
            };

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: location
            });

            if (location.lat !== 0 && location.lng !== 0) {
                var marker = new google.maps.Marker({
                    position: location,
                    map: map
                });
            } else {
                document.getElementById('map').innerHTML = "<p>No valid GPS data available to show a map.</p>";
            }
        }
    </script>
</head>
<body>
    <h4>Select a Vehicle by Plate:</h4>
    <ul>
        <?php
        if ($plateResult->num_rows > 0) {
            while ($plate = $plateResult->fetch_assoc()) {
                echo "<li><a href='?device_plate=" . urlencode($plate['device_plate']) . "'>Plate: " . htmlspecialchars($plate['device_plate']) . "</a></li>";
            }
        } else {
            echo "<li>No vehicles found.</li>";
        }
        ?>
    </ul>

    <h4>Incident Details:</h4>
    <p><strong>Latitude:</strong> <?php echo $latitude; ?></p>
    <p><strong>Longitude:</strong> <?php echo $longitude; ?></p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($location); ?></p>

    <a href="https://maps.google.com?q=<?php echo $latitude; ?>,<?php echo $longitude; ?>" target="_blank">View Location on Google Maps</a>

    <div id="map" style="height: 500px; width: 100%;"></div>

</body>
</html>
