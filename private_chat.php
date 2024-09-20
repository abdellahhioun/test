<?php
include 'db_conn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$friend_id = $_GET['friend_id'] ?? null;

if (!$friend_id) {
    echo "No friend specified.";
    exit;
}

// Fetch friend's username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $friend_id);
$stmt->execute();
$stmt->bind_result($friend_username);
$stmt->fetch();
$stmt->close();

if (!$friend_username) {
    echo "Friend not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($friend_username); ?></title>
    <style>
        .message { margin-bottom: 10px; }
        .pfp { width: 30px; height: 30px; border-radius: 50%; }
        .username { font-weight: bold; margin-right: 5px; }
        .text { margin-right: 5px; }
        .timestamp { color: gray; font-size: 0.8em; }
    </style>
</head>
<body>
    <h1>Chat with <?php echo htmlspecialchars($friend_username); ?></h1>
    <div id="messages-container"></div>
    <form id="message-form">
        <input type="text" id="message" required>
        <input type="hidden" id="friend-id" value="<?php echo htmlspecialchars($friend_id); ?>">
        <button type="submit">Send</button>
    </form>

    <script>
        function fetchMessages() {
            const friendId = document.getElementById('friend-id').value;
            fetch(`fetch_private_messages.php?friend_id=${friendId}`)
                .then(response => response.json())
                .then(data => {
                    const messagesContainer = document.getElementById('messages-container');
                    messagesContainer.innerHTML = ''; // Clear existing messages

                    data.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message');

                        const pfpElement = document.createElement('img');
                        pfpElement.src = `assets/default.png`;
                        pfpElement.alt = 'Profile Picture';
                        pfpElement.classList.add('pfp');

                        const usernameElement = document.createElement('span');
                        usernameElement.classList.add('username');
                        usernameElement.textContent = message.username;

                        const textElement = document.createElement('span');
                        textElement.classList.add('text');
                        textElement.textContent = message.message;

                        const timestampElement = document.createElement('span');
                        timestampElement.classList.add('timestamp');
                        timestampElement.textContent = message.timestamp;

                        messageElement.appendChild(pfpElement);
                        messageElement.appendChild(usernameElement);
                        messageElement.appendChild(textElement);
                        messageElement.appendChild(timestampElement);

                        messagesContainer.appendChild(messageElement);
                    });
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const message = document.getElementById('message').value;
            const friendId = document.getElementById('friend-id').value;

            fetch('send_private_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${message}&friend_id=${friendId}`
            })
            .then(response => response.text())
            .then(data => {
                console.log('Response from send_private_message.php:', data); // Log the response
                document.getElementById('message').value = ''; // Clear the input field
                fetchMessages(); // Refresh the messages
            })
            .catch(error => console.error('Error sending message:', error));
        });

        // Call fetchMessages initially to load messages
        fetchMessages();

        // Optionally, set an interval to refresh messages periodically
        setInterval(fetchMessages, 5000);
    </script>
</body>
</html>
