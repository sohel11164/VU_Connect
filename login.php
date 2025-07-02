<?php
session_start();
require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $password   = $_POST['password'];
    $department = trim($_POST['department']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND department = ?");
    $stmt->bind_param('ss', $username, $department);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // 🔄 consistent session key
            header('Location: dashboard.php');
            exit;
        } else {
            $error = '❌ Password is incorrect.';
        }
    } else {
        $error = '❌ No user found with that username and department.';
    }
}
?>

<!-- HTML PART -->
<link rel="stylesheet" href="style.css">

<div class="nav">
    <a href="index.php">Home</a>
    <a href="register.php">Register</a>
    <a href="login.php">Login</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="view.php">Posts</a>
    <a href="profile.php">Profile</a>
</div>

<div class="container">
    <h2>Login</h2>

    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <select name="department" required>
            <option value="">Select Department</option>
            <option value="CSE">CSE</option>
            <option value="EEE">EEE</option>
            <option value="BBA">BBA</option>
        </select>

        <button type="submit">Login</button>
    </form>
</div>
