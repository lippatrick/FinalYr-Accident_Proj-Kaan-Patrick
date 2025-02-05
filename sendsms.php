<?php

include('db.php');

$input = json_decode(file_get_contents('php://input'), true);
$incidentId = $input['incident_id'] ?? null;
$priority = $input['priority'] ?? 'unspecified';

if ($incidentId) {
    $send_sql = "SELECT contact_number FROM emergency_centers";
    $sendresult = $conn->query($send_sql);

    if ($sendresult->num_rows > 0) {
        $apiUrl = "https://kqqyyx.api.infobip.com/sms/2/text/advanced";
        $apiKey = "1e0150591760e6a4b9972a3bcf690179-1a54b1b1-bf88-437a-881e-5ab6437c60d7";

        $message = "Emergency Alert for Incident ID $incidentId with priority '$priority'. Please respond immediately.";

        while ($row = $sendresult->fetch_assoc()) {
            $phoneNumber = $row['contact_number'];

            $postData = json_encode([
                "messages" => [[
                    "from" => "AccidentAlert",
                    "to" => $phoneNumber,
                    "text" => $message
                ]]
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: App $apiKey",
                "Content-Type: application/json"
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                echo "SMS successfully sent for Incident ID $incidentId with priority '$priority'.";
            } else {
                echo "Failed to send SMS for Incident ID $incidentId. Response: $response";
            }
        }
    } else {
        echo "No contact numbers found.";
    }
} else {
    echo "No incident ID provided.";
}

$conn->close();
?>
