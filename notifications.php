<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

$friend_requests = [];
while ($row = $result->fetch_assoc()) {
    $friend_requests[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Notifications</h1>
        <ul class="list-group">
            <?php foreach ($friend_requests as $request): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <img src="<?php echo htmlspecialchars($request['pfp']); ?>" alt="Profile Picture" class="rounded-circle" style="width: 30px; height: 30px;">
                    <span><?php echo htmlspecialchars($request['username']); ?></span>
                    <div>
                        <button class="btn btn-success btn-sm" onclick="manageInvite(<?php echo $request['id']; ?>, 'accept')">Accept</button>
                        <button class="btn btn-danger btn-sm" onclick="manageInvite(<?php echo $request['id']; ?>, 'reject')">Reject</button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function manageInvite(inviteId, action) {
            fetch('manage_invites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `invite_id=${inviteId}&action=${action}`
            })
            .then(response => response.text())
            .then(data => {
                console.log('Response from manage_invites.php:', data); // Log the response
                location.reload(); // Reload the page to update the notifications
            })
            .catch(error => console.error('Error managing invite:', error));
        }
    </script>
</body>
</html>
