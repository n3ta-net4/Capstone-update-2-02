<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user = $_SESSION['user'];

$stmt = $pdo->query("SELECT * FROM grooming_services ORDER BY category, service_name");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groupedServices = [];
foreach ($services as $service) {
    $groupedServices[$service['category']][] = $service;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grooming Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/services.css">
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
            <li><a href="services.php" class="active"><i class="fas fa-list"></i>Services & Prices</a></li>
            <li><a href="pet_boarding_rates.php"><i class="fas fa-money-bill"></i>Pet Boarding Rates</a></li>
            <li><a href="feedback.php"><i class="fas fa-comments"></i>Feedback & Reviews</a></li>
            <li><a href="user_information.php"><i class="fas fa-user"></i>User Information</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Grooming Services</h1>
            <div>
                <button class="btn-notifications">Notifications</button>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        <div class="services-container">
            <?php foreach ($groupedServices as $category => $categoryServices): ?>
            <div class="services-category">
                <h2 class="category-title"><?php echo htmlspecialchars($category); ?> Services</h2>
                <div class="services-grid">
                    <?php foreach ($categoryServices as $service): ?>
                    <div class="service-card">
                        <h3 class="service-name"><?php echo htmlspecialchars($service['service_name']); ?></h3>
                        <ul class="price-list">
                            <?php if (isset($service['fixed_price'])): ?>
                                <li class="price-item">
                                    <span class="price-label">Price</span>
                                    <span class="price-value">₱<?php echo number_format($service['fixed_price'], 2); ?></span>
                                </li>
                            <?php else: ?>
                                <li class="price-item">
                                    <span class="price-label">Small</span>
                                    <span class="price-value">₱<?php echo number_format($service['small_price'], 2); ?></span>
                                </li>
                                <li class="price-item">
                                    <span class="price-label">Medium</span>
                                    <span class="price-value">₱<?php echo number_format($service['medium_price'], 2); ?></span>
                                </li>
                                <li class="price-item">
                                    <span class="price-label">Large</span>
                                    <span class="price-value">₱<?php echo number_format($service['large_price'], 2); ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <?php if (isset($service['description'])): ?>
                            <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>