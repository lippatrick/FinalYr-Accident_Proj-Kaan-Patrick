<?php
require_once __DIR__ . '/db.php';

try {
    // Fetch incidents
    $stmt = $conn->prepare("SELECT incident_id, plate_no, vehicle, owner, location, status FROM incident_table");
    $stmt->execute();
    $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident List</title>
</head>
<body>

    <h1>Incident List</h1>

    <table border="1">
        <thead>
            <tr>
                <th>Incident ID</th>
                <th>Plate No</th>
                <th>Vehicle</th>
                <th>Location</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($incidents as $incident): ?>
                <tr>
                    <td><?= htmlspecialchars($incident['incident_id']) ?></td>
                    <td><?= htmlspecialchars($incident['plate_no']) ?></td>
                    <td><?= htmlspecialchars($incident['vehicle']) ?></td>
                    <td><?= htmlspecialchars($incident['location']) ?></td>
                    <td><?= htmlspecialchars($incident['status']) ?></td>
                    <td>
                        <button onclick="sendSms('<?= $incident['incident_id'] ?>')">Send SMS</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function sendSms(incident_id) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'smsSend.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert(xhr.responseText); // SMS sent confirmation
                }
            };

            xhr.send('incident_id=' + incident_id);
        }
    </script>

</body>
</html>
