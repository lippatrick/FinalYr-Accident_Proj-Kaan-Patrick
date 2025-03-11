<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

// This will be triggered when a new entry is inserted into the gps_data table.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device_id'])) {
    $device_id = intval($_POST['device_id']);
    $device_plate = floatval($_POST['device_plate']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $status = $_POST['status'] ?? 'unknown';

    try {
        // Step 1: Fetch the device_plate from gps_data based on device_id
        $stmt = $conn->prepare("SELECT device_plate FROM gps_data WHERE device_id = ? ORDER BY entry_time DESC LIMIT 1");
        $stmt->execute([$device_id]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device) {
            echo json_encode(['status' => 'error', 'message' => 'Device not found.']);
            exit;
        }

        $device_plate = $device['device_plate'];

        // Step 2: Fetch incident details based on device_plate
        $stmt = $conn->prepare("SELECT plate_no, kin_phone_no FROM incident_table WHERE plate_no = ?");
        $stmt->execute([$device_plate]);
        $incident = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$incident) {
            echo json_encode(['status' => 'error', 'message' => 'Incident not found.']);
            exit;
        }

        $plate_no = $incident['plate_no'];
        $kin_phone_no = $incident['kin_phone_no'];

        // Step 3: Geocode the latitude and longitude into a human-readable address.
        $address = getAddressFromCoordinates($latitude, $longitude);

        // Step 4: Prepare SMS messages with location included
        $messageEmergency = "Emergency alert for car #$plate_no at $address. Status: $status.";
        $messageEmergency = substr($messageEmergency, 0, 155);

        $messageKin = "Person with car #$plate_no was in a $status incident. Location: $address.";
        $messageKin = substr($messageKin, 0, 155);

        // Step 5: Fetch emergency contacts from emergency_centers table
        $stmt = $conn->prepare("SELECT DISTINCT contact_number FROM emergency_centers");
        $stmt->execute();
        $emergencyContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $smsResults = [];

        // Step 6: Send SMS to emergency contacts
        foreach ($emergencyContacts as $contact) {
            $cleanNumber = substr($contact['contact_number'], -9);
            $smsData = [
                'phone' => '+256' . $cleanNumber,
                'message' => $messageEmergency,
                'reference' => uniqid()
            ];
            $smsResults[] = sendSingleSms($smsData);
        }

        // Step 7: Send SMS to next of kin if available
        if (!empty($kin_phone_no)) {
            $cleanKinNumber = substr($kin_phone_no, -9);
            $smsDataKin = [
                'phone' => '+256' . $cleanKinNumber,
                'message' => $messageKin,
                'reference' => uniqid()
            ];
            $smsResults[] = sendSingleSms($smsDataKin);
        }

        echo json_encode(['status' => 'success', 'message' => 'SMS sent successfully.', 'results' => $smsResults]);

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to send SMS.']);
    }
}

// Function to get human-readable address from latitude and longitude using Google Geocoding API
function getAddressFromCoordinates($latitude, $longitude) {
    $apiKey = 'AIzaSyBkygXdlMc23xRCwvXUlRig1-LS1XFRSuU';
    $geoUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$apiKey";

    $response = file_get_contents($geoUrl);
    $data = json_decode($response, true);

    if ($data['status'] == 'OK') {
        return $data['results'][0]['formatted_address'];
    } else {
        return "Location not available";
    }
}

// Function to send SMS using an external API
function sendSingleSms($smsData)
{
    $ch = curl_init(CISSY_COLLECTO_BASE_URL . CISSY_USERNAME . '/sendSingleSMS');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . CISSY_API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $raw = curl_exec($ch);
    curl_close($ch);

    return json_decode($raw, true) ?? ['error' => 'Failed to send SMS'];
}
?>
