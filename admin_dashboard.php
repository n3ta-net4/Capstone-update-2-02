<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <a href="admin_dashboard.php">
            <img src="css/aw-k9.png" alt="aw-k9 logo">
        </a>
    </div>
    <h2>Admin Dashboard</h2>
    <hr>
    <ul>
        <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="admin_appointments.php"><i class="fas fa-calendar-check"></i> Pending Appointments</a></li>
        <li><a href="admin_bookings.php"><i class="fas fa-paw"></i> Pending Reservations</a></li>
        <li><a href="admin_manage_appointments.php"><i class="fas fa-calendar"></i> Manage Appointments</a></li>
        <li><a href="admin_manage_reservations.php"><i class="fas fa-hotel"></i> Manage Reservations</a></li>
        <li><a href="admin_services.php"><i class="fas fa-bone"></i> Manage Services</a></li>
        <li><a href="admin_boarding_rates.php"><i class="fas fa-dollar-sign" style="width: 16px; text-align: center;"></i> Manage Rates</a></li>
        <li><a href="admin_feedback.php"><i class="fas fa-comments"></i> Manage Feedback</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="top-bar">
        <h1>Welcome, <?php echo htmlspecialchars($admin['name']); ?></h1>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
    <hr> 
</div>
</body>
</html>
