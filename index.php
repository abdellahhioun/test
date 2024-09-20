<?php
include 'db_conn.php';
session_start();

if (isset($_SESSION['username'])) {
    echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!<br>";
    echo "<a href='logout.php' class='btn btn-danger'>Logout</a>";
} else {
?>
    <form method="POST" action="login.php" class="form-inline">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .message { margin-bottom: 10px; }
        .pfp { width: 30px; height: 30px; border-radius: 50%; }
        .username { font-weight: bold; margin-right: 5px; }
        .text { margin-right: 5px; }
        .timestamp { color: gray; font-size: 0.8em; }
        #search-results { display: none; } /* Hide search results container initially */
    </style>
</head>
<body>
    <div class="container mt-4">
        <div id="messages-container" class="mb-4"></div>
        <form id="message-form" class="form-inline">
            <input type="text" id="message" class="form-control mr-2" required>
            <input type="hidden" id="friend-id" value="1"> <!-- Ensure this value is set correctly -->
            <button type="submit" class="btn btn-primary">Send</button>
        </form>

        <div class="mt-4">
            <input type="text" id="search" class="form-control" placeholder="Search users">
            <div id="search-results" class="mt-2"></div>
        </div>

        <div class="mt-4">
            <a href="notifications.php" class="btn btn-info">Notifications</a>
        </div>

        <div class="mt-4">
            <a href="friends_list.php" class="btn btn-info">Friends List</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function fetchMessages() {
            fetch('fetch_messages.php')
                .then(response => response.json())
                .then(data => {
                    const messagesContainer = document.getElementById('messages-container');
                    messagesContainer.innerHTML = ''; // Clear existing messages

                    data.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message', 'border', 'p-2', 'mb-2', 'rounded');

                        const pfpElement = document.createElement('img');
                        pfpElement.src = message.pfp;
                        pfpElement.alt = 'Profile Picture';
                        pfpElement.classList.add('pfp', 'mr-2');

                        const usernameElement = document.createElement('span');
                        usernameElement.classList.add('username');
                        usernameElement.textContent = message.username;

                        const textElement = document.createElement('span');
                        textElement.classList.add('text');
                        textElement.textContent = message.message;

                        const timestampElement = document.createElement('span');
                        timestampElement.classList.add('timestamp');
                        timestampElement.textContent = new Date(message.timestamp).toLocaleString();

                        messageElement.appendChild(pfpElement);
                        messageElement.appendChild(usernameElement);
                        messageElement.appendChild(textElement);
                        messageElement.appendChild(timestampElement);

                        messagesContainer.appendChild(messageElement);
                    });
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        document.getElementById('message-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const message = document.getElementById('message').value;
            const friendId = document.getElementById('friend-id').value;

            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${message}&friend_id=${friendId}`
            })
            .then(response => response.text())
            .then(data => {
                console.log('Response from send_message.php:', data); // Log the response
                document.getElementById('message').value = '';
                fetchMessages();
            })
            .catch(error => console.error('Error sending message:', error));
        });

        document.getElementById('search').addEventListener('input', function() {
            const search = document.getElementById('search').value;
            const searchResults = document.getElementById('search-results');

            if (search.trim() === '') {
                searchResults.innerHTML = ''; // Clear existing results
                searchResults.style.display = 'none'; // Hide the search results container
                return;
            }

            fetch(`search_users.php?search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = ''; // Clear existing results

                    if (data.length > 0) {
                        searchResults.style.display = 'block'; // Show the search results container
                        data.forEach(user => {
                            const userElement = document.createElement('div');
                            userElement.classList.add('user', 'border', 'p-2', 'mb-2', 'rounded');

                            const pfpElement = document.createElement('img');
                            pfpElement.src = user.pfp;
                            pfpElement.alt = 'Profile Picture';
                            pfpElement.classList.add('pfp', 'mr-2');

                            const usernameElement = document.createElement('span');
                            usernameElement.classList.add('username');
                            usernameElement.textContent = user.username;

                            const inviteButton = document.createElement('button');
                            inviteButton.textContent = 'Invite';
                            inviteButton.classList.add('btn', 'btn-primary', 'ml-2');
                            inviteButton.addEventListener('click', function() {
                                fetch('send_invite.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `receiver_id=${user.id}`
                                })
                                .then(response => response.text())
                                .then(data => {
                                    console.log('Response from send_invite.php:', data); // Log the response
                                    searchResults.innerHTML = ''; // Clear existing results
                                    searchResults.style.display = 'none'; // Hide the search results container
                                })
                                .catch(error => console.error('Error sending invite:', error));
                            });

                            userElement.appendChild(pfpElement);
                            userElement.appendChild(usernameElement);
                            userElement.appendChild(inviteButton);

                            searchResults.appendChild(userElement);
                        });
                    } else {
                        searchResults.style.display = 'none'; // Hide the search results container if no users found
                    }
                })
                .catch(error => console.error('Error searching users:', error));
        });

        // Call fetchMessages initially to load messages
        fetchMessages();

        // Optionally, set an interval to refresh messages periodically
        setInterval(fetchMessages, 5000);
    </script>
</body>
</html>