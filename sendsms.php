<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate_no     = $_POST['plate_no']     ?? '';
    $vehicle      = $_POST['vehicle']      ?? '';
    $owner        = $_POST['owner']        ?? '';
    $phone_no     = $_POST['phone_no']     ?? '';
    $kin_phone_no = $_POST['kin_phone_no'] ?? '';
    $location     = $_POST['location']     ?? '';
    $status       = $_POST['status']       ?? null;
    if (!preg_match('/^256\d{9}$/', $phone_no)) {
        exit("Invalid phone number format. Must be 256 followed by 9 digits.");
    }
    if (!empty($kin_phone_no) && !preg_match('/^256\d{9}$/', $kin_phone_no)) {
        exit("Invalid Next of Kin phone number format. Must be 256 followed by 9 digits.");
    }
    try {
        $stmt = $conn->prepare("INSERT INTO incident_table (plate_no, vehicle, owner, phone_no, kin_phone_no, location, incident_time, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([$plate_no, $vehicle, $owner, $phone_no, $kin_phone_no, $location, $status]);
        $incidentId = $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT DISTINCT contact_number FROM emergency_centers");
        $stmt->execute();
        $emergencyContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $messageEmergency = "Emergency alert for Incident ID #$incidentId, priority #$status. Please respond immediately.";
        $messageKin = "Person driving car number #$plate_no has been involved in a #$status incident at #$location. You are their next of kin.";
        $messageEmergency = substr($messageEmergency, 0, 155);
        $messageKin = substr($messageKin, 0, 155);
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
                    exit("Could not send SMS to emergency contact: " . $response['error']);
                }
            }
        }
        if (!empty($kin_phone_no)) {
            $cleanKinNumber = substr($kin_phone_no, -9);
            $smsDataKin = [
                'phone'     => '+256' . $cleanKinNumber,
                'message'   => $messageKin,
                'reference' => uniqid()
            ];
            $responseKin = sendSingleSms($smsDataKin);
            if (isset($responseKin['error'])) {
                exit("Could not send SMS to next of kin: " . $responseKin['error']);
            }
        }
        echo "Incident reported successfully.";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
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
