<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends List</title>
</head>
<body>
<?php
include 'db_conn.php';

// Assuming the user ID is stored in the session
session_start();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT u.id, u.username FROM friends f JOIN users u ON f.friend_id = u.id WHERE f.user_id = ?");
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

<ul>
<?php if (empty($friends)): ?>
    <li>No friends found.</li>
<?php else: ?>
    <?php foreach ($friends as $friend): ?>
        <li>
            <?php echo htmlspecialchars($friend['username']); ?>
            <button onclick="startChat(<?php echo $friend['id']; ?>)">Chat</button>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
</ul>

<script>
function startChat(friendId) {
    // Set the friend ID for private chat
    document.getElementById('friend-id').value = friendId;
    // Optionally, you can redirect to a private chat page
    // window.location.href = `private_chat.php?friend_id=${friendId}`;
}
</script>
</body>
</html>
