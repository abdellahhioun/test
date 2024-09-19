<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invite_id = $_POST['invite_id'];
    $action = $_POST['action']; // 'accept' or 'reject'

    if ($action == 'accept') {
        $stmt = $conn->prepare("UPDATE invitations SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $invite_id);
        $stmt->execute();

        // Add to friends table
        $stmt = $conn->prepare("SELECT sender_id, receiver_id FROM invitations WHERE id = ?");
        $stmt->bind_param("i", $invite_id);
        $stmt->execute();
        $stmt->bind_result($sender_id, $receiver_id);
        $stmt->fetch();

        // Add friendship in both directions
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?), (?, ?)");
        $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
        $stmt->execute();
    } else if ($action == 'reject') {
        $stmt = $conn->prepare("UPDATE invitations SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $invite_id);
        $stmt->execute();
    }

    echo "Invitation $action successfully!";
    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
