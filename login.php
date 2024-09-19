<?php
include 'db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate username and password
    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username, password, pfp FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashed_password, $pfp);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['pfp'] = $pfp;
            echo "Login successful!";
            header("Location: index.php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that username.";
    }

    $stmt->close();
    $conn->close();
}

if (isset($_SESSION['username'])) {
    echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!<br>";
    echo "<img src='path/to/pfp/" . htmlspecialchars($_SESSION['pfp']) . "' alt='Profile Picture'>";
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
