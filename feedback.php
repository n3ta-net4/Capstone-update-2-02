<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

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
    <title>Feedback & Reviews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/feedback.css">
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
            <li><a href="feedback.php" class="active"><i class="fas fa-comments"></i>Feedback & Reviews</a></li>
            <li><a href="user_information.php"><i class="fas fa-user"></i>User Information</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Feedback & Reviews</h1>
            <div>
                <button class="btn-notifications">Notifications</button>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
        <div class="feedback-stats">
            <div class="stat-card">
                <h3>Total Reviews</h3>
                <div class="number"><?php echo count($reviews); ?></div>
            </div>
            <div class="stat-card">
                <h3>Average Rating</h3>
                <div class="number">
                    <?php 
                    $avgRating = count($reviews) > 0 ? 
                        number_format(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0;
                    echo $avgRating;
                    ?>
                    <i class="fas fa-star" style="color: #f1c40f;"></i>
                </div>
            </div>
        </div>

        <div class="feedback-section">
            <h2>Leave Your Feedback</h2>
            <form class="feedback-form" action="submit_feedback.php" method="POST" enctype="multipart/form-data">
                <div class="rating-input">
                    <label>Rating:</label>
                    <select name="rating" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div class="services-input" style="margin: 10px 0;">
                    <label>Services Reviewed:</label>
                    <div style="margin-top: 5px;">
                        <label style="margin-right: 15px;">
                            <input type="checkbox" name="services[]" value="pet_grooming"> Pet Grooming
                        </label>
                        <label>
                            <input type="checkbox" name="services[]" value="pet_hotel"> Pet Hotel
                        </label>
                    </div>
                </div>
                <textarea name="comment" rows="4" placeholder="Share your experience..." required></textarea>
                <div class="image-input" style="margin: 10px 0;">
                    <label>Add Image (optional):</label>
                    <input type="file" name="review_image" accept="image/*">
                    <p style="font-size: 12px; color: #666;">Supported formats: JPG, PNG, GIF (max 5MB)</p>
                </div>
                <button type="submit" class="btn-logout" style="background-color: #1abc9c;">Submit Feedback</button>
            </form>

            <h2>All Reviews</h2>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                            <span style="color: #666; font-size: 0.9em;">
                                <?php echo date('F j, Y g:i A', strtotime($review['created_at'])); ?>
                            </span>
                        </div>
                        <div class="star-rating">
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($review['services'])): ?>
                            <div class="services-tags" style="margin: 5px 0;">
                                <?php 
                                $services = json_decode($review['services'], true);
                                foreach ($services as $service): 
                                    $serviceName = $service === 'pet_grooming' ? 'Pet Grooming' : 'Pet Hotel';
                                ?>
                                    <span style="background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 5px;">
                                        <?php echo $serviceName; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                        <?php if (!empty($review['image_path'])): ?>
                            <div class="review-image" style="margin-top: 10px;">
                                <img src="<?php echo htmlspecialchars($review['image_path']); ?>" 
                                     alt="Review image" 
                                     style="max-width: 200px; border-radius: 4px;">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>