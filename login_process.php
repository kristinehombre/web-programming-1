<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


include 'config.php';

$message = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    
    if (empty($username) || empty($password)) {
        $message = "Username and Password cannot be empty.";
    } else {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        
        if ($user && password_verify($password, $user['password'])) {
           
            $_SESSION['user_id'] = $user['id'];
            header('Location: todo.php'); 
            exit();
        } else {
            $message = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Login - ToDo App</title>
</head>
<body>
    <div class="container">
        <h1>Welcome to ToDo App!</h1>
        <h2>Login to Your Account</h2>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Enter username" required>
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
