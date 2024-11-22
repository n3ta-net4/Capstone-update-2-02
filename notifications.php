<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/notifications.css">
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
            <li><a href="user_information.php"><i class="fas fa-user"></i>User Information</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Notifications</h1>
            <div>
                <a href="notifications.php" class="btn-grooming-notifications active">Notifications</a>
                <a href="logout.php" class="btn-grooming-logout">Logout</a>
            </div>
        </div>
        
        <div class="notifications-container">
            <?php
            $stmt = $pdo->prepare('
                SELECT n.*, 
                       COALESCE(a.appointment_date, "") as appointment_date, 
                       COALESCE(a.appointment_time, "") as appointment_time,
                       COALESCE(a.status, "") as appointment_status 
                FROM notifications n 
                LEFT JOIN appointments a ON n.appointment_id = a.id 
                WHERE n.user_id = ? 
                ORDER BY n.created_at DESC
            ');
            $stmt->execute([$user['id']]);
            $notifications = $stmt->fetchAll();

            if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['type']; ?> <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                        <div class="notification-content">
                            <?php echo htmlspecialchars($notification['message']); ?>
                            <?php if ($notification['type'] == 'appointment' && !empty($notification['appointment_date'])): ?>
                                <div class="appointment-details">
                                    <span>Date: <?php echo htmlspecialchars($notification['appointment_date']); ?></span>
                                    <span>Time: <?php echo htmlspecialchars($notification['appointment_time']); ?></span>
                                    <span class="status-<?php echo $notification['appointment_status']; ?>">
                                        Status: <?php echo ucfirst(htmlspecialchars($notification['appointment_status'])); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="notification-time">
                            <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-notifications">You have no notifications at this time.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
