<?php
// Include the database connection
include('incidents.php');

// Get POST data (make sure this data is being passed correctly)
$device_plate = $_POST['device_plate']; // The plate number
$priority = $_POST['priority']; // The priority status (e.g., 'high', 'medium', 'low')
$entry_time = $_POST['entry_time']; // The entry time

// Step 1: Insert data into gps_data table
$sql_insert_gps_data = "INSERT INTO gps_data (device_plate, priority, entry_time) 
                        VALUES (:device_plate, :priority, :entry_time)";
$stmt_insert = $conn->prepare($sql_insert_gps_data);
$stmt_insert->bindParam(':device_plate', $device_plate);
$stmt_insert->bindParam(':priority', $priority);
$stmt_insert->bindParam(':entry_time', $entry_time);

// Execute the insert statement
if ($stmt_insert->execute()) {
    // Step 2: Update incident_table based on the device_plate (foreign key)
    $sql_update_incident = "UPDATE incident_table 
                            SET incident_time = :entry_time, 
                                status = :priority 
                            WHERE plate_no = :device_plate";
    
    $stmt_update = $conn->prepare($sql_update_incident);
    $stmt_update->bindParam(':entry_time', $entry_time);
    $stmt_update->bindParam(':priority', $priority);
    $stmt_update->bindParam(':device_plate', $device_plate);
    
    // Execute the update query
    if ($stmt_update->execute()) {
        echo "Incident table updated successfully!";
    } else {
        echo "Error updating the incident table: " . $stmt_update->errorInfo()[2];
    }
} else {
    echo "Error inserting data into gps_data: " . $stmt_insert->errorInfo()[2];
}
?>


<script>
    // Function to fetch updated incident data
function fetchIncidentData() {
    $.ajax({
        url: 'fetch_incidents.php', // PHP file that retrieves incident data
        type: 'GET',
        success: function(response) {
            // Update the incident data in the table body
            $('#incidentData').html(response);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching incident data: " + error);
        }
    });
}

// Refresh the table every 5 seconds (adjust the interval as needed)
setInterval(fetchIncidentData, 5000);

</script>