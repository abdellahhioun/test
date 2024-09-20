<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['message']) && isset($_POST['friend_id'])) {
        $user_id = $_SESSION['user_id'];
        $friend_id = $_POST['friend_id'];
        $message = $_POST['message'];

        $stmt = $conn->prepare("INSERT INTO messages (user_id, friend_id, message, `read`, timestamp) VALUES (?, ?, ?, 0, NOW())");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("iis", $user_id, $friend_id, $message);

        if ($stmt->execute()) {
            echo "Message sent successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Message content or friend ID missing.";
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
