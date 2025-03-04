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
    <link rel="stylesheet" href="login_styles.css"> 
    <style>
        body {
            background-image: url('images/caution.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        .login-container h3 {
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-label {
            font-weight: 600;
            color: #555;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .btn-primary {
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            background-color: #007bff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .login-options a {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #007bff;
            text-decoration: none;
        }

        .login-options a:hover {
            text-decoration: underline;
        }
        .modal-content {
            border-radius: 12px;
        }
        @media (max-width: 576px) {
            .login-container {
                padding: 25px;
            }
            .login-container h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3>Login to Admin Panel</h3>
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
    </div>

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
</body>
</html>
