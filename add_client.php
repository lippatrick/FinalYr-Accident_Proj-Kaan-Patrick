<?php
// Include the database connection file
include('db.php');

// Initialize variables for form data
$plate_no = $vehicle = $owner = $phone_no = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $plate_no = $_POST['plate_no'];
    $vehicle = $_POST['vehicle'];
    $owner = $_POST['owner'];
    $phone_no = $_POST['phone_no'];

    // Validate the inputs (optional)
    if (!empty($plate_no) && !empty($vehicle) && !empty($owner) && !empty($phone_no)) {
        // Prepare the SQL query to insert a new record
        $sql = "INSERT INTO incident_table (plate_no, vehicle, owner, phone_no) 
                VALUES (?, ?, ?, ?)";

        // Prepare the statement using PDO
        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameters using PDO
            $stmt->bindValue(1, $plate_no, PDO::PARAM_STR);
            $stmt->bindValue(2, $vehicle, PDO::PARAM_STR);
            $stmt->bindValue(3, $owner, PDO::PARAM_STR);
            $stmt->bindValue(4, $phone_no, PDO::PARAM_STR);

            // Execute the query
            if ($stmt->execute()) {
                echo "<script>
                        alert('Record added successfully!');
                        window.location.href = 'tables.php';
                      </script>";
            } else {
                echo "Error adding record: " . $stmt->errorInfo()[2];  // Fetch the PDO error
            }

            // Close the statement
            $stmt->closeCursor();  // This is used for PDO instead of $stmt->close()
        } else {
            echo "Error preparing the query: " . $conn->errorInfo()[2]; // Fetch the PDO error
        }
    } else {
        echo "Please fill in all fields.";
    }
}
?>

<!-- HTML Form (no changes needed) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Add New Incident - SB Admin</title>
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
                                <div class="card-header"><h3 class="text-center font-weight-light my-4">Add New Incident Record</h3></div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputPlateNo" type="text" name="plate_no" placeholder="Enter Plate Number" value="<?php echo htmlspecialchars($plate_no); ?>" />
                                            <label for="inputPlateNo">Plate Number</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputVehicle" type="text" name="vehicle" placeholder="Enter Vehicle Type" value="<?php echo htmlspecialchars($vehicle); ?>" />
                                            <label for="inputVehicle">Vehicle</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputOwner" type="text" name="owner" placeholder="Enter Owner Name" value="<?php echo htmlspecialchars($owner); ?>" />
                                            <label for="inputOwner">Owner</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputPhoneNo" type="tel" name="phone_no" placeholder="Enter Phone Number" value="<?php echo htmlspecialchars($phone_no); ?>" />
                                            <label for="inputPhoneNo">Phone Number</label>
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid"><button type="submit" class="btn btn-primary btn-block">Add Record</button></div>
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
