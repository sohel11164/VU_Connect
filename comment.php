<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$postId  = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['comment'] ?? '');

if ($postId <= 0 || $content === '') {
    header('Location: view.php'); exit;
}

$stmt = $conn->prepare(
    "INSERT INTO comments(post_id,user_id,comment) VALUES(?,?,?)"
);
$stmt->bind_param('iis', $postId, $_SESSION['user_id'], $content);
$stmt->execute();

header("Location: view.php#post-$postId");
