<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['action'];
    
    if ($status === 'rejected') {
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        $stmt = $pdo->prepare("UPDATE pet_boarding SET status = ?, rejection_reason = ? WHERE id = ?");
        $stmt->execute([$status, $rejection_reason, $booking_id]);
        
        $stmt = $pdo->prepare("
            UPDATE accommodations a 
            JOIN pet_boarding pb ON a.id = pb.accommodation_id 
            SET a.is_available = 1 
            WHERE pb.id = ?
        ");
        $stmt->execute([$booking_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE pet_boarding SET status = ? WHERE id = ?");
        $stmt->execute([$status, $booking_id]);
    }
    
    header("Location: admin_bookings.php");
    exit();
}

$stmt = $pdo->query("
    SELECT pb.id, pb.pet_name, pb.pet_type, pb.notes,
           DATE(pb.check_in) as check_in, 
           DATE(pb.check_out) as check_out, 
           a.type, a.number, u.name as client_name 
    FROM pet_boarding pb 
    JOIN accommodations a ON pb.accommodation_id = a.id 
    JOIN users u ON pb.user_id = u.id 
    WHERE pb.status = 'pending'
    ORDER BY pb.check_in
");
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_bookings.css">
    <script>
        function confirmAction(action, petName) {
            if (action === 'approved') {
                return confirm(`Are you sure you want to approve the booking for ${petName}?`);
            }
            return true;
        }

        function showRejectionModal(bookingId) {
            var modal = document.getElementById('rejectionModal');
            document.getElementById('modalBookingId').value = bookingId;
            modal.style.display = 'block';
            return false;
        }

        function closeModal() {
            document.getElementById('rejectionModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('rejectionModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
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
            <li><a href="admin_bookings.php" class="active"><i class="fas fa-paw"></i> Pending Reservations</a></li>
            <li><a href="admin_manage_appointments.php"><i class="fas fa-calendar"></i> Manage Appointments</a></li>
            <li><a href="admin_manage_reservations.php"><i class="fas fa-hotel"></i> Manage Reservations</a></li>
            <li><a href="admin_services.php"><i class="fas fa-bone"></i> Manage Services</a></li>
            <li><a href="admin_boarding_rates.php"><i class="fas fa-dollar-sign" style="width: 16px; text-align: center;"></i> Manage Rates</a></li>
            <li><a href="admin_feedback.php"><i class="fas fa-comments"></i> Manage Feedback</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Manage Reservations</h1>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <hr>
        <?php foreach ($bookings as $booking): ?>
            <div class="appointment-card">
                <div class="appointment-info">
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">Booking Details</h3>
                    <p><span class="info-label">Client:</span> <?= htmlspecialchars($booking['client_name']) ?></p>
                    <p><span class="info-label">Pet Name:</span> <?= htmlspecialchars($booking['pet_name']) ?></p>
                    <p><span class="info-label">Pet Type:</span> <?= htmlspecialchars($booking['pet_type']) ?></p>
                    <p><span class="info-label">Accommodation:</span> <?= htmlspecialchars($booking['type']) ?> <?= htmlspecialchars($booking['number']) ?></p>
                    <p><span class="info-label">Check In:</span> <?= htmlspecialchars($booking['check_in']) ?></p>
                    <p><span class="info-label">Check Out:</span> <?= htmlspecialchars($booking['check_out']) ?></p>
                    <?php if (!empty($booking['notes'])): ?>
                        <p><span class="info-label">Notes:</span> <?= htmlspecialchars($booking['notes']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($booking['rejection_reason'])): ?>
                        <p><span class="info-label">Rejection Reason:</span> <?= htmlspecialchars($booking['rejection_reason']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="button-group">
                    <form method="POST" style="display:inline;" onsubmit="return confirmAction('approved', '<?= htmlspecialchars($booking['pet_name']) ?>')">
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                        <button type="submit" name="action" value="approved" class="btn approve-btn">
                            <i class="fas fa-check icon"></i> Approve
                        </button>
                    </form>
                    <button onclick="return showRejectionModal(<?= $booking['id'] ?>)" class="btn reject-btn">
                        <i class="fas fa-times icon"></i> Reject
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-hotel"></i>
                <p>No pending reservations at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="rejectionModal" class="modal">
        <div class="modal-content">
            <h3>Rejection Reason</h3>
            <form method="POST" id="rejectionForm">
                <input type="hidden" id="modalBookingId" name="booking_id">
                <input type="hidden" name="action" value="rejected">
                <textarea name="rejection_reason" rows="4" required 
                    placeholder="Please provide a reason for rejection"></textarea>
                <div class="button-group">
                    <button type="submit" class="btn reject-btn">Submit</button>
                    <button type="button" class="btn cancel-btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>