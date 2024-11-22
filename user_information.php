<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userDetails = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    $error_message = [];
    $success_message = '';
    
    if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
        if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $error_message[] = "All password fields are required for password change";
        } else if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error_message[] = "New password and confirmation do not match";
        } else if (strlen($_POST['new_password']) < 8) {
            $error_message[] = "New password must be at least 8 characters long";
        } else {
            if (password_verify($_POST['current_password'], $userDetails['password'])) {
                $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $address, $new_password_hash, $user['id']]);
                $success_message = "Information updated successfully with new password";
            } else {
                $error_message[] = "Current password is incorrect";
            }
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $user['id']]);
        $success_message = "Information updated successfully";
    }

    if (empty($error_message)) {
        $_SESSION['user']['name'] = $name;
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userDetails = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/user_information.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <a href="user_dashboard.php">
            <img src="css/aw-k9.png" alt="aw-k9 logo">
        </a>
    </div>
    <div class="user-details">
        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
    </div>
    <div class="divider"></div>
    <ul>
        <li><a href="user_dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
        <li><a href="booking_calendar.php"><i class="fas fa-calendar-alt"></i>Book Pet Grooming</a></li>
        <li><a href="book_pet_boarding.php"><i class="fas fa-hotel"></i>Book Pet Hotel</a></li>
        <li><a href="services.php"><i class="fas fa-list"></i>Services & Prices</a></li>
        <li><a href="pet_boarding_rates.php"><i class="fas fa-money-bill"></i>Pet Boarding Rates</a></li>
        <li><a href="feedback.php"><i class="fas fa-comments"></i>Feedback & Reviews</a></li>
        <li><a href="user_information.php" class="active"><i class="fas fa-user"></i>User Information</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="top-bar">
        <h1>User Information</h1>
        <div>
            <a href="notifications.php">
            <button class="btn-notifications">Notifications</button></a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="user-form">
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php 
                if (is_array($error_message)) {
                    foreach ($error_message as $error) {
                        echo htmlspecialchars($error) . "<br>";
                    }
                } else {
                    echo htmlspecialchars($error_message);
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userDetails['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userDetails['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userDetails['phone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($userDetails['address']); ?>" required>
            </div>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit" class="btn-update">Update Information</button>
        </form>
    </div>
</div>

</body>
</html>
