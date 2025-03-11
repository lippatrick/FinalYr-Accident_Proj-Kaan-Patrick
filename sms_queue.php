<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

// Log start time for debugging
file_put_contents('sms_log.txt', date('Y-m-d H:i:s') . " - Starting SMS queue processing...\n", FILE_APPEND);

// Step 1: Fetch all pending SMS requests from sms_queue
$stmt = $conn->prepare("SELECT * FROM sms_queue WHERE status = 'pending'");
$stmt->execute();
$smsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($smsRequests as $sms) {
    $device_plate = $sms['device_plate'];
    $latitude = $sms['latitude'];
    $longitude = $sms['longitude'];
    $status = $sms['status'];
    $plate_no = $sms['plate_no'];
    $kin_phone_no = $sms['kin_phone_no'];

    // Step 2: Geocode coordinates to address
    $address = getAddressFromCoordinates($latitude, $longitude);

    // Step 3: Prepare SMS messages
    $messageEmergency = "Emergency alert for car #$plate_no at $address. Status: $status.";
    $messageKin = "Person with car #$plate_no was in a $status incident. Location: $address.";

    // Step 4: Send SMS to emergency contacts
    $stmt = $conn->prepare("SELECT DISTINCT contact_number FROM emergency_centers");
    $stmt->execute();
    $emergencyContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($emergencyContacts as $contact) {
        $cleanNumber = substr($contact['contact_number'], -9);
        $smsData = [
            'phone' => '+256' . $cleanNumber,
            'message' => $messageEmergency,
            'reference' => uniqid()
        ];
        $smsResult = sendSingleSms($smsData);
        file_put_contents('sms_log.txt', "Sent to Emergency: {$smsData['phone']} - {$smsResult['status']}\n", FILE_APPEND);
    }

    // Step 5: Send SMS to next of kin
    if (!empty($kin_phone_no)) {
        $cleanKinNumber = substr($kin_phone_no, -9);
        $smsDataKin = [
            'phone' => '+256' . $cleanKinNumber,
            'message' => $messageKin,
            'reference' => uniqid()
        ];
        $smsResultKin = sendSingleSms($smsDataKin);
        file_put_contents('sms_log.txt', "Sent to Next of Kin: {$smsDataKin['phone']} - {$smsResultKin['status']}\n", FILE_APPEND);
    }

    // Step 6: Mark as sent
    $updateStmt = $conn->prepare("UPDATE sms_queue SET status = 'sent' WHERE id = ?");
    $updateStmt->execute([$sms['id']]);
    file_put_contents('sms_log.txt', "Marked SMS ID {$sms['id']} as sent.\n", FILE_APPEND);
}

// Function to get address from Google Maps API
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

// Function to send SMS using external API
function sendSingleSms($smsData) {
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

    return json_decode($raw, true) ?? ['status' => 'failed'];
}

file_put_contents('sms_log.txt', date('Y-m-d H:i:s') . " - Finished processing.\n\n", FILE_APPEND);
?>
