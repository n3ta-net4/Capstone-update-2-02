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
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/user_dashboard.css">
</head>
<body>
    <button class="hamburger-menu" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

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
            <li><a href="user_dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
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
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
            <div>
                <button class="btn-notifications" onclick="toggleNotifications()">Notifications</button>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <div class="content-section">
            <p>You are logged in as <strong><?php echo htmlspecialchars($user['email']); ?></strong>.</p>
        </div>
        
    </div>
    
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
        
        function toggleNotifications() {
            document.querySelector('.notifications').classList.toggle('show');
        }
    </script>
</body>
</html>
