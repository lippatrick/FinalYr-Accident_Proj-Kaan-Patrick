<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // Hash the password
    $role = $_POST['role'];

    // Check credentials in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = ?");
    $stmt->execute([$username, $password, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Start session and redirect based on role
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Log the login activity
        $stmt = $conn->prepare("INSERT INTO login_activity (user_id) VALUES (?)");
        $stmt->execute([$user['id']]);

        if ($role == 'admin') {
            header("Location: admin.php"); // Redirect to admin dashboard
        } else {
            header("Location: index.php"); // Redirect to user dashboard
        }
        exit();
    } else {
        // If login fails, display error modal using JavaScript
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('loginErrorModal'), {
                    keyboard: false
                });
                myModal.show();
            });
        </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login_styles.css"> <!-- Link to the external CSS file -->
    <style>
         body {
            background-image: url('images/knock_accident.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex align-items-center justify-content-center vh-100">
        <!-- Login Form with Image Inside -->
        <div class="login-container d-flex align-items-center">
            <!-- Left side for accident image -->
            <div class="image-container">
                <img src="images/login_image1.jpg" alt="Accident Image" class="img-fluid">
            </div>
            <!-- Right side for login form -->
            <div class="form-container">
                <h3 class="text-center">Login to Admin Panel</h3>
                <form id="loginForm" method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Login as:</label>
                        <select class="form-control" id="role" name="role">
                            <option value="admin">Admin</option>
                            <option value="user">Other User</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="login-options">
                    <a href="forgot_pass.php">Forgot Password?</a>
                </div>
                <div id="errorMessage" class="error-message" style="display: none;">Incorrect username or password. Please try again.</div>
            </div>
        </div>
    </div>
    <style>
        .container-fluid {
            height: 80vh;
        }

        .login-container {
            display: flex;
            height: 60%;
            width: 80%;
        }

        .image-container img {
            object-fit: cover; /* Makes sure the image covers the entire left side */
            width: 800%;
            height: 100%;
        }

        .form-container {
            flex: 1;
            padding: 20px;
        } 
    </style>

    <!-- Modal for error message -->
    <div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginErrorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    The username or password is incorrect. Please check your credentials and try again.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        // Function to show error message
        function showErrorMessage() {
            document.getElementById('errorMessage').style.display = 'block';
            setTimeout(function() {
                document.getElementById('errorMessage').style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
