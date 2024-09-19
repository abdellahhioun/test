<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT users.id, users.username, users.pfp 
                        FROM friends 
                        JOIN users ON friends.friend_id = users.id 
                        WHERE friends.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends List</title>
    <style>
        .friend { margin-bottom: 10px; }
        .pfp { width: 30px; height: 30px; border-radius: 50%; }
        .username { font-weight: bold; margin-right: 5px; }
    </style>
</head>
<body>
    <h1>Friends List</h1>
    <div id="friends-list">
        <?php if (count($friends) > 0): ?>
            <?php foreach ($friends as $friend): ?>
                <div class="friend">
                    <img src="path/to/pfp/<?php echo htmlspecialchars($friend['pfp']); ?>" alt="Profile Picture" class="pfp">
                    <span class="username"><?php echo htmlspecialchars($friend['username']); ?></span>
                    <button onclick="startPrivateChat(<?php echo $friend['id']; ?>)">Private Chat</button>
                    <button onclick="startVideoChat('<?php echo htmlspecialchars($friend['username']); ?>')">Video Chat</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No friends found.</p>
        <?php endif; ?>
    </div>

    <script>
        function startPrivateChat(friendId) {
            // Set the friend ID for private chat
            document.getElementById('friend-id').value = friendId;
            // Optionally, you can redirect to a private chat page
            // window.location.href = `private_chat.php?friend_id=${friendId}`;
        }

        function startVideoChat(friendUsername) {
            // Implement video chat functionality here
            alert('Video chat with ' + friendUsername);
        }
    </script>
</body>
</html>
