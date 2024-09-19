<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
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

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'type' => 'friend_request',
        'data' => $row
    ];
}

// Fetch unread messages (this part assumes you have a way to track read/unread messages)
$stmt = $conn->prepare("SELECT messages.message, messages.timestamp, users.username, users.pfp 
                        FROM messages 
                        JOIN users ON messages.user_id = users.id 
                        WHERE messages.friend_id = ? AND messages.read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'type' => 'message',
        'data' => $row
    ];
}

echo json_encode($notifications);

$stmt->close();
$conn->close();
?>
