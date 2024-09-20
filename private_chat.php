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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .message { margin-bottom: 10px; }
        .pfp { width: 30px; height: 30px; border-radius: 50%; }
        .username { font-weight: bold; margin-right: 5px; }
        .text { margin-right: 5px; }
        .timestamp { color: gray; font-size: 0.8em; }
        #localVideo, #remoteVideo {
            width: 100%;
            max-width: 300px;
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Chat with <?php echo htmlspecialchars($friend_username); ?></h1>
        <div id="messages-container" class="mb-4"></div>
        <form id="message-form" class="form-inline">
            <input type="text" id="message" class="form-control mr-2" required>
            <input type="hidden" id="friend-id" value="<?php echo htmlspecialchars($friend_id); ?>">
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
        <div class="mt-4">
            <h2>Video Chat</h2>
            <video id="localVideo" autoplay muted></video>
            <video id="remoteVideo" autoplay></video>
            <button id="startVideoChat" class="btn btn-success mt-2">Start Video Chat</button>
            <button id="hangUp" class="btn btn-danger mt-2">Hang Up</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const message = document.getElementById('message').value;
            const friendId = document.getElementById('friend-id').value;

            fetch('send_private_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}&friend_id=${encodeURIComponent(friendId)}`
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

        // WebRTC Video Chat
        const signalingServerUrl = 'ws://localhost:8080';
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const startVideoChatButton = document.getElementById('startVideoChat');
        let localStream;
        let peerConnection;
        const configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' }
            ]
        };

        startVideoChatButton.addEventListener('click', startVideoChat);

        function startVideoChat() {
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(stream => {
                    localVideo.srcObject = stream;
                    localStream = stream;

                    peerConnection = new RTCPeerConnection(configuration);
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                    peerConnection.ontrack = event => {
                        remoteVideo.srcObject = event.streams[0];
                    };

                    peerConnection.onicecandidate = event => {
                        if (event.candidate) {
                            signalingServer.send(JSON.stringify({ type: 'candidate', candidate: event.candidate }));
                        }
                    };

                    peerConnection.createOffer()
                        .then(offer => {
                            peerConnection.setLocalDescription(offer);
                            signalingServer.send(JSON.stringify({ type: 'offer', offer: offer }));
                        });

                    signalingServer.onmessage = message => {
                        const data = JSON.parse(message.data);
                        if (data.type === 'offer') {
                            peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
                            peerConnection.createAnswer()
                                .then(answer => {
                                    peerConnection.setLocalDescription(answer);
                                    signalingServer.send(JSON.stringify({ type: 'answer', answer: answer }));
                                });
                        } else if (data.type === 'answer') {
                            peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
                        } else if (data.type === 'candidate') {
                            peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                        }
                    };
                })
                .catch(error => console.error('Error accessing media devices.', error));
        }

        const signalingServer = new WebSocket(signalingServerUrl);

        const hangUpButton = document.getElementById('hangUp');

        hangUpButton.addEventListener('click', hangUp);

        function hangUp() {
            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
            localVideo.srcObject = null;
            remoteVideo.srcObject = null;
            signalingServer.close();
            console.log('Call ended.');
        }
    </script>
</body>
</html>
