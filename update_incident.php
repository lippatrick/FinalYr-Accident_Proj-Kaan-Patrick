<?php
// Include the database connection file
include('db.php');


// Initialize variables for form data
$plate_no = $vehicle = $owner = $phone_no = '';
$id = ''; // Add an ID to identify the record to edit

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $plate_no = $_POST['plate_no'];
    $vehicle = $_POST['vehicle'];
    $owner = $_POST['owner'];
    $phone_no = $_POST['phone_no'];
    $incident_id = $_POST['incident_id']; // Get the ID of the record to edit

    // Validate the inputs
    if (!empty($plate_no) && !empty($vehicle) && !empty($owner) && !empty($phone_no)) {
        // Prepare the SQL query to update the record
        $sql = "UPDATE incident_table SET plate_no = :plate_no, vehicle = :vehicle, owner = :owner, phone_no = :phone_no 
                WHERE incident_id = :incident_id";

        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters
            $stmt->bindParam(':plate_no', $plate_no);
            $stmt->bindParam(':vehicle', $vehicle);
            $stmt->bindParam(':owner', $owner);
            $stmt->bindParam(':phone_no', $phone_no);
            $stmt->bindParam(':incident_id', $incident_id); // Bind the ID for updating the correct record

            // Execute the query
            if ($stmt->execute()) {
                echo "<script>
                        alert('Record updated successfully!');
                        window.location.href = 'table.php';
                      </script>";
            } else {
                echo "Error updating record: " . $stmt->errorInfo()[2];
            }
        } else {
            echo "Error preparing the query: " . $conn->errorInfo()[2];
        }
    } else {
        echo "Please fill in all fields.";
    }
}

// If an ID is provided, fetch the existing record to pre-fill the form
if (isset($_GET['incident_id'])) {
    $id = $_GET['incident_id'];
    $sql = "SELECT * FROM incident_table WHERE incident_id = :incident_id";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bindParam(':incident_id', $incident_id);
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pre-fill form fields with existing data
        if ($record) {
            $plate_no = $record['plate_no'];
            $vehicle = $record['vehicle'];
            $owner = $record['owner'];
            $phone_no = $record['phone_no'];
        } else {
            echo "Record not found.";
        }
    } else {
        echo "Error fetching record: " . $conn->errorInfo()[2];
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Edit Incident Record</title>
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
                                    <h3 class="text-center font-weight-light my-4">Edit Incident Record</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($incident_id); ?>" /> <!-- Hidden field for ID -->
                                        <div class="form-floating mb-3">
                                        <div><label for="inputPlateNo">Plate Number</label></div>
                                        <input class="form-control" id="inputPlateNo" type="text" name="plate_no" placeholder="<?= htmlspecialchars($plate_no); ?>" required />
                                    </div>

                                    <div class="form-floating mb-3">
                                        <div><label for="inputVehicle">Vehicle</label></div>
                                        <input class="form-control" id="inputVehicle" type="text" name="vehicle" placeholder="<?= htmlspecialchars($vehicle); ?>" required />
                                    </div>

                                    <div class="form-floating mb-3">
                                        <div><label for="inputOwner">Owner</label></div>
                                        <input class="form-control" id="inputOwner" type="text" name="owner" placeholder="<?= htmlspecialchars($owner); ?>" required />
                                    </div>

                                    <div class="form-floating mb-3">
                                        <div><label for="inputPhoneNo">Phone Number</label></div>
                                        <input class="form-control" id="inputPhoneNo" type="tel" name="phone_no" placeholder="<?= htmlspecialchars($phone_no); ?>" required />
                                    </div>

                                        <div class="mt-4 mb-0">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-block">Update Record</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="tables.php">Back to Records</a></div>
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
