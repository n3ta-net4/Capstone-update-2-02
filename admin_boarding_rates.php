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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica', Arial, sans-serif;
        }
        body {
            display: flex;
            height: 100vh;
            background-color: #f5f7fa;
        }
        hr {
            border: 0;
            height: 1px;
            background: #fff;
            margin: 10px 0;
        }
        
        .sidebar {
            width: 240px;
            background-color: #2c3e50;
            color: #fff;
            padding: 20px;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
        }
        .sidebar .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .sidebar .logo img {
            width: 200px;
            margin-bottom: 5px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .sidebar h2 {
            color: #ecf0f1;
            margin-bottom: 20px;
            text-align: center;
        }
        .sidebar ul {
            list-style: none;
            padding-top: 10px;
            flex-grow: 1;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 6px;
            transition: background-color 0.3s ease-in-out;
        }
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #1abc9c;
        }

        .main-content {
            margin-left: 240px;
            padding: 30px;
            width: calc(100% - 240px);
            background-color: #f5f7fa;
            overflow-y: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #cccccc;
            padding: 15px 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .top-bar h1 {
            font-size: 30px;
            font-weight: 600;
            color: #2c3e50;
        }

        .btn-logout {
            background-color: #e74c3c;
            padding: 10px 20px;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 20px;
            transition: background-color 0.3s ease-in-out;
            margin-left: 15px;
        }

        .rates-category {
            background: #2c3e50;
            padding: 25px;
            margin: 15px 0;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .category-title {
            color: #ffffff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1abc9c;
            font-size: 1.8em;
        }

        .rates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .rate-card {
            background: #34495e;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #415b76;
            position: relative;
        }

        .rate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        .accommodation-type {
            color: #fff;
            font-size: 1.4em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1abc9c;
        }

        .rate-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 15px 0;
            font-size: 1.1em;
            color: #ecf0f1;
        }

        .rate-row span:last-child {
            font-weight: bold;
            color: #1abc9c;
        }

        .inclusions {
            margin-top: 15px;
            padding: 15px;
            background: #2c3e50;
            border-radius: 8px;
            font-size: 0.95em;
            line-height: 1.6;
            color: #bdc3c7;
        }

        .inclusions strong {
            color: #ecf0f1;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-add {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-edit:hover {
            background: #2980b9;
        }

        .btn-add {
            background: #1abc9c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-add:hover {
            background: #27ae60;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .edit-form {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .edit-form input, 
        .edit-form select, 
        .edit-form textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #dde1e3;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        .edit-form input:focus, 
        .edit-form select:focus, 
        .edit-form textarea:focus {
            outline: none;
            border-color: #1abc9c;
            box-shadow: 0 0 0 2px rgba(26, 188, 156, 0.2);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 12px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <a href="admin_dashboard.php">
                <img src="aw-k9.png" alt="aw-k9 logo">
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
