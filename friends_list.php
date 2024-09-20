<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends List</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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

<div class="container mt-4">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <img src="<?php echo htmlspecialchars($userPfp); ?>" alt="Profile Picture" class="pfp rounded-circle">
    <a href="profile.php?username=<?php echo htmlspecialchars($username); ?>" class="btn btn-info ml-2">My Profile</a>

    <h2 class="mt-4">Friends List</h2>
    <ul class="list-group">
    <?php foreach ($friends as $friend): ?>
        <li class="list-group-item d-flex align-items-center">
            <img src="<?php echo htmlspecialchars($friend['pfp']); ?>" alt="Profile Picture" class="pfp mr-2">
            <a href="profile.php?username=<?php echo htmlspecialchars($friend['username']); ?>" class="mr-auto">
                <?php echo htmlspecialchars($friend['username']); ?>
            </a>
            <button onclick="startChat(<?php echo $friend['id']; ?>)" class="btn btn-primary">Chat</button>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

<script>
function startChat(friendId) {
    // Redirect to the private chat page
    window.location.href = `private_chat.php?friend_id=${friendId}`;
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
