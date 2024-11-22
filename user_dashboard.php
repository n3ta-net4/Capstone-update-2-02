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
            <a href="notifications.php" class="btn-grooming-notifications">Notifications</a>
            <a href="logout.php" class="btn-grooming-logout">Logout</a>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="stats-card">
                <i class="fas fa-calendar-check"></i>
                <div class="stats-info">
                    <h3>Upcoming Appointments</h3>
                    <p>2</p>
                </div>
            </div>
            <div class="stats-card">
                <i class="fas fa-history"></i>
                <div class="stats-info">
                    <h3>Past Bookings</h3>
                    <p>8</p>
                </div>
            </div>
            <div class="stats-card">
                <i class="fas fa-paw"></i>
                <div class="stats-info">
                    <h3>Registered Pets</h3>
                    <p>3</p>
                </div>
            </div>

            <div class="dashboard-section recent-bookings">
                <h2>Recent Bookings</h2>
                <div class="booking-list">
                    <div class="booking-item">
                        <div class="booking-info">
                            <h4>Pet Grooming</h4>
                            <p>Date: Oct 15, 2024</p>
                            <p>Status: Confirmed</p>
                        </div>
                    </div>
                    <div class="booking-item">
                        <div class="booking-info">
                            <h4>Pet Boarding</h4>
                            <p>Date: Oct 10-12, 2024</p>
                            <p>Status: Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-section announcements">
                <h2>Announcements</h2>
                <div class="announcement-item">
                    <h4>Holiday Schedule</h4>
                    <p>We will be operating with special hours during the upcoming holiday season.</p>
                    <small>Posted: Oct 1, 2024</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
