<?php
include 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare('UPDATE users SET is_verified = TRUE WHERE verification_token = ?');
    if ($stmt->execute([$token])) {
        $success = "Email verified successfully! You can now login.";
    } else {
        $error = "Invalid verification token!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="css/verify.css">
</head>
<body>
    <div class="auth-container">
        <h2>Email Verification</h2>
        <?php if (isset($success)): ?>
            <p style="color: green; margin-bottom: 20px;"><?php echo $success; ?></p>
            <p><a href="login.php">Proceed to Login</a></p>
        <?php elseif (isset($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>