<?php

if(!isset($_SESSION)){
    session_start();
}

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include "db.php";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a_id = $_POST['appointment_id'];
    $act = $_POST['action'];
    $newStatus = ($act==='approve')? 'approved':'rejected';
    
    $stmt = $pdo->prepare('SELECT user_id, appointment_date, appointment_time FROM appointments WHERE id = ?');
    $stmt->execute([$a_id]);
    $appointment = $stmt->fetch();
    
    if($act === 'reject') {
        $reason = $_POST['rejection_reason'];
        $q = $pdo->prepare('UPDATE appointments SET status = ?, rejection_reason = ? WHERE id = ?');
        $q->execute([$newStatus, $reason, $a_id]);
        
        $message = "Your appointment for {$appointment['appointment_date']} at {$appointment['appointment_time']} has been rejected. Reason: $reason";
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, type) VALUES (?, ?, "rejection")');
        $stmt->execute([$appointment['user_id'], $message]);
    } else {
        $q = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");  
        $q->execute([$newStatus, $a_id]);
        
        $message = "Your appointment for {$appointment['appointment_date']} at {$appointment['appointment_time']} has been approved!";
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, type) VALUES (?, ?, "approval")');
        $stmt->execute([$appointment['user_id'], $message]);
    }
    
    header('Location: admin_appointments.php');
    exit();
}

$q = $pdo->prepare("SELECT a.*, u.name as user_name, u.email, a.notes 
    FROM appointments a JOIN users u ON a.user_id = u.id 
    WHERE a.status = 'pending' ORDER BY a.appointment_date,a.appointment_time");
$q->execute();
$appointments=$q->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="css/admin_appointments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <li><a href="admin_appointments.php" class="active"><i class="fas fa-calendar-check"></i> Pending Appointments</a></li>
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
        <h1>Manage Appointments</h1>
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
    <?php foreach($appointments as $a): ?>
        <div class="appointment-card">
            <div class="appointment-info">
                <h3 style="color: #2c3e50;margin-bottom: 15px">Appointment Details</h3>
                <p><span class="info-label">Client:</span><?=htmlspecialchars($a['user_name'])?></p>
                <p><span class="info-label">Email:</span><?=htmlspecialchars($a['email'])?></p>
                <p><span class="info-label">Date:</span><?=htmlspecialchars($a['appointment_date'])?></p>
                <p><span class="info-label">Time:</span><?=htmlspecialchars($a['appointment_time'])?></p>
                <?php if (!empty($a['notes'])): ?>
                    <p><span class="info-label">Notes:</span><?=htmlspecialchars($a['notes'])?></p>
                <?php endif; ?>
            </div>
            <div class="button-group">
                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to approve this appointment?');">
                    <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn approve-btn">
                        <i class="fas fa-check icon"></i> Approve
                    </button>
                </form>
                <button type="button" class="btn reject-btn" onclick="openRejectModal(<?= $a['id'] ?>)">
                    <i class="fas fa-times icon"></i> Reject
                </button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if(empty($appointments)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>No pending appointments at the moment.</p>
        </div>
    <?php endif ?>
</div>

<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3>Rejection Reason</h3>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="appointment_id" id="modal_appointment_id">
            <input type="hidden" name="action" value="reject">
            <textarea name="rejection_reason" rows="4" required 
                placeholder="Please provide a reason for rejection"></textarea>
            <div class="button-group">
                <button type="submit" class="btn reject-btn">Submit</button>
                <button type="button" class="btn cancel-btn" onclick="closeRejectModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(aid) {
    var m = document.getElementById('rejectModal')
    m.style.display = 'block'
    document.getElementById('modal_appointment_id').value=aid
}

function closeRejectModal() {
  document.getElementById('rejectModal').style.display='none'
}

window.onclick = function(ev) {
    let m = document.getElementById('rejectModal')
    if(ev.target==m) {m.style.display="none"}
}
</script>
</body>
</html>
