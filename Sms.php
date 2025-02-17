<?php
// Include database connection
include 'db.php';

// Fetch incident_id from POST data
$incidentId = $_POST['incident_id'];

// Fetch incident data
$sql = "SELECT i.plate_no, i.vehicle, i.location, i.status, i.kin_phone_no, e.contact_numbers 
        FROM incident_table i 
        JOIN emergency_centers e ON e.district = i.location 
        WHERE i.incident_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $incidentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Prepare message for SMS
$message = "Incident Report\n";
$message .= "Plate No: " . $row['plate_no'] . "\n";
$message .= "Vehicle: " . $row['vehicle'] . "\n";
$message .= "Location: " . $row['location'] . "\n";
$message .= "Status: " . ucfirst($row['status']) . "\n";

// Combine kin_phone_no and emergency contacts
$phoneNumbers = $row['kin_phone_no'] . ',' . $row['contact_numbers']; // Comma-separated phone numbers

// Send SMS to kin and emergency contacts using an SMS API
// Example using Infobip API (Twilio or any SMS provider can be used similarly)
$apiUrl = 'https://api.infobip.com/sms/1/text/single';
$apiKey = 'YOUR_INFOBIP_API_KEY';
$sender = 'YOUR_SENDER_ID';

// Prepare SMS request
$data = [
    'from' => $sender,
    'to' => $phoneNumbers,
    'text' => $message,
];

$options = [
    'http' => [
        'header'  => "Authorization: Basic " . base64_encode("apikey:$apiKey"),
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];
$context  = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);

// Check response
if ($response) {
    echo "SMS Sent!";
} else {
    echo "Failed to send SMS.";
}
?>
