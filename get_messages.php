<?php
require 'db_conn.php';

$sql = "SELECT username, message, profile_pic, timestamp FROM messages ORDER BY timestamp DESC";
$result = $conn->query($sql);

$messages = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

echo json_encode($messages);

$conn->close();
?>
