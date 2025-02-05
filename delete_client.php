<?php
// Include database connection file
include('incidents.php'); 

// Check if 'id' parameter exists in the query string
if (isset($_GET['id'])) {
    // Sanitize the input
    $incident_id = intval($_GET['id']);

    // SQL to delete the record
    $sql = "DELETE FROM incident_table WHERE incident_id = ?";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $incident_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Incident successfully deleted!');
                window.location.href = 'tables.php';
              </script>";
    } else {
        echo "<script>
                alert('Error deleting the incident.');
                window.location.href = 'tables.php';
              </script>";
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<script>
            alert('Invalid request.');
            window.location.href = 'tables.php';
          </script>";
}

// Close the database connection
$conn->close();
?>
