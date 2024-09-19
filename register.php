<?php
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate username and password
    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
        exit;
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Username already taken.";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Hash the password with password_hash
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $pfp = 'default.png'; // Default profile picture

    $stmt = $conn->prepare("INSERT INTO users (username, password, pfp) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $pfp);

    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: login.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<form method="POST" action="register.php">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Register</button>
</form>
