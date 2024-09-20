<?php
include 'db_conn.php';
session_start();

if (isset($_SESSION['username'])) {
    echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!<br>";
    echo "<a href='logout.php'>Logout</a>";
} else {
?>
    <form method="POST" action="login.php">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
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
    <div id="messages-container"></div>
    <form id="message-form">
        <input type="text" id="message" required>
        <input type="hidden" id="friend-id" value="1"> <!-- Ensure this value is set correctly -->
        <button type="submit">Send</button>
    </form>

    <div>
        <input type="text" id="search" placeholder="Search users">
        <div id="search-results"></div>
    </div>

    <div>
        <a href="notifications.php">Notifications</a>
    </div>

    <div>
        <a href="friends_list.php">Friends List</a>
    </div>

    <script>
        function fetchMessages() {
            fetch('fetch_messages.php')
                .then(response => response.json())
                .then(data => {
                    const messagesContainer = document.getElementById('messages-container');
                    messagesContainer.innerHTML = ''; // Clear existing messages

                    data.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message');

                        const pfpElement = document.createElement('img');
                        pfpElement.src = message.pfp;
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
                            userElement.classList.add('user');

                            const pfpElement = document.createElement('img');
                            pfpElement.src = user.pfp;
                            pfpElement.alt = 'Profile Picture';
                            pfpElement.classList.add('pfp');

                            const usernameElement = document.createElement('span');
                            usernameElement.classList.add('username');
                            usernameElement.textContent = user.username;

                            const inviteButton = document.createElement('button');
                            inviteButton.textContent = 'Invite';
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

        function fetchFriends() {
            fetch('fetch_friends.php')
                .then(response => response.json())
                .then(data => {
                    const friendsList = document.getElementById('friends-list');
                    friendsList.innerHTML = ''; // Clear existing friends

                    data.forEach(friend => {
                        const friendElement = document.createElement('div');
                        friendElement.classList.add('friend');

                        const pfpElement = document.createElement('img');
                        pfpElement.src = friend.pfp;
                        pfpElement.alt = 'Profile Picture';
                        pfpElement.classList.add('pfp');

                        const usernameElement = document.createElement('span');
                        usernameElement.classList.add('username');
                        usernameElement.textContent = friend.username;

                        const chatButton = document.createElement('button');
                        chatButton.textContent = 'Private Chat';
                        chatButton.addEventListener('click', function() {
                            // Set the friend ID for private chat
                            document.getElementById('friend-id').value = friend.id;
                            // Optionally, you can redirect to a private chat page
                            // window.location.href = `private_chat.php?friend_id=${friend.id}`;
                        });

                        const videoChatButton = document.createElement('button');
                        videoChatButton.textContent = 'Video Chat';
                        videoChatButton.addEventListener('click', function() {
                            // Implement video chat functionality here
                            alert('Video chat with ' + friend.username);
                        });

                        friendElement.appendChild(pfpElement);
                        friendElement.appendChild(usernameElement);
                        friendElement.appendChild(chatButton);
                        friendElement.appendChild(videoChatButton);

                        friendsList.appendChild(friendElement);
                    });
                })
                .catch(error => console.error('Error fetching friends:', error));
        }

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
                fetchFriends(); // Refresh the friends list
            })
            .catch(error => console.error('Error managing invite:', error));
        }

        // Call fetchMessages initially to load messages
        fetchMessages();

        // Call fetchFriends initially to load friends list
        fetchFriends();

        // Optionally, set an interval to refresh messages and friends list periodically
        setInterval(fetchMessages, 5000);
        setInterval(fetchFriends, 10000);
    </script>
</body>
</html>