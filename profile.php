<?php
require 'db_conn.php';
session_start();

$username = $_GET['username'] ?? '';
$currentUser = $_SESSION['user_id'] ?? null;

if ($username) {
    $stmt = $conn->prepare("SELECT id, username, COALESCE(pfp, 'assets/default.png') AS pfp FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($userId, $userUsername, $userPfp);
    $stmt->fetch();
    $stmt->close();

    if (!$userUsername) {
        echo "User not found.";
        exit;
    }
} else {
    echo "No username specified.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $currentUser == $userId) {
    $newUsername = $_POST['username'] ?? $userUsername;
    $newPfp = $userPfp;

    if (isset($_FILES['pfp']) && $_FILES['pfp']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $uploadFile = $uploadDir . basename($_FILES['pfp']['name']);
        if (move_uploaded_file($_FILES['pfp']['tmp_name'], $uploadFile)) {
            $newPfp = $uploadFile;
        } else {
            echo "Failed to move uploaded file.";
        }
    } else {
        echo "No file uploaded or upload error.";
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, pfp = ? WHERE id = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("ssi", $newUsername, $newPfp, $userId);

    if ($stmt->execute()) {
        // Update session data
        $_SESSION['username'] = $newUsername;
        $_SESSION['pfp'] = $newPfp;
        
        echo "Profile updated successfully!";
        header("Location: profile.php?username=" . urlencode($newUsername));
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($userUsername); ?>'s Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($userUsername); ?>'s Profile</h1>
        <img src="<?php echo htmlspecialchars($userPfp); ?>" alt="Profile Picture" class="rounded-circle" style="width: 100px; height: 100px;">
        <p>Username: <?php echo htmlspecialchars($userUsername); ?></p>

        <?php if ($currentUser == $userId): ?>
            <h2>Edit Profile</h2>
            <form action="profile.php?username=<?php echo htmlspecialchars($userUsername); ?>" method="post" enctype="multipart/form-data" class="form-inline">
                <div class="form-group">
                    <label for="username">New Username:</label>
                    <input type="text" name="username" id="username" class="form-control ml-2" value="<?php echo htmlspecialchars($userUsername); ?>">
                </div>
                <div class="form-group ml-2">
                    <label for="pfp">New Profile Picture:</label>
                    <input type="file" name="pfp" id="pfp" class="form-control ml-2">
                </div>
                <button type="submit" class="btn btn-primary ml-2">Update Profile</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
