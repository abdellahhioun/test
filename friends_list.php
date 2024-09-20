<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends List</title>
    <style>
        .pfp { width: 30px; height: 30px; border-radius: 50%; }
    </style>
</head>
<body>
<?php
include 'db_conn.php';

// Assuming the user ID is stored in the session
session_start();
$userId = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT username, COALESCE(pfp, 'assets/default.png') AS pfp FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $userPfp);
$stmt->fetch();
$stmt->close();

// Fetch friends information
$stmt = $conn->prepare("SELECT DISTINCT u.id, u.username, COALESCE(u.pfp, 'assets/default.png') AS pfp FROM friends f JOIN users u ON f.friend_id = u.id WHERE f.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = $row;
}

$stmt->close();
$conn->close();
?>

<h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
<img src="<?php echo htmlspecialchars($userPfp); ?>" alt="Profile Picture" class="pfp">
<a href="profile.php?username=<?php echo htmlspecialchars($username); ?>">My Profile</a>

<h2>Friends List</h2>
<ul>
<?php foreach ($friends as $friend): ?>
    <li>
        <img src="<?php echo htmlspecialchars($friend['pfp']); ?>" alt="Profile Picture" class="pfp">
        <a href="profile.php?username=<?php echo htmlspecialchars($friend['username']); ?>">
            <?php echo htmlspecialchars($friend['username']); ?>
        </a>
        <button onclick="startChat(<?php echo $friend['id']; ?>)">Chat</button>
    </li>
<?php endforeach; ?>
</ul>

<script>
function startChat(friendId) {
    // Redirect to the private chat page
    window.location.href = `private_chat.php?friend_id=${friendId}`;
}
</script>
</body>
</html>
