<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) { // ✅ changed from 'user' to 'user_id'
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id']; // ✅ consistent session key
$userData = $conn->query("SELECT * FROM users WHERE id='$userId'")->fetch_assoc();

if (isset($_POST['update'])) {
    $bio = $_POST['bio'];
    $pic = $userData['profile_pic'];

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $filename = time() . '_' . basename($_FILES['profile_pic']['name']);
        $target = "uploads/" . $filename;

        // Create uploads directory if not exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
        $pic = $filename;
    }

    $conn->query("UPDATE users SET profile_pic='$pic', bio='$bio' WHERE id='$userId'");
    header("Location: profile.php");
    exit();
}
?>
<link rel="stylesheet" href="profile.css">

<div class="container">
    <h2>Your Profile</h2>

    <?php if (!empty($userData['profile_pic'])) { ?>
        <img src="uploads/<?php echo htmlspecialchars($userData['profile_pic']); ?>" class="profile-pic" alt="Profile Picture">
    <?php } else { ?>
        <p><i>No profile picture uploaded.</i></p>
    <?php } ?>

    <p><strong>Name:</strong> <?php echo htmlspecialchars($userData['name'] ?? 'No Name'); ?></p>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['username']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($userData['phone']); ?></p>
    <p><strong>Department:</strong> <?php echo htmlspecialchars($userData['department']); ?></p>
    <p><strong>Bio:</strong> <?php echo !empty($userData['bio']) ? htmlspecialchars($userData['bio']) : '<i>No bio available.</i>'; ?></p>

    <h3>Update Profile Picture and Bio</h3>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_pic" accept="image/*">
        <textarea name="bio" placeholder="Write your bio..."><?php echo htmlspecialchars($userData['bio']); ?></textarea>
        <button type="submit" name="update">Update</button>
    </form>
</div>
