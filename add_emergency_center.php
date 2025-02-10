<?php
// Include the database connection file
include('db.php');

// Initialize variables for form data
$center_id = $center_name = $center_location = $contact_number = '';
$message = ''; // Message to display success or error

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $center_name = $_POST['center_name'];
    $center_location = $_POST['center_location'];
    $contact_number = $_POST['contact_number'];

    // Validate the inputs
    if (!empty($center_name) && !empty($center_location) && !empty($contact_number)) {
        // Prepare the SQL query to insert the record
        $sql = "INSERT INTO emergency_centers (center_name, center_location, contact_number) 
                VALUES (:center_name, :center_location, :contact_number)";

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind the parameters
        $stmt->bindParam(':center_name', $center_name);
        $stmt->bindParam(':center_location', $center_location);
        $stmt->bindParam(':contact_number', $contact_number);

        // Execute the query
        if ($stmt->execute()) {
            // Get the last inserted center_id
            $center_id = $conn->lastInsertId();
            $message = "Emergency center added successfully! Center ID: " . $center_id;
        } else {
            $message = "Error inserting record.";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Add Emergency Center</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Add Emergency Center</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <?php if (!empty($message)) { ?>
                                            <div class="alert alert-info"><?= $message ?></div>
                                        <?php } ?>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputCenterID" type="text" name="center_id" value="<?= $center_id ?>" readonly />
                                            <label for="inputCenterID">Center ID (Auto-generated)</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputCenterName" type="text" name="center_name" placeholder="Enter Center Name" required />
                                            <label for="inputCenterName">Center Name</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputCenterLocation" type="text" name="center_location" placeholder="Enter Center Location" required />
                                            <label for="inputCenterLocation">Center Location</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputContactNumber" type="tel" name="contact_number" placeholder="Enter Contact Number" required />
                                            <label for="inputContactNumber">Contact Number</label>
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-block">Add Emergency Center</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="table.php">Back to Records</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
