<?php
// include('dp.php');
include('incidents.php');

$sql = "SELECT * FROM incident_table"; // Replace 'incidents' with your table name
$result = $conn->query($sql);

$emergency_sql = "SELECT * FROM emergency_centers";
$emergency_result = $conn->query($emergency_sql);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
if (!$result) {
    die("Error fetching incident data: " . $conn->error);
}
if (!$emergency_result) {
    die("Error fetching emergency center data: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Tables</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        
        <style>
            /* Styling for the overall card and table sections */
.card.mb-4 {
    border: 1px solid #4e73df; /* Dark navy blue border */
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background-color: #ffffff;
    margin-bottom: 20px;
    padding: 10px;
}

/* Styling for the card header */
.card-header {
    background-color: #2c3e50; /* Dark navy blue */
    color: white;
    font-size: 1.1rem;
    font-weight: bold;
    padding: 12px 15px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

/* Table styles */
#datatablesSimple {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    font-size: 0.85rem;
}

#datatablesSimple thead {
    background-color: #34495e; /* Lighter dark navy blue */
    text-align: left;
    color: white;
}

#datatablesSimple th, #datatablesSimple td {
    padding: 8px 12px;
    border-bottom: 1px solid #ddd;
}

#datatablesSimple th {
    background-color: #2c3e50; /* Dark navy blue */
    font-weight: bold;
}

#datatablesSimple tr:nth-child(even) {
    background-color: #ecf0f1; /* Light grey */
}

#datatablesSimple tbody tr:hover {
    background-color: #bdc3c7; /* Light gray for hover effect */
    cursor: pointer;
}

/* Styling for buttons in the action column */
#datatablesSimple .btn {
    text-decoration: none;
    padding: 5px 10px;
    font-size: 0.8rem;
    border-radius: 4px;
    color: white;
    margin-right: 5px;
    transition: background-color 0.3s ease;
}

#datatablesSimple .btn-sm {
    font-size: 0.75rem;
}

/* Styling for the 'Edit' button */
#datatablesSimple .btn-warning {
    background-color: #f39c12;
}

#datatablesSimple .btn-warning:hover {
    background-color: #e67e22;
}

/* Styling for the 'Delete' button */
#datatablesSimple .btn-danger {
    background-color: #e74a3b;
}

#datatablesSimple .btn-danger:hover {
    background-color: #c0392b;
}

/* Styling for the empty table message */
#datatablesSimple tbody tr td[colspan='10'] {
    text-align: center;
    font-style: italic;
    color: #888;
}


        </style>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.php">Accident Detection</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="login.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Core</div>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <div class="sb-sidenav-menu-heading">Interface</div>
                            
                            
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Management
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                                    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                                        Authentication
                                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                        <nav class="sb-sidenav-menu-nested nav">
                                            
                                            <a class="nav-link" href="addClient.php">Add Client</a>
                                            <a class="nav-link" href="add_emergency_center.php">Add Emergency Center</a>
                                        </nav>
                                    </div>
                                    
                                </nav>
                            </div>
                            <div class="sb-sidenav-menu-heading">Addons</div>
                            <a class="nav-link" href="table.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                                Tables
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Staff Member
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Tables</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Tables</li>
                        </ol>
                        
                        <!-- incidents table for all the victims -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Incident DataTable
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>Incident ID</th>
                                            <th>Plate_No</th>
                                            <th>Vehicle</th>
                                            <th>Owner_Name</th>
                                            <th>Owner_Tel</th>
                                            <th>Kin_Telephone</th>
                                            <th>Location</th>
                                            <th>Incident_Time</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
                                            if ($result->num_rows > 0) {
                                                // Output data of each row
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>
                                                        <td>" . $row['incident_id'] . "</td>
                                                        <td>" . $row['plate_no'] . "</td>
                                                        <td>" . $row['vehicle'] . "</td>
                                                        <td>" . $row['owner'] . "</td>
                                                        <td>" . $row['phone_no'] . "</td>
                                                        <td>" . $row['kin_phone_no'] . "</td>
                                                        <td>" . $row['location'] . "</td>
                                                        <td>" . $row['incident_time'] . "</td>
                                                        <td>" . $row['status'] . "</td>
                                                        <td>
                                                            <a href='update_incident.php?id=" . $row['incident_id'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                                            <a href='delete_client.php?id=" . $row['incident_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this incident?\")'>Delete</a>
                                                        </td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='8'>No data found</td></tr>";
                                            }
                                            ?>
                                    </tbody>
                                </table>

                            </div>
                            
                        </div>
                        <div class="card mb-4">
                                                            <!-- emergency table section -->
                                
                                                            <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>
                                            Emergency DataTable
                                    </div>
                                
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>Center_ID</th>
                                            <th>Center_Name</th>
                                            <th>Center_Location</th>
                                            <th>Contact_Number</th>
                                            <th>Action</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
                                            if ($emergency_result->num_rows > 0) {
                                                // Output data of each row
                                                while ($row = $emergency_result->fetch_assoc()) {
                                                    echo "<tr>
                                                        <td>" . $row['center_id'] . "</td>
                                                        <td>" . $row['center_name'] . "</td>
                                                        <td>" . $row['center_location'] . "</td>
                                                        <td>" . $row['contact_number'] . "</td>
                                                        <td>
                                                            <a href='edit_center.php?id=" . $row['center_id'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                                            <a href='delete_emergency.php?center_id=" . $row['center_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this center?\")'>Delete</a>
                                                        </td>
                                                        </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4'>No emergency center data found</td></tr>";
                                            }
                                            ?>
                                        
                                        
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website 2023</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>

        <script>
            window.addEventListener('DOMContentLoaded', (event) => {
                const datatablesSimple = document.getElementById('datatablesSimple');
                if (datatablesSimple) {
                    new simpleDatatables.DataTable(datatablesSimple);
                }
            });
        </script>
    </body>
</html>
