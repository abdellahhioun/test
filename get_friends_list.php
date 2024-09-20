<?php
include 'db_conn.php';

session_start();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT u.id, u.username, COALESCE(u.pfp, 'assets/default.png') AS pfp FROM friends f JOIN users u ON f.friend_id = u.id WHERE f.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($friends);
?>
