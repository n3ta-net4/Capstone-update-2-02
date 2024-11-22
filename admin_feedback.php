<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $pdo->query("SELECT id, user_name, rating, comment, created_at, image_path, services FROM feedback ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $reviews = [];
    $_SESSION['error'] = "Error fetching reviews: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_feedback.css">
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
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_appointments.php"><i class="fas fa-calendar-check"></i> Pending Appointments</a></li>
            <li><a href="admin_bookings.php"><i class="fas fa-paw"></i> Pending Reservations</a></li>
            <li><a href="admin_manage_appointments.php"><i class="fas fa-calendar"></i> Manage Appointments</a></li>
            <li><a href="admin_manage_reservations.php"><i class="fas fa-hotel"></i> Manage Reservations</a></li>
            <li><a href="admin_services.php"><i class="fas fa-bone"></i> Manage Services</a></li>
            <li><a href="admin_boarding_rates.php"><i class="fas fa-dollar-sign" style="width: 16px; text-align: center;"></i> Manage Rates</a></li>
            <li><a href="admin_feedback.php" class="active"><i class="fas fa-comments"></i> Manage Feedback</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Manage Customer Feedback</h1>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <hr> 

        <div class="feedback-list">
            <?php foreach ($reviews as $review): ?>
                <div class="feedback-card">
                    <form action="delete_feedback.php" method="POST">
                        <input type="hidden" name="feedback_id" value="<?php echo $review['id']; ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this feedback?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    
                    <h3><?php echo htmlspecialchars($review['user_name']); ?></h3>
                    <div class="star-rating">
                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <?php if (!empty($review['services'])): ?>
                        <div class="services-tags">
                            <?php 
                            $services = json_decode($review['services'], true);
                            foreach ($services as $service): 
                                $serviceName = $service === 'pet_grooming' ? 'Pet Grooming' : 'Pet Hotel';
                            ?>
                                <span><?php echo $serviceName; ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($review['comment']); ?></p>
                    <?php if (!empty($review['image_path'])): ?>
                        <div class="review-image">
                            <img src="<?php echo htmlspecialchars($review['image_path']); ?>" alt="Review image">
                        </div>
                    <?php endif; ?>
                    <small style="color: #666;">Posted on: <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>