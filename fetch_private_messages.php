<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_GET['friend_id'] ?? null;

if (!$friend_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT messages.message, messages.timestamp, users.username, users.pfp 
                        FROM messages 
                        JOIN users ON messages.user_id = users.id 
                        WHERE (messages.user_id = ? AND messages.friend_id = ?) 
                           OR (messages.user_id = ? AND messages.friend_id = ?)
                        ORDER BY messages.timestamp ASC");
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?>
