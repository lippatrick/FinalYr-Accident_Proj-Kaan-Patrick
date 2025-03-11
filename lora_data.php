<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header("Content-Type: application/json");

function jsonResponse($success, $message)
{
    echo json_encode(["success" => $success, "message" => $message]);
    exit;
}

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
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        return ['error' => 'cURL Error: ' . $err];
    }
    $res = json_decode($raw, true);
    if ($http_status != 200 || (isset($res['status']) && $res['status'] != '200')) {
        return ['error' => 'SMS send failed', 'details' => $res];
    }
    return ['success' => true];
}

try {
    if (!isset($_POST['device_plate'], $_POST['latitude'], $_POST['longitude'], $_POST['status'])) {
        jsonResponse(false, "Missing required data");
    }

    $device_plate = trim($_POST['device_plate']);
    $latitude     = filter_var($_POST['latitude'], FILTER_VALIDATE_FLOAT);
    $longitude    = filter_var($_POST['longitude'], FILTER_VALIDATE_FLOAT);
    $status       = trim($_POST['status']);

    if ($latitude === false || $longitude === false) {
        jsonResponse(false, "Invalid latitude or longitude");
    }

    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $stmt = $pdo->prepare("INSERT INTO gps_data (device_plate, latitude, longitude, status) VALUES (:device_plate, :latitude, :longitude, :status)");
    $stmt->execute([
        ":device_plate" => $device_plate,
        ":latitude"     => $latitude,
        ":longitude"    => $longitude,
        ":status"       => $status
    ]);
    $incidentId = $pdo->lastInsertId();
    $location = "Lat: $latitude, Lon: $longitude";

    $stmt = $pdo->prepare("SELECT * FROM incident_table WHERE plate_no = :plate_no LIMIT 1");
    $stmt->execute([':plate_no' => $device_plate]);
    $incident = $stmt->fetch();

    if (!$incident) {
        jsonResponse(false, "No incident record found for plate $device_plate");
    }

    $incidentLocation = !empty($incident['location']) ? $incident['location'] : $location;

    $messageEmergency = substr("Emergency alert for Incident ID #$incidentId, priority #$status. Please respond immediately.", 0, 155);
    $messageKin = substr("Person driving car number #$device_plate has been involved in a #$status incident at $incidentLocation. You are their next of kin.", 0, 155);

    $stmt = $pdo->prepare("SELECT DISTINCT contact_number FROM emergency_centers");
    $stmt->execute();
    $emergencyContacts = $stmt->fetchAll();

    foreach ($emergencyContacts as $contact) {
        if (!empty($contact['contact_number'])) {
            $cleanNumber = substr($contact['contact_number'], -9);
            $smsData = [
                'phone'     => '+256' . $cleanNumber,
                'message'   => $messageEmergency,
                'reference' => uniqid()
            ];
            $response = sendSingleSms($smsData);
            if (isset($response['error'])) {
                jsonResponse(false, "Could not send SMS to emergency contact: " . $response['error']);
            }
        }
    }

    if (!empty($incident['kin_phone_no'])) {
        $cleanKinNumber = substr($incident['kin_phone_no'], -9);
        $smsDataKin = [
            'phone'     => '+256' . $cleanKinNumber,
            'message'   => $messageKin,
            'reference' => uniqid()
        ];
        $responseKin = sendSingleSms($smsDataKin);
        if (isset($responseKin['error'])) {
            jsonResponse(false, "Could not send SMS to next of kin: " . $responseKin['error']);
        }
    }

    jsonResponse(true, "Incident reported successfully.");
} catch (PDOException $e) {
    jsonResponse(false, "Database error: " . $e->getMessage());
} catch (Exception $e) {
    jsonResponse(false, "Error: " . $e->getMessage());
}
