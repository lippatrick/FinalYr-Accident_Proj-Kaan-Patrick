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

// Fetch all device IDs from gps_data table
$deviceQuery = "SELECT DISTINCT device_id FROM gps_data";
$deviceResult = $conn->query($deviceQuery);

$latitude = 0.0;
$longitude = 0.0;

if (isset($_GET['device_id'])) {
    $device_id = intval($_GET['device_id']);
    // Prepared statement for fetching GPS coordinates
    $stmt = $conn->prepare("SELECT latitude, longitude FROM gps_data WHERE device_id = ? ORDER BY device_id DESC LIMIT 1");
    $stmt->bind_param("i", $device_id);
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        $row = $results->fetch_assoc();
        $latitude = floatval($row['latitude']);
        $longitude = floatval($row['longitude']);
    } else {
        echo "No GPS data found for device_id: $device_id";
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
    <title>GPS Location Viewer</title>
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
    <h4>Select a Device:</h4>
    <ul>
        <?php
        if ($deviceResult->num_rows > 0) {
            while ($device = $deviceResult->fetch_assoc()) {
                echo "<li><a href='?device_id=" . $device['device_id'] . "'>Device ID: " . $device['device_id'] . "</a></li>";
            }
        } else {
            echo "<li>No devices found.</li>";
        }
        ?>
    </ul>

    <p>Latitude: <?php echo $latitude; ?></p>
    <p>Longitude: <?php echo $longitude; ?></p>

    <a href="https://maps.google.com?q=<?php echo $latitude; ?>,<?php echo $longitude; ?>" target="_blank">View Location on Google Maps</a>

    <div id="map" style="height: 500px; width: 100%;"></div>
</body>
</html>
