<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}
?>

<?php
// Include the database connection file
include('incidents.php');


// Fetch data from the database
$sql = "SELECT * FROM incident_table"; // Replace 'incidents' with your table name
$result = $conn->query($sql);

// Total Count
$countquery = "SELECT COUNT(DISTINCT incident_id) AS client_count FROM incident_table";
$countresult = mysqli_query($conn, $countquery);
$row = mysqli_fetch_assoc($countresult);
$client_count = $row['client_count'];


// Count medium status incidents
$query_medium = "SELECT COUNT(*) AS medium_count FROM incident_table WHERE LOWER(status) = 'medium'";
$result_medium = mysqli_query($conn, $query_medium);
$row_medium = mysqli_fetch_assoc($result_medium);
$medium_count = $row_medium['medium_count'];

// Count high status incidents
$query_high = "SELECT COUNT(*) AS high_count FROM incident_table WHERE LOWER(status) = 'high'";
$result_high = mysqli_query($conn, $query_high);
$row_high = mysqli_fetch_assoc($result_high);
$high_count = $row_high['high_count'];

// Count low status incidents
$query_low = "SELECT COUNT(*) AS low_count FROM incident_table WHERE LOWER(status) = 'low'";
$result_low = mysqli_query($conn, $query_low);
$row_low = mysqli_fetch_assoc($result_low);
$low_count = $row_low['low_count'];


mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Smart Real-Time Accident Detection and Monitoring</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <link href="css/styles.css" rel="stylesheet" />
        <link href="statusSytles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            body {
            background-color:rgb(91, 101, 134);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        </style>
        <!-- CSS Styling for the incident table-->
        <style>
            .bg-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .bg-gradient-warning {
                background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
            }
            .bg-gradient-danger {
                background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            }
            .bg-gradient-success {
                background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            }
            .status-btn {
                padding: 6px 12px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: bold;
                transition: transform 0.2s ease, background-color 0.2s ease;
            }
            .status-btn:hover {
                transform: scale(1.05);
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }
            .status-high { background-color: #e57373; color: white; }
            .status-medium { background-color: #fbc02d; color: white; }
            .status-low { background-color: #81c784; color: white; }

            /* DataTable Styling */
            table#datatablesSimple {
                border-collapse: collapse;
                width: 100%;
                font-size: 14px;
                background-color: #f9f9f9;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            table#datatablesSimple thead tr {
                background-color: #607d8b;
                color: #fff;
            }
            table#datatablesSimple th, table#datatablesSimple td {
                padding: 12px 15px;
                text-align: left;
            }
            table#datatablesSimple tbody tr:nth-child(even) {
                background-color: #e0e0e0;
            }
            table#datatablesSimple tbody tr:hover {
                background-color: #d6d6d6;
                transition: background-color 0.3s ease;
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
                                            
                                            <a class="nav-link" href="addClient.php">AddClient</a>
                                            <a class="nav-link" href="password.html">Forgot Password</a>
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
        <h1 class="mt-4 text-white">Smart Real-Time Accident Detection, Alert && Monitoring System</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>

        <div class="row">
            <!-- Incident Count Cards (2x2 Grid) -->
            <div class="col-md-4">
                <div class="row">
                    <!-- Total Count -->
                    <div class="col-md-6">
                        <div class="card bg-gradient-primary text-white shadow-lg rounded text-center">
                            <div class="card-body">
                                <h5 class="card-title mb-2">Total Count</h5>
                                <h1 class="display-5"><?php echo $client_count; ?></h1>
                            </div>
                        </div>
                    </div>

                    <!-- Medium Priority -->
                    <div class="col-md-6">
                        <div class="card bg-gradient-warning text-white shadow-lg rounded text-center">
                            <div class="card-body">
                                <h5 class="card-title mb-2">Medium Priority</h5>
                                <h1 class="display-5"><?php echo $medium_count; ?></h1>
                            </div>
                        </div>
                    </div>

                    <!-- High Priority -->
                    <div class="col-md-6 mt-3">
                        <div class="card bg-gradient-danger text-white shadow-lg rounded text-center">
                            <div class="card-body">
                                <h5 class="card-title mb-2">High Priority</h5>
                                <h1 class="display-5"><?php echo $high_count; ?></h1>
                            </div>
                        </div>
                    </div>

                    <!-- Low Priority -->
                    <div class="col-md-6 mt-3">
                        <div class="card bg-gradient-success text-white shadow-lg rounded text-center">
                            <div class="card-body">
                                <h5 class="card-title mb-2">Low Priority</h5>
                                <h1 class="display-5"><?php echo $low_count; ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Right Side: Area Chart + Pie Chart -->
            <div class="col-md-8">
                
                <div class="row">
                    <!-- Area Chart (Larger, Takes 8 Columns) -->
                    <div class="col-md-8">
                        <div class="card shadow-lg rounded" style="height: 300px;">
                            <div class="card-header bg-dark text-white">
                                <i class="fas fa-chart-area me-1"></i> Area Chart For Incidents
                            </div>
                            <div class="card-body">
                                <canvas id="myAreaChart" width="50%" height="50%"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Pie Chart (Smaller, Takes 4 Columns) -->
                    <div class="col-md-4">
                        <div class="card shadow-lg rounded" style="height: 300px;">
                            <div class="card-header bg-dark text-white">
                                <i class="fas fa-chart-pie me-1"></i> Incident Categories
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <canvas id="myPieChart" width="50%" height="0"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="row md-4">
                <!-- GPS Location Viewer -->
                <div class="card-body" id="mapContainer">
                            <?php include('google_location.php') ?>
                        </div>
            </div>
        </div>

        <!-- Accident Incident DataTable -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i> Accident Incident DataTable
            </div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Incident ID</th>
                            <th>Plate_No</th>
                            <th>Vehicle</th>
                            <th>Owner</th>
                            <th>Tel_No</th>
                            <th>Kin_Phone_No</th>
                            <th>Location</th>
                            <th>IncidentTime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="incidentData">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status = strtolower($row['status']);
                                $buttonColorClass = '';
                                $icon = '';

                                switch ($status) {
                                    case 'high':
                                        $buttonColorClass = 'status-high';
                                        $icon = '<i class="fas fa-times-circle"></i>';
                                        break;
                                    case 'medium':
                                        $buttonColorClass = 'status-medium';
                                        $icon = '<i class="fas fa-exclamation-circle"></i>';
                                        break;
                                    case 'low':
                                        $buttonColorClass = 'status-low';
                                        $icon = '<i class="fas fa-check-circle"></i>';
                                        break;
                                }

                                echo "<tr>
                                    <td>{$row['incident_id']}</td>
                                    <td>{$row['plate_no']}</td>
                                    <td>{$row['vehicle']}</td>
                                    <td>{$row['owner']}</td>
                                    <td>{$row['phone_no']}</td>
                                    <td>{$row['kin_phone_no']}</td>
                                    <td>{$row['location']}</td>
                                    <td>{$row['incident_time']}</td>
                                    <td>{$row['status']}</td>
                                    
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No data found</td></tr>";
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- JavaScript for Charts -->
        <script>
            const ctxArea = document.getElementById('myAreaChart').getContext('2d');
            new Chart(ctxArea, {
                type: 'line',
                data: { /* Your area chart data here */ },
                options: { /* Chart options */ }
            });

            const ctxPie = document.getElementById('myPieChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ["High", "Medium", "Low"],
                    datasets: [{
                        data: [<?php echo $high_count; ?>, <?php echo $medium_count; ?>, <?php echo $low_count; ?>],
                        backgroundColor: ['#ff4b2b', '#fbc02d', '#81c784']
                    }]
                },
                options: { responsive: true }
            });
        </script>
            
    

     <!-- JavaScript for Charts -->
        <script>
            const ctxArea = document.getElementById('myAreaChart').getContext('2d');
            new Chart(ctxArea, {
                type: 'line',
                data: { /* Your area chart data here */ },
                options: { /* Chart options */ }
            });

            const ctxPie = document.getElementById('myPieChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ["High", "Medium", "Low"],
                    datasets: [{
                        data: [<?php echo $high_count; ?>, <?php echo $medium_count; ?>, <?php echo $low_count; ?>],
                        backgroundColor: ['#ff4b2b', '#fbc02d', '#81c784']
                    }]
                },
                options: { responsive: true }
            });
        </script>

    </body>
</html>
