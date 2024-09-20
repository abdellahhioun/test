<?php
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inviteId = $_POST['invite_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        // Update the invite status to accepted
        $stmt = $conn->prepare("UPDATE invitations SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $inviteId);
        $stmt->execute();

        // Fetch the sender and receiver IDs from the invite
        $stmt = $conn->prepare("SELECT sender_id, receiver_id FROM invitations WHERE id = ?");
        $stmt->bind_param("i", $inviteId);
        $stmt->execute();
        $stmt->bind_result($senderId, $receiverId);
        $stmt->fetch();
        $stmt->close();

        // Insert the friendship into the friends table
        $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?), (?, ?)");
        $stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'reject') {
        // Update the invite status to rejected
        $stmt = $conn->prepare("UPDATE invitations SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $inviteId);
        $stmt->execute();
        $stmt->close();
    }

    echo "Invite $action successfully.";
} else {
    echo "Invalid request method.";
}
?>
