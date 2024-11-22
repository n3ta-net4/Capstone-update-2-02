<?php
include 'db.php';
include 'mail_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    if (strlen($_POST['password']) < 8) {
        $error = "Password must be at least 8 characters long!";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = 'user';
        $verification_token = bin2hex(random_bytes(32));
        
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Email already registered!";
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password, role, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, ?, FALSE)');
            if ($stmt->execute([$name, $email, $phone, $password, $role, $verification_token])) {
                $mailResult = sendVerificationEmail($email, $name, $verification_token);
                
                if ($mailResult === true) {
                    $success = "Registration successful! Please check your email to verify your account.";
                } else {
                    $error = "Registration successful but failed to send verification email: " . $mailResult;
                }
            } else {
                $error = "Registration failed!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
        <button class="back-button" onclick="window.location.href='main.php';">
            <img src="css/dog-paw.png" alt="back_button">
        </button>
        <span class="back-text">Click paw to go back</span>
<div class="auth-container">
    <h2>Register</h2>
    <?php if (isset($error)): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <p style="color: green; margin-bottom: 20px;"><?php echo $success; ?></p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="tel" name="phone" placeholder="Mobile Phone Number" required>
        <input type="password" name="password" placeholder="Password" required minlength="8">
        <button class="sub-button" button type="submit">Register</button><br><br>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>
</body>
</html>
