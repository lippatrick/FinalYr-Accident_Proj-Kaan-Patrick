<?php
// Include database connection file
include('incidents.php'); 

// Check if 'center_id' parameter exists in the query string
if (isset($_GET['center_id'])) {
    // Sanitize the input
    $center_id = intval($_GET['center_id']);

    // SQL to delete the record
    $sql = "DELETE FROM emergency_centers WHERE center_id = ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $center_id);
        
        if ($stmt->execute()) {
            echo "<script>
                    alert('Emergency center successfully deleted!');
                    window.location.href = 'table.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error deleting the emergency center.');
                    window.location.href = 'table.php';
                  </script>";
        }
        
        // Close the statement
        $stmt->close();
    } else {
        echo "<script>
                alert('Error preparing the statement.');
                window.location.href = 'table.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid or missing request parameter.');
            window.location.href = 'table.php';
          </script>";
}

// Close the database connection
$conn->close();
?>
