<?php
session_start();
include 'config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $content);
    $stmt->execute();
}
?>
<link rel="stylesheet" href="dashboard.css">

<div class="container">
    <h2>Create a Post</h2>
    <form method="POST">
        <textarea name="content" placeholder="Write something..." required></textarea>
        <button type="submit">Post</button>
    </form>
    <a href="view.php">View Posts</a>
</div>
