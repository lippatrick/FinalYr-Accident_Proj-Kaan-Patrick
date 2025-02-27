<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "smart_accident";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch latest GPS data
$sql = "SELECT * FROM gps_data ORDER BY entry_time DESC LIMIT 10";
$result = $conn->query($sql);

$gps_data = [];
while ($row = $result->fetch_assoc()) {
    $gps_data[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live GPS Accident Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: flex-end;
        }
        #notification-container {
            width: 100%;
            max-width: 350px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        #notification-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
        }
        #notification-box {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
        }
        .notification {
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 5px;
            transition: background 0.3s;
            cursor: pointer;
        }
        .notification:hover { background-color: #eef4ff; }
        .notification:last-child { border-bottom: none; }
        .notification p {
            margin: 0;
            font-size: 0.9em;
            color: #333;
        }
        .notification small { font-size: 0.8em; color: #888; }
        .new-notification {
            animation: blinkBackground 1s infinite;
        }
        @keyframes blinkBackground {
            0% { background-color: #ffff99; }
            50% { background-color: #f9f9f9; }
            100% { background-color: #ffff99; }
        }
    </style>
</head>
<body>

    <div id="notification-container">
        <div id="notification-header">Live GPS Accident Notifications</div>
        <div id="notification-box">
            <?php foreach ($gps_data as $index => $data): ?>
                <div class="notification <?= $index === 0 ? 'new-notification' : ''; ?>" onclick="handleNotificationClick(<?= $data['incident_id']; ?>)">
                    <p><strong>Device: <?= htmlspecialchars($data['device_plate']); ?></strong></p>
                    <p>Location: <?= htmlspecialchars($data['latitude']) . ', ' . htmlspecialchars($data['longitude']); ?></p>
                    <p>Priority: <?= htmlspecialchars($data['priority']); ?></p>
                    <small><?= date('D, h:i:s A', strtotime($data['entry_time'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        async function fetchNotifications() {
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newNotifications = newDoc.getElementById('notification-box').innerHTML;
                document.getElementById('notification-box').innerHTML = newNotifications;
            } catch (error) {
                console.error('Error fetching notifications:', error.message);
            }
        }

        setInterval(fetchNotifications, 3000); // Refresh every 3 seconds

        function handleNotificationClick(incidentId) {
            // You can replace this with the desired action, e.g., open a detailed view
            alert("Notification clicked! Incident ID: " + incidentId);

            // Example: Redirect to a page with more details about the incident
            // window.location.href = "/incident-details.php?id=" + incidentId;
        }
    </script>

</body>
</html>
