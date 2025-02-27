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

// Get the device_plate from the URL parameter
if (isset($_GET['device_plate'])) {
    $device_plate = $_GET['device_plate'];

    // Fetch the relevant details (priority, entry_time) from the gps_data table for the device_plate
    $stmt = $conn->prepare("SELECT priority, entry_time, latitude, longitude FROM gps_data WHERE device_plate = ? ORDER BY entry_time DESC LIMIT 1");
    $stmt->bind_param("s", $device_plate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $gps_data = $result->fetch_assoc();
        $priority = $gps_data['priority'];
        $entry_time = $gps_data['entry_time'];
        $latitude = $gps_data['latitude'];
        $longitude = $gps_data['longitude'];

        // Reverse Geocoding using Google Maps API to get location address
        $apiKey = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with your API key
        $geoUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey";
        $response = file_get_contents($geoUrl);
        $geoData = json_decode($response);

        if ($geoData && $geoData->status == "OK") {
            $location = $geoData->results[0]->formatted_address;
        } else {
            $location = "Location not found";
        }

        // Fetch kin_phone_no from the incident_table
        $incidentStmt = $conn->prepare("SELECT kin_phone_no FROM incident_table WHERE plate_no = ?");
        $incidentStmt->bind_param("s", $device_plate);
        $incidentStmt->execute();
        $incidentResult = $incidentStmt->get_result();

        if ($incidentResult->num_rows > 0) {
            $incident = $incidentResult->fetch_assoc();
            $kin_phone_no = $incident['kin_phone_no'];

            // 1. Send SMS alert to kin_phone_no (Emergency Contact from incident_table)
            sendSmsAlert($kin_phone_no, $device_plate, $priority, $entry_time, $location);

            // 2. Send SMS to all emergency contacts from the emergency_centers table
            $emergencyStmt = $conn->prepare("SELECT contact_number FROM emergency_centers");
            $emergencyStmt->execute();
            $emergencyResult = $emergencyStmt->get_result();

            while ($row = $emergencyResult->fetch_assoc()) {
                // Send SMS alert to each contact number in the emergency_centers table
                sendSmsAlert($row['contact_number'], $device_plate, $priority, $entry_time, $location);
            }
        } else {
            echo "No emergency contact found for this device plate.";
        }
    } else {
        echo "No GPS data found for this device plate.";
    }

    $stmt->close();
    $incidentStmt->close();
    $emergencyStmt->close();
}

$conn->close();

// Function to send SMS using Infobip API (with API key authentication)
function sendSmsAlert($phoneNumber, $devicePlate, $priority, $entryTime, $location) {
    // Set your Infobip API credentials here
    $apiUrl = 'https://kqqyyx.api.infobip.com';  // Updated URL
    $apiKey = 'your_infobip_api_key';  // Replace with your Infobip API key

    // Prepare the SMS message
    $message = "URGENT: Vehicle Plate: $devicePlate\nPriority: $priority\nTime: $entryTime\nLocation: $location";

    // Prepare the request data
    $data = [
        "messages" => [
            [
                "from" => "AccidentAlert", // You can change this to your sender ID
                "to" => $phoneNumber,
                "text" => $message
            ]
        ]
    ];

    // Initialize the cURL session
    $ch = curl_init($apiUrl . "/sms/2/text/advanced");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: App $apiKey",  // Use 'App' followed by your API key for authentication
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the cURL request
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    } else {
        // Handle the response (logging or additional actions can be added here)
        echo "SMS sent to $phoneNumber successfully!";
    }

    // Close the cURL session
    curl_close($ch);
}
?>
