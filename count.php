<?php
// db_connection.php
include('incidents.php');

$query = "SELECT COUNT(DISTINCT incident_id) AS client_count FROM incident_table";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$client_count = $row['client_count'];
mysqli_close($conn);
?>

<!-- incident_count.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Count</title>
    <style>
        .count-container {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #eef2f3;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
        }
        .count-container h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        .count-number {
            font-size: 50px;
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="count-container">
        <h2>Active Incident Count</h2>
        <p class="count-number">
            <?php include 'db.php'; echo $client_count; ?>
        </p>
    </div>
</body>
</html>
