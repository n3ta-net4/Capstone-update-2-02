<?php
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['user', 'admin'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT DISTINCT pet_size FROM boarding_rates ORDER BY 
    CASE 
        WHEN pet_size = 'Small' THEN 1 
        WHEN pet_size = 'Medium' THEN 2 
        WHEN pet_size = 'Large' THEN 3 
    END");
$stmt->execute();
$sizes = $stmt->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Boarding Rates</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/pet_boarding_rates.css">
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
            <li><a href="pet_boarding_rates.php" class="active"><i class="fas fa-money-bill"></i>Pet Boarding Rates</a></li>
            <li><a href="feedback.php"><i class="fas fa-comments"></i>Feedback & Reviews</a></li>
            <li><a href="user_information.php"><i class="fas fa-user"></i>User Information</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Pet Boarding Rates</h1>
            <div>
                <button class="btn-notifications">Notifications</button>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        
        <div class="rates-container">
            <?php foreach ($sizes as $size): ?>
                <div class="rates-category">
                    <h2 class="category-title">
                        <?php 
                        $sizeRange = '';
                        switch($size) {
                            case 'Small':
                                $sizeRange = '(1 - 5 KGS)';
                                break;
                            case 'Medium':
                                $sizeRange = '(6 - 12 KGS)';
                                break;
                            case 'Large':
                                $sizeRange = '(13 KGS above)';
                                break;
                        }
                        echo $size . " Pets " . $sizeRange . " Boarding Rates";
                        ?>
                    </h2>
                    <div class="rates-grid">
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM boarding_rates WHERE pet_size = ? ORDER BY accommodation_type, rate DESC");
                        $stmt->execute([$size]);
                        $rates = $stmt->fetchAll();
                        
                        $groupedRates = [];
                        foreach ($rates as $rate) {
                            $groupedRates[$rate['accommodation_type']][] = $rate;
                        }
                        
                        foreach ($groupedRates as $type => $typeRates):
                        ?>
                            <div class="rate-card">
                                <h3 class="accommodation-type"><?php echo $type; ?></h3>
                                <?php foreach ($typeRates as $rate): ?>
                                    <div class="rate-row">
                                        <span><?php echo $rate['days_range']; ?></span>
                                        <span>₱<?php echo number_format($rate['rate'], 2); ?>/Day</span>
                                    </div>
                                <?php endforeach; ?>
                                <div class="inclusions">
                                    <strong>Inclusions:</strong><br>
                                    <?php echo $typeRates[0]['inclusions']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
