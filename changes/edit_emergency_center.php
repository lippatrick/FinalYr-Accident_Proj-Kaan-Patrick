<?php
include('incidents.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $center_id = $_POST['center_id'];
    $center_name = $_POST['center_name'];
    $center_location = $_POST['center_location'];
    $contact_number = $_POST['contact_number'];

    $sql = "UPDATE emergency_centers SET center_name='$center_name', center_location='$center_location', contact_number='$contact_number' WHERE center_id='$center_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: tables.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM emergency_centers WHERE center_id='$id'");
    $row = $result->fetch_assoc();
}
?>
<form method="POST" action="">
    <input type="hidden" name="center_id" value="<?= $row['center_id'] ?>">
    Center Name: <input type="text" name="center_name" value="<?= $row['center_name'] ?>"><br>
    Location: <input type="text" name="center_location" value="<?= $row['center_location'] ?>"><br>
    Contact Number: <input type="text" name="contact_number" value="<?= $row['contact_number'] ?>"><br>
    <button type="submit">Update</button>
</form>
