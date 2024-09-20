<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$search = $_GET['search'] ?? '';

$stmt = $conn->prepare("SELECT id, username, COALESCE(pfp, 'assets/default.png') AS pfp FROM users WHERE username LIKE ? AND id != ?");
$searchTerm = '%' . $search . '%';
$user_id = $_SESSION['user_id'];
$stmt->bind_param("si", $searchTerm, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);

$stmt->close();
$conn->close();
?>
