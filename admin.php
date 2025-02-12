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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
    <h1>Welcome, Admin</h1>

    <!-- Add User -->
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

    <!-- List of Users -->
    <h3>Users</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['username'] ?></td>
                <td><?= $user['role'] ?></td>
                <td>
                    <a href="?delete_user=<?= $user['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Login Activity -->
    <h3>Login Activity</h3>
    <table border="1">
        <tr>
            <th>Username</th>
            <th>Login Time</th>
        </tr>
        <?php foreach ($logins as $login): ?>
            <tr>
                <td><?= $login['username'] ?></td>
                <td><?= $login['login_time'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
