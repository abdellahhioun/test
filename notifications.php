<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch friend requests
$stmt = $conn->prepare("SELECT invitations.id, users.username, users.pfp 
                        FROM invitations 
                        JOIN users ON invitations.sender_id = users.id 
                        WHERE invitations.receiver_id = ? AND invitations.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$friend_requests = [];
while ($row = $result->fetch_assoc()) {
    $friend_requests[] = $row;
}

$stmt->close();

// Fetch unread messages
$stmt = $conn->prepare("SELECT messages.message, messages.timestamp, users.username, users.pfp 
                        FROM messages 
                        JOIN users ON messages.user_id = users.id 
                        WHERE messages.friend_id = ? AND messages.read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$unread_messages = [];
while ($row = $result->fetch_assoc()) {
    $unread_messages[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        .notification { margin-bottom: 10px; }
        .pfp { width: 30px; height: 30px; border-radius: 50%; }
        .username { font-weight: bold; margin-right: 5px; }
        .text { margin-right: 5px; }
        .timestamp { color: gray; font-size: 0.8em; }
    </style>
</head>
<body>
    <h1>Notifications</h1>

    <h2>Friend Requests</h2>
    <div id="friend-requests">
        <?php if (count($friend_requests) > 0): ?>
            <?php foreach ($friend_requests as $request): ?>
                <div class="notification">
                    <img src="path/to/pfp/<?php echo htmlspecialchars($request['pfp']); ?>" alt="Profile Picture" class="pfp">
                    <span class="username"><?php echo htmlspecialchars($request['username']); ?></span>
                    <button onclick="manageInvite(<?php echo $request['id']; ?>, 'accept')">Accept</button>
                    <button onclick="manageInvite(<?php echo $request['id']; ?>, 'reject')">Reject</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No friend requests.</p>
        <?php endif; ?>
    </div>

    <h2>Unread Messages</h2>
    <div id="unread-messages">
        <?php if (count($unread_messages) > 0): ?>
            <?php foreach ($unread_messages as $message): ?>
                <div class="notification">
                    <img src="path/to/pfp/<?php echo htmlspecialchars($message['pfp']); ?>" alt="Profile Picture" class="pfp">
                    <span class="username"><?php echo htmlspecialchars($message['username']); ?></span>
                    <span class="text"><?php echo htmlspecialchars($message['message']); ?></span>
                    <span class="timestamp"><?php echo htmlspecialchars($message['timestamp']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No unread messages.</p>
        <?php endif; ?>
    </div>

    <script>
        function manageInvite(inviteId, action) {
            fetch('manage_invites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `invite_id=${inviteId}&action=${action}`
            })
            .then(response => response.text())
            .then(data => {
                console.log('Response from manage_invites.php:', data); // Log the response
                location.reload(); // Reload the page to update the notifications
            })
            .catch(error => console.error('Error managing invite:', error));
        }
    </script>
</body>
</html>
