<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
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

echo json_encode($friends);

$stmt->close();
$conn->close();
?>
