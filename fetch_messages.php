<?php
include 'db_conn.php';
session_start();

$query = "SELECT messages.message, messages.timestamp, users.username, users.pfp 
          FROM messages 
          JOIN users ON messages.user_id = users.id 
          ORDER BY messages.timestamp DESC";

$result = $conn->query($query);

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

echo json_encode($messages);

$conn->close();
?>
