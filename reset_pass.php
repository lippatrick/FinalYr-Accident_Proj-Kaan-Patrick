<?php
session_start();
require 'db.php'; // Include the database connection

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists and belongs to admin
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND role = 'admin'");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = md5($_POST['new_password']);
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
            $stmt->execute([$new_password, $token]);
            echo "<div class='alert alert-success'>Password reset successful.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid or expired token.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No token provided.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container vh-100 d-flex align-items-center justify-content-center">
        <div class="card p-4" style="width: 400px;">
            <h3 class="text-center">Enter New Password</h3>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>
