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

// Check if a vehicle plate is selected
if (isset($_GET['device_plate'])) {
    $device_plate = $_GET['device_plate'];

    // Get the latest GPS coordinates for the selected device plate
    $stmt = $conn->prepare("SELECT latitude, longitude FROM gps_data WHERE device_plate = ? ORDER BY device_plate DESC LIMIT 1");
    $stmt->bind_param("s", $device_plate);
    $stmt->execute();
    $results = $stmt->get_result();

    if ($results->num_rows > 0) {
        $row = $results->fetch_assoc();
        $latitude = floatval($row['latitude']);
        $longitude = floatval($row['longitude']);

        // Reverse Geocoding to get address from Google Maps API
        $apiKey = 'AIzaSyBkygXdlMc23xRCwvXUlRig1-LS1XFRSuU'; // Use your actual API Key
        $geoUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey";

        $response = file_get_contents($geoUrl);
        $geoData = json_decode($response);

        if ($geoData && $geoData->status == "OK") {
            $location = $geoData->results[0]->formatted_address;
        } else {
            $location = "Location not found";
        }

        // Update incident_table with the latest location
        $updateStmt = $conn->prepare("UPDATE incident_table SET location = ? WHERE plate_no = ?");
        $updateStmt->bind_param("ss", $location, $device_plate);
        $updateStmt->execute();
        $updateStmt->close();
    }

    $stmt->close();
}

$conn->close();
?>

<div class="row mt-4 d-flex align-items-stretch">
    <!-- GPS Location Container (Takes up 50% width) -->
    <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-1"></i> GPS Location Viewer
            </div>
            <div class="card-body" id="mapContainer">
                <div id="map" style="height: 100%; width: 100%;"></div>
            </div>
        </div>
    </div>
    
    <!-- Map Details Container (Takes up 25% width) -->
    <div class="col-xl-3 col-lg-3 col-md-12 mb-4">
        <div class="card h-100 shadow-lg rounded">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-info-circle me-1"></i> Location Details
            </div>
            <div class="card-body">
                <h5>Incident Details:</h5>
                <p><strong>Latitude:</strong> <?php echo $latitude; ?></p>
                <p><strong>Longitude:</strong> <?php echo $longitude; ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($location); ?></p>
                <a href="https://maps.google.com?q=<?php echo $latitude; ?>,<?php echo $longitude; ?>" target="_blank">
                    View Location on Google Maps
                </a>
            </div>
        </div>
    </div>
    
    <!-- Pop-in Div (Takes up 25% width) -->
    <div class="col-xl-3 col-lg-3 col-md-12 mb-4">
        <div class="card h-100 popin-card">
            <div class="card-header popin-card-header">
                <i class="fas fa-info-circle me-1"></i> Income Incidents
            </div>
            <div class="card-body popin-card-body">
                
                <div class="device-plates-container">
                    <?php
                    if ($plateResult->num_rows > 0) {
                        while ($plate = $plateResult->fetch_assoc()) {
                            echo "<div class='device-plate-card'>
                                    <a href='?device_plate=" . urlencode($plate['device_plate']) . "' class='device-plate-link'>
                                        " . htmlspecialchars($plate['device_plate']) . "
                                    </a>
                                  </div>";
                        }
                    } else {
                        echo "<p>No vehicles found.</p>";
                    }
                    ?>
                    <?php include 'google.php'; ?>  <!-- Include the incident details here -->
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Google Maps JavaScript -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBkygXdlMc23xRCwvXUlRig1-LS1XFRSuU&callback=initMap" async defer></script>

<script>
    // Initialize the map with GPS coordinates
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
            new google.maps.Marker({
                position: location,
                map: map
            });
        } else {
            document.getElementById('map').innerHTML = "<p>No valid GPS data available to show a map.</p>";
        }
    }
</script>

<style>
    

    /* Add styles for the pop-in div */
.card.popin-card {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 60%;
    right: 10px;
    transform: translateY(-50%); /* Center it vertically */
    z-index: 9999; /* Ensure it stays on top */
    width: 300px; /* Adjust width as per your requirement */
    height: 30vh; /* Set the height to 30% of the viewport height */
    max-height: 40%; /* Optional: To make sure the card doesn't overflow */
    overflow-y: auto; /* Make it scrollable if content overflows */
    transition: all 0.3s ease;
}

/* Styling for the card header */
.card-header.popin-card-header {
    background-color: #343a40;
    color: white;
    font-weight: bold;
    padding: 10px 15px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

/* Styling for the card body */
.card-body.popin-card-body {
    padding: 15px;
    font-size: 14px;
    color: #333;
    height: 100%; /* Allow the card to fill the container */
    overflow-y: auto; /* Enable scroll inside the card if needed */
}

/* Device Plates Container */
.device-plates-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Device Plate Card Styling */
.device-plate-card {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: all 0.3s ease;
}

.device-plate-card:hover {
    background-color: #e9ecef;
    transform: scale(1.05);
}

/* Device Plate Link Styling */
.device-plate-link {
    text-decoration: none;
    font-weight: bold;
    color: #007bff;
    font-size: 16px;
}

.device-plate-link:hover {
    color: #0056b3;
}

</style>