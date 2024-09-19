<?php
require 'db_conn.php';

$username = $_GET['username'] ?? '';

if ($username) {
    $sql = "SELECT username, pfp FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit;
    }
} else {
    echo "No username specified.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
</head>
<body>
    <h1><?php echo htmlspecialchars($user['username']); ?>'s Profile</h1>
    <img src="<?php echo htmlspecialchars($user['pfp']); ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%;">
    <p>Username: <?php echo htmlspecialchars($user['username']); ?></p>
</body>
</html>
