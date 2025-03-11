<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['incident_id'])) {
    $incident_id = intval($_POST['incident_id']);

    try {
        $stmt = $conn->prepare("SELECT * FROM incident_table WHERE incident_id = ?");
        $stmt->execute([$incident_id]);
        $incident = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$incident) {
            echo json_encode(['status' => 'error', 'message' => 'Incident not found.']);
            exit;
        }

        $plate_no = $incident['plate_no'];
        $location = $incident['location'];
        $status = $incident['status'];
        $kin_phone_no = $incident['kin_phone_no'];

        $messageEmergency = "Emergency alert for car #$plate_no at $location. Status: $status.";
        $messageEmergency = substr($messageEmergency, 0, 155);

        $messageKin = "Person with car #$plate_no was in a $status incident at $location.";
        $messageKin = substr($messageKin, 0, 155);

        $stmt = $conn->prepare("SELECT DISTINCT contact_number FROM emergency_centers");
        $stmt->execute();
        $emergencyContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $smsResults = [];

        foreach ($emergencyContacts as $contact) {
            $cleanNumber = substr($contact['contact_number'], -9);
            $smsData = [
                'phone' => '+256' . $cleanNumber,
                'message' => $messageEmergency,
                'reference' => uniqid()
            ];
            $smsResults[] = sendSingleSms($smsData);
        }

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
