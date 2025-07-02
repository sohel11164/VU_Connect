<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'message'=>'Login required']); exit;
}

$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?: $_POST;
$postId = (int)($in['post_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if ($postId <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid post']); exit;
}

$chk = $conn->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
$chk->bind_param('ii', $postId, $userId);
$chk->execute();
$liked = $chk->get_result()->num_rows;

if ($liked) {
    $del = $conn->prepare("DELETE FROM likes WHERE post_id=? AND user_id=?");
    $del->bind_param('ii', $postId, $userId);
    $del->execute();
    $liked = 0;
} else {
    $ins = $conn->prepare("INSERT INTO likes(post_id,user_id) VALUES(?,?)");
    $ins->bind_param('ii', $postId, $userId);
    $ins->execute();
    $liked = 1;
}

$cnt = $conn->prepare("SELECT COUNT(*) AS c FROM likes WHERE post_id=?");
$cnt->bind_param('i', $postId);
$cnt->execute();
$total = $cnt->get_result()->fetch_assoc()['c'];

echo json_encode([
    'success'     => true,
    'liked'       => (bool)$liked,
    'total_likes' => (int)$total
]);
