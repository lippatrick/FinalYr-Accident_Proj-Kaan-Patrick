<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users
$users = $conn->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Add User
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // Hash the password
    $role = $_POST['role'];
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $role]);
    header("Location: admin.php");
    exit();
}

// Delete User
if (isset($_GET['delete_user'])) {
    $userId = $_GET['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    header("Location: admin.php");
    exit();
}

// Fetch login activity
$logins = $conn->query("SELECT login_activity.*, users.username 
                        FROM login_activity 
                        JOIN users ON login_activity.user_id = users.id")
               ->fetchAll(PDO::FETCH_ASSOC);
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
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
            main {
                background-image: url('images/admin_image.jpg');
                background-size: cover; /* Ensures the image covers the entire element */
                background-position: center center; /* Centers the image */
                background-repeat: no-repeat; /* Prevents the image from repeating */
            }
        </style>
         <style>
        /* Container for the flex layout */
        .container {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin: 20px 0;
        }

        /* Style for the form section (smaller form) */
        .form-container {
            flex: 0 0 300px; /* Fixed width to make it smaller */
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .form-container:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .form-container h3 {
            color: #003366; /* Navy Blue color */
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
        }

        .form-container input, 
        .form-container select, 
        .form-container button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .form-container input:focus, 
        .form-container select:focus, 
        .form-container button:focus {
            border-color: #003366; /* Navy Blue color */
            outline: none;
        }

        .form-container button {
            background-color: #003366; /* Navy Blue */
            color: white;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #002244; /* Darker Navy Blue */
        }

        /* Style for the user table */
        .user-table-container {
            flex: 1; /* Let the user list take the remaining space */
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .user-list-title {
            color: #003366; /* Navy Blue */
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            font-size: 16px;
        }

        table th {
            background-color: #003366; /* Navy Blue */
            color: #fff;
            font-weight: 600;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        table td a {
            color: #ff0000;
            text-decoration: none;
            font-weight: 500;
        }

        table td a:hover {
            text-decoration: underline;
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
                        <li><a class="dropdown-item" href="#activity-log">Activity Log</a></li>
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
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Admin
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
           
            <main style="font-family: Arial, sans-serif; background-color: #f0f2f5; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
            <div style="position: relative; margin: 20px 0; text-align: center; height: 250px; background: rgba(0, 0, 0, 0.5);">
    <h1 style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #fff; font-size: 50px; font-weight: bold; font-family: 'Roboto', sans-serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">Welcome Admin</h1>
</div>

<div class="container">
        <!-- Add User -->
        <div class="form-container">
            <form method="POST">
                <h3>Add User</h3>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" name="add_user">Add User</button>
            </form>
        </div>

        <!-- List of Users -->
        <div>
            <h4 class="user-list-title">Users Added</h4>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['username'] ?></td>
                            <td><?= $user['role'] ?></td>
                            <td><a href="?delete_user=<?= $user['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Login Activity -->
    <h3 style="margin-top: 20px; color: #555; font-size: 24px;">Login Activity</h3>
<table id="activity-log" border="1" style="width: 100%; border-collapse: collapse; background-color: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
    <thead style="background-color: #007bff; color: #fff;">
        <tr>
            <th style="padding: 12px; text-align: left;">Username</th>
            <th style="padding: 12px; text-align: left;">Login Time</th>
        </tr>
    </thead>
    <tbody id="loginTableBody">
        <?php foreach ($logins as $login): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 12px;"> <?= $login['username'] ?> </td>
                <td style="padding: 12px;"> <?= $login['login_time'] ?> </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<button onclick="toggleRows()" style="margin-top: 10px; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Show More</button>

</main>

                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; lubegap132@gmail.com</div>
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
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                form.addEventListener('submit', function(event) {
                    const username = form.querySelector('[name="username"]').value;
                    const password = form.querySelector('[name="password"]').value;

                    if(username.trim() === '' || password.trim() === '') {
                        event.preventDefault();
                        alert('Please fill out all fields.');
                    } else {
                        alert('User added successfully!');
                    }
                });

                const deleteLinks = document.querySelectorAll('a[href*="delete_user"]');
                deleteLinks.forEach(link => {
                    link.addEventListener('click', function(event) {
                        if(!confirm('Are you sure you want to delete this user?')) {
                            event.preventDefault();
                        }
                    });
                });
            });
        </script>

        <!-- activity table javascript code -->
        <script>
            const rows = document.querySelectorAll('#loginTableBody tr');
            let visible = 5;
            function toggleRows() {
                for(let i = 5; i < rows.length; i++) {
                    rows[i].style.display = rows[i].style.display === 'none' || rows[i].style.display === '' ? 'table-row' : 'none';
                }
                document.querySelector('button').textContent = document.querySelector('button').textContent === 'Show More' ? 'Show Less' : 'Show More';
            }
            window.onload = () => {
                for(let i = 5; i < rows.length; i++) rows[i].style.display = 'none';
            }
        </script>

        
    </body>
</html>
