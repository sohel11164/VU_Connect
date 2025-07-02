<?php
require 'config.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $username   = trim($_POST['username']);
    $password   = $_POST['password'];
    $phone      = trim($_POST['phone']);
    $department = trim($_POST['department']);

    // Validation checks
    if (strlen($password) < 6) {
        $msg = "❌ Password must be at least 6 characters.";
    } elseif (!preg_match('/^[A-Z]/', $password)) {
        $msg = "❌ Password must start with a capital letter.";
    } elseif (!preg_match('/\d/', $password)) {
        $msg = "❌ Password must contain at least one digit.";
    } else {
        // Check if username already exists
        $chk = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $chk->bind_param('s', $username);
        $chk->execute();
        $chk_result = $chk->get_result();

        if ($chk_result->num_rows > 0) {
            $msg = "❌ Username already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $ins = $conn->prepare(
                "INSERT INTO users(name, username, password, phone, department)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $ins->bind_param('sssss', $name, $username, $hashed, $phone, $department);
            $ins->execute();

            header('Location: login.php?reg=1');
            exit;
        }
    }
}
?>

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
    <h2>Register</h2>

    <?php if (!empty($msg)) echo "<p style='color:red'>$msg</p>"; ?>

    <form method="POST">
        <input type="text" name="name"      placeholder="Full Name" required>
        <input type="text" name="username"  placeholder="Username" required>
        <input type="password" name="password"
               placeholder="Password (min 6 chars, start with capital, include digit)" required>
        <input type="text" name="phone"     placeholder="Phone Number" required>

        <select name="department" required>
            <option value="">Select Department</option>
            <option value="CSE">CSE</option>
            <option value="EEE">EEE</option>
            <option value="BBA">BBA</option>
        </select>

        <button type="submit">Register</button>
    </form>
</div>
