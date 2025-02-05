<?php
include('incidents.php');
$id = $_GET['id'];
$sql = "DELETE FROM emergency_centers WHERE center_id='$id'";
if ($conn->query($sql) === TRUE) {
    header("Location: tables.php");
} else {
    echo "Error deleting record: " . $conn->error;
}
?>
