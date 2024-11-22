<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO boarding_rates (pet_size, accommodation_type, days_range, rate, inclusions) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['pet_size'], $_POST['accommodation_type'], $_POST['days_range'], $_POST['rate'], $_POST['inclusions']]);
                break;
            case 'update':
                $stmt = $pdo->prepare("UPDATE boarding_rates SET pet_size = ?, accommodation_type = ?, days_range = ?, rate = ?, inclusions = ? WHERE id = ?");
                $stmt->execute([$_POST['pet_size'], $_POST['accommodation_type'], $_POST['days_range'], $_POST['rate'], $_POST['inclusions'], $_POST['id']]);
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM boarding_rates WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
        }
        header("Location: admin_boarding_rates.php");
        exit();
    }
}

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
    <title>Admin - Pet Boarding Rates</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_boarding_rates.css">
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
            <li><a href="admin_boarding_rates.php" class="active"><i class="fas fa-dollar-sign" style="width: 16px; text-align: center;"></i> Manage Rates</a></li>
            <li><a href="admin_feedback.php"><i class="fas fa-comments"></i> Manage Feedback</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Manage Pet Boarding Rates</h1>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
        
        <div class="rates-container">
            <div class="admin-controls">
                <button class="btn-add" onclick="showAddModal()">Add New Rate</button>
            </div>

            <div id="rateModal" class="modal">
                <div class="modal-content">
                    <form id="rateForm" method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="pet_size">Pet Size</label>
                            <select name="pet_size" id="pet_size" required>
                                <option value="">Select Size</option>
                                <option value="Small">Small</option>
                                <option value="Medium">Medium</option>
                                <option value="Large">Large</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="accommodation_type">Accommodation Type</label>
                            <input type="text" id="accommodation_type" name="accommodation_type" required>
                        </div>

                        <div class="form-group">
                            <label for="days_range">Days Range</label>
                            <input type="text" id="days_range" name="days_range" required>
                        </div>

                        <div class="form-group">
                            <label for="rate">Rate per Day</label>
                            <input type="number" id="rate" name="rate" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="inclusions">Inclusions</label>
                            <textarea id="inclusions" name="inclusions" required></textarea>
                        </div>

                        <button type="submit" class="btn-add">Save Rate</button>
                        <button type="button" onclick="closeModal()" class="btn-delete">Cancel</button>
                    </form>
                </div>
            </div>

            <?php foreach ($sizes as $size): ?>
                <div class="rates-category">
                    <h2 class="category-title">
                        <?php 
                        $sizeRange = '';
                        switch($size) {
                            case 'Small': $sizeRange = '(1 - 5 KGS)'; break;
                            case 'Medium': $sizeRange = '(6 - 12 KGS)'; break;
                            case 'Large': $sizeRange = '(13 KGS above)'; break;
                        }
                        echo $size . " Pets " . $sizeRange . " Boarding Rates";
                        ?>
                    </h2>
                    <div class="rates-grid">
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM boarding_rates WHERE pet_size = ? ORDER BY accommodation_type, rate DESC");
                        $stmt->execute([$size]);
                        $rates = $stmt->fetchAll();
                        
                        foreach ($rates as $rate):
                        ?>
                            <div class="rate-card" id="rate-<?php echo $rate['id']; ?>">
                                <div class="rate-content">
                                    <h3 class="accommodation-type"><?php echo $rate['accommodation_type']; ?></h3>
                                    <div class="rate-row">
                                        <span><?php echo $rate['days_range']; ?></span>
                                        <span>₱<?php echo number_format($rate['rate'], 2); ?>/Day</span>
                                    </div>
                                    <div class="inclusions">
                                        <strong>Inclusions:</strong><br>
                                        <?php echo $rate['inclusions']; ?>
                                    </div>
                                    <div class="actions">
                                        <button class="btn-edit" onclick="showEditForm(<?php echo htmlspecialchars(json_encode($rate)); ?>)">Edit</button>
                                        <button class="btn-delete" onclick="deleteRate(<?php echo $rate['id']; ?>)">Delete</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    </div>

    <script>
        function showEditForm(rate) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.className = 'edit-form';
            form.innerHTML = `
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="${rate.id}">
                <select name="pet_size" required>
                    <option value="Small" ${rate.pet_size === 'Small' ? 'selected' : ''}>Small</option>
                    <option value="Medium" ${rate.pet_size === 'Medium' ? 'selected' : ''}>Medium</option>
                    <option value="Large" ${rate.pet_size === 'Large' ? 'selected' : ''}>Large</option>
                </select>
                <input type="text" name="accommodation_type" value="${rate.accommodation_type}" required>
                <input type="text" name="days_range" value="${rate.days_range}" required>
                <input type="number" name="rate" value="${rate.rate}" step="0.01" required>
                <textarea name="inclusions" required>${rate.inclusions}</textarea>
                <button type="submit" class="btn-add">Update Rate</button>
            `;
            
            const rateCard = document.getElementById(`rate-${rate.id}`);
            const existingForm = rateCard.querySelector('.edit-form');
            if (existingForm) {
                existingForm.remove();
            } else {
                rateCard.appendChild(form);
            }
        }

        function deleteRate(id) {
            if (confirm('Are you sure you want to delete this rate?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function showAddModal() {
            document.getElementById('rateModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('rateModal').style.display = 'none';
        }
    </script>
</body>
</html>
