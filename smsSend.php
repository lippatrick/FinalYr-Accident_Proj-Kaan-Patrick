<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

try {
    // Fetch incidents
    $stmt = $conn->prepare("SELECT incident_id, plate_no, vehicle, owner, phone_no, kin_phone_no, location, status FROM incident_table");
    $stmt->execute();
    $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch emergency contacts
    $stmt = $conn->prepare("SELECT DISTINCT contact_number FROM emergency_centers");
    $stmt->execute();
    $emergencyContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are incidents or contacts to process
    if (empty($incidents) || empty($emergencyContacts)) {
        echo "No incidents or contacts to process.";
        exit;
    }

    // Loop through incidents and send SMS
    foreach ($incidents as $incident) {
        $incident_id = $incident['incident_id'];
        $plate_no = $incident['plate_no'];
        $vehicle = $incident['vehicle'];
        $owner = $incident['owner'];
        $phone_no = $incident['phone_no'];
        $kin_phone_no = $incident['kin_phone_no'];
        $location = $incident['location'];
        $status = $incident['status'];

        // Prepare emergency message
        $messageEmergency = "Emergency alert for Incident ID #$incident_id, priority #$status. Please respond immediately.";
        $messageKin = "Person driving car number #$plate_no has been involved in a #$status incident at #$location. You are their next of kin.";

        // Truncate message to 155 characters
        $messageEmergency = substr($messageEmergency, 0, 155);
        $messageKin = substr($messageKin, 0, 155);

        // Send SMS to emergency contacts
        foreach ($emergencyContacts as $contact) {
            if (!empty($contact['contact_number'])) {
                $cleanNumber = substr($contact['contact_number'], -9);
                $smsData = [
                    'phone'     => '+256' . $cleanNumber,
                    'message'   => $messageEmergency,
                    'reference' => uniqid()
                ];

                // Debug: Log phone number being sent to
                error_log("Sending SMS to emergency contact: +256" . $cleanNumber);

                // Send the SMS and check for response
                $response = sendSingleSms($smsData);
                if (isset($response['error'])) {
                    // Log the error if sending fails
                    error_log("Error sending SMS to emergency contact: " . $response['error']);
                    exit("Could not send SMS to emergency contact: " . $response['error']);
                }
            }
        }

        // Send SMS to the next of kin if provided
        if (!empty($kin_phone_no)) {
            $cleanKinNumber = substr($kin_phone_no, -9);
            $smsDataKin = [
                'phone'     => '+256' . $cleanKinNumber,
                'message'   => $messageKin,
                'reference' => uniqid()
            ];

            // Debug: Log phone number being sent to
            error_log("Sending SMS to next of kin: +256" . $cleanKinNumber);

            // Send the SMS and check for response
            $responseKin = sendSingleSms($smsDataKin);
            if (isset($responseKin['error'])) {
                // Log the error if sending fails
                error_log("Error sending SMS to next of kin: " . $responseKin['error']);
                exit("Could not send SMS to next of kin: " . $responseKin['error']);
            }
        }
    }

    echo "SMS notifications sent successfully.";

} catch (PDOException $e) {
    // Log database errors
    error_log("Database error: " . $e->getMessage());
    echo "Database error: " . $e->getMessage();
}

// Function to send SMS using the API
function sendSingleSms($smsData)
{
    // API URL for sending SMS via Infobip
    $apiUrl = 'https://kqqyyx.api.infobip.com/sms/2/text/advanced'; // Replace 'kqqyyx' with your Infobip account base URL

    // Initialize cURL session for sending SMS
    $ch = curl_init($apiUrl);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . LIPO_KEY,  // Make sure LIPO_KEY contains your actual Infobip API key
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $raw = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($err) {
        error_log("cURL Error: $err");
        return ['error' => 'cURL Error: ' . $err];
    }

    // Log the raw response and status code for debugging
    error_log("Raw Response: $raw");
    error_log("HTTP Status: $http_status");

    // Check if the response is valid
    $res = json_decode($raw, true);
    if ($http_status != 200 || (isset($res['status']) && $res['status'] != '200')) {
        // Log the API response if the status is not OK
        error_log("SMS send failed with status code $http_status: " . print_r($res, true));
        return ['error' => 'SMS send failed', 'details' => $res];
    }

    // Return success if the SMS was sent successfully
    return ['success' => true];
}
