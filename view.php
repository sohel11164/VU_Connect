<?php
session_start();
require 'config.php';          // $conn = new mysqli(...)

$userId = $_SESSION['user_id'] ?? 0;

/* latest posts with author data */
$postsStmt = $conn->prepare(
    "SELECT posts.*, users.username, users.profile_pic
     FROM posts
     JOIN users ON posts.user_id = users.id
     ORDER BY posts.created_at DESC"
);
$postsStmt->execute();
$posts = $postsStmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Posts</title>
    <link rel="stylesheet" href="view.css">
    <style>
        .post         {border:1px solid #ccc;padding:15px;margin-bottom:20px;border-radius:10px;background:#f9f9f9}
        .post-user    {display:flex;align-items:center;margin-bottom:10px}
        .profile-pic  {width:40px;height:40px;border-radius:50%;margin-right:10px;object-fit:cover}
        .actions      {display:flex;gap:10px;align-items:center;margin-top:5px}
        .comment-box  {padding:5px;width:60%}
        .comment      {background:#eee;padding:5px 10px;border-radius:5px;margin-top:5px}
        button        {cursor:pointer}
    </style>
</head>
<body>

<div class="nav">
    <a href="index.php">Home</a>  <a href="register.php">Register</a>
    <a href="login.php">Login</a> <a href="dashboard.php">Dashboard</a>
    <a href="view.php">Posts</a>  <a href="profile.php">Profile</a>
</div>

<div class="container">
    <h2>All Posts</h2>

<?php while ($post = $posts->fetch_assoc()): ?>
    <?php
    /* per‑post like data */
    $likeCnt   = $conn->query("SELECT COUNT(*) AS c FROM likes WHERE post_id={$post['id']}")->fetch_assoc()['c'];
    $didILike  = $userId ?
        $conn->query("SELECT 1 FROM likes WHERE post_id={$post['id']} AND user_id=$userId")->num_rows : 0;
    ?>
    <div class="post" id="post-<?= $post['id'] ?>">
        <!-- user/heading -->
        <div class="post-user">
            <img class="profile-pic"
                 src="uploads/<?= $post['profile_pic'] ?: 'default-profile.jpg' ?>"
                 alt="pic">
            <strong><?= htmlspecialchars($post['username']) ?></strong>
        </div>

        <!-- body -->
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

        <!-- like / comment -->
        <div class="actions">
            <?php if ($userId): ?>
                <button id="like-btn-<?= $post['id'] ?>"
                        onclick="toggleLike(<?= $post['id'] ?>)">
                    <?= $didILike ? '❤️ Liked' : '❤️ Like' ?>
                </button>
            <?php else: ?>
                <em><a href="login.php">login to like</a></em>
            <?php endif; ?>

            <span id="like-cnt-<?= $post['id'] ?>"><?= $likeCnt ?> ❤️ Likes</span>
        </div>

        <!-- comment form -->
        <?php if ($userId): ?>
            <form method="POST" action="comment.php" class="actions">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="text" name="comment" class="comment-box"
                       placeholder="Write a comment…" required>
                <button>💬 Comment</button>
            </form>
        <?php endif; ?>

        <!-- comment list -->
        <div class="comments">
            <?php
            $cStmt = $conn->prepare(
                "SELECT comments.*, users.username
                 FROM comments
                 JOIN users ON comments.user_id = users.id
                 WHERE post_id = ? ORDER BY comments.created_at DESC"
            );
            $cStmt->bind_param('i', $post['id']);
            $cStmt->execute();
            $comments = $cStmt->get_result();
            while ($c = $comments->fetch_assoc()):
            ?>
                <div class="comment">
                    <strong><?= htmlspecialchars($c['username']) ?></strong>:
                    <?= htmlspecialchars($c['comment']) ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endwhile; ?>
</div>

<script>
function toggleLike(postId){
    fetch('like.php', {
        method: 'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({post_id: postId})
    })
    .then(res => res.json())
    .then(data => {
        if(!data.success) return alert(data.message||'error');
        /* update UI */
        document.getElementById('like-btn-'+postId).textContent =
            data.liked ? '❤️ Liked' : '❤️ Like';
        document.getElementById('like-cnt-'+postId).textContent =
            data.total_likes + ' ❤️ Likes';
    })
    .catch(err => console.error(err));
}
</script>
</body>
</html>
