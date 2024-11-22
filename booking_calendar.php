<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_appointment'])) {
        $stmt = $pdo->prepare('DELETE FROM notifications WHERE appointment_id = ?');
        $stmt->execute([$_POST['appointment_id']]);
        
        $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ? AND user_id = ?');
        $stmt->execute([$_POST['appointment_id'], $user['id']]);
        
        header("Location: booking_calendar.php");
        exit();
    }
    
    if (isset($_POST['delete_all'])) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE n FROM notifications n 
                INNER JOIN appointments a ON n.appointment_id = a.id 
                WHERE a.user_id = ?');
            $stmt->execute([$user['id']]);
            
            $stmt = $pdo->prepare('DELETE FROM appointments WHERE user_id = ?');
            $stmt->execute([$user['id']]);
            
            $pdo->commit();
            header("Location: booking_calendar.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("An error occurred: " . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $user_id = $user['id'];
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $response = ['success' => false, 'message' => ''];

    $stmt = $pdo->prepare('
        SELECT * FROM appointments 
        WHERE appointment_date = ? 
        AND appointment_time = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ');
    $stmt->execute([$date, $time]);
    $existing = $stmt->fetch();

    if (!$existing || $existing['status'] === 'rejected') {
        $stmt = $pdo->prepare('
            INSERT INTO appointments 
            (user_id, appointment_date, appointment_time, notes, status, created_at) 
            VALUES (?, ?, ?, ?, "pending", CURRENT_TIMESTAMP)
        ');
        $stmt->execute([$user_id, $date, $time, $notes]);
        
        $appointment_id = $pdo->lastInsertId();
        
        $message = "Your appointment request for {$date} at {$time} has been submitted and is pending approval.";
        $stmt = $pdo->prepare('
            INSERT INTO notifications 
            (user_id, message, type, appointment_id, created_at) 
            VALUES (?, ?, "appointment", ?, CURRENT_TIMESTAMP)
        ');
        $stmt->execute([$user_id, $message, $appointment_id]);
        
        $response['success'] = true;
        $response['message'] = "Appointment booked successfully!";
    } else {
        $response['message'] = "This time slot is already booked!";
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

function generateTimeSlots() {
    $slots = [];
    $start = strtotime('09:00');
    $end = strtotime('17:00');
    $interval = 30 * 60;

    for ($time = $start; $time <= $end; $time += $interval) {
        $slots[] = date('H:i', $time);
    }
    return $slots;
}

$timeSlots = generateTimeSlots();

$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

if ($year < date('Y') || ($year == date('Y') && $month < date('n'))) {
    $month = date('n');
    $year = date('Y');
}

function getNextMonth($month, $year) {
    return $month == 12 ? [1, $year + 1] : [$month + 1, $year];
}

function getPrevMonth($month, $year) {
    $currentMonth = date('n');
    $currentYear = date('Y');
    
    $prevMonth = ($month == 1) ? 12 : $month - 1;
    $prevYear = ($month == 1) ? $year - 1 : $year;
    
    return ($prevYear < $currentYear || ($prevYear == $currentYear && $prevMonth < $currentMonth)) 
           ? false 
           : [$prevMonth, $prevYear];
}

function buildCalendar($month, $year) {
    $firstDay = mktime(0,0,0,$month,1,$year);
    $numberDays = date('t',$firstDay);
    $dateComponents = getdate($firstDay);
    $monthName = $dateComponents['month'];
    $dayOfWeek = $dateComponents['wday'];
    
    $next = getNextMonth($month, $year);
    $prev = getPrevMonth($month, $year);
    
    $calendar = "<div class='calendar'>";
    
    $calendar .= "<div class='calendar-nav'>";
    if ($prev) {
        $calendar .= "<a href='?month={$prev[0]}&year={$prev[1]}'>&lt; Previous</a>";
    }
    $calendar .= " <span class='current-month'>$monthName $year</span> ";
    $calendar .= "<a href='?month={$next[0]}&year={$next[1]}'>Next &gt;</a>";
    $calendar .= "</div>";
    
    $calendar .= "<div class='calendar-grid'>";
    $daysOfWeek = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    
    foreach($daysOfWeek as $day) {
        $calendar .= "<div class='calendar-day-header'>$day</div>";
    }
    
    if ($dayOfWeek > 0) { 
        for($k=0;$k<$dayOfWeek;$k++){
            $calendar .= "<div class='calendar-day empty'></div>";
        }
    }
    
    for($i=1;$i<=$numberDays;$i++) {
        $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $i);
        $class = 'calendar-day';
        if($i == date('d') && $month == date('m') && $year == date('Y')) {
            $class .= ' today';
        }
        if(strtotime($currentDate) < strtotime(date('Y-m-d'))) {
            $class .= ' past';
        }
        $calendar .= "<div class='$class' data-date='$currentDate'>$i</div>";
    }
    
    $calendar .= "</div></div>";
    return $calendar;
}

function checkTimeSlotStatus($date, $time) {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT status 
        FROM appointments 
        WHERE appointment_date = ? 
        AND appointment_time = ?
        ORDER BY created_at DESC 
        LIMIT 1
    ');
    $stmt->execute([$date, $time]);
    $result = $stmt->fetch(PDO::FETCH_COLUMN);
    
    return ($result && $result !== 'rejected') ? $result : 'available';
}

if (isset($_GET['check_status'])) {
    $date = $_GET['date'];
    $time = $_GET['time'];
    echo checkTimeSlotStatus($date, $time);
    exit;
}

$calendar = buildCalendar($month, $year);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/booking_calendar.css">
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
            <li><a href="booking_calendar.php" class="active"><i class="fas fa-calendar-alt"></i>Book Pet Grooming</a></li>
            <li><a href="book_pet_boarding.php"><i class="fas fa-hotel"></i>Book Pet Hotel</a></li>
            <li><a href="services.php"><i class="fas fa-list"></i>Services & Prices</a></li>
            <li><a href="pet_boarding_rates.php"><i class="fas fa-money-bill"></i>Pet Boarding Rates</a></li>
            <li><a href="feedback.php"><i class="fas fa-comments"></i>Feedback & Reviews</a></li>
            <li><a href="user_information.php"><i class="fas fa-user"></i>User Information</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Book Pet Grooming</h1>
            <div>
                <a href="notifications.php" class="btn-grooming-notifications">Notifications</a>
                <a href="logout.php" class="btn-grooming-logout">Logout</a>
            </div>
        </div>
        <div id="messageContainer" class="message-container"></div>
        <div class="content-grid">
            <div class="calendar-section">
                <?php
                    $month = isset($_GET['month']) ? $_GET['month'] : date('m');
                    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                    echo buildCalendar($month, $year);
                ?>
            </div>
            
            <div class="appointments-section">
                <h2>My Appointments</h2>
                <?php
                $stmt = $pdo->prepare('
                    SELECT * FROM appointments 
                    WHERE user_id = ? 
                    ORDER BY appointment_date DESC, appointment_time DESC
                ');
                $stmt->execute([$user['id']]);
                $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (count($appointments) > 0): ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete all appointments?');">
                        <button type="submit" name="delete_all" class="delete-all-btn">Delete All Appointments</button>
                    </form>
                    <div class="appointments-container">
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-card <?php echo $appointment['status']; ?>">
                                <div class="appointment-details"><strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?></div>
                                <div class="appointment-details"><strong>Time:</strong> <?php echo htmlspecialchars($appointment['appointment_time']); ?></div>
                                <div class="appointment-details"><strong>Status:</strong> <span class="status-<?php echo $appointment['status']; ?>"><?php echo ucfirst(htmlspecialchars($appointment['status'])); ?></span></div>
                                <?php if (!empty($appointment['notes'])): ?>
                                    <div class="appointment-details notes"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></div>
                                <?php endif; ?>
                                <?php if ($appointment['status'] === 'rejected' && !empty($appointment['rejection_reason'])): ?>
                                    <div class="appointment-details rejection-reason">
                                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($appointment['rejection_reason']); ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <button type="submit" name="delete_appointment" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-appointments">You have no appointments at this time.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="time-slot-modal" id="timeSlotModal">
        <h3>Select Time Slot</h3>
        <div class="time-slots" id="timeSlots"></div>
        <div class="notes-container">
            <textarea id="appointmentNotes" 
                      placeholder="Add any notes or special requests..."
                      rows="3"
                      maxlength="500"></textarea>
            <div class="char-counter">0/500</div>
        </div>
        <button id="submitAppointment" class="submit-btn" disabled>Book Appointment</button>
    </div>
    <div class="modal-backdrop" id="modalBackdrop"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('timeSlotModal');
    const backdrop = document.getElementById('modalBackdrop');
    const timeSlotsContainer = document.getElementById('timeSlots');
    let selectedDate = null;
    let selectedTime = null;
    const submitButton = document.getElementById('submitAppointment');
    const notesTextarea = document.getElementById('appointmentNotes');
    const charCounter = document.querySelector('.char-counter');
    
    document.querySelectorAll('.calendar-day:not(.past):not(.empty)').forEach(day => {
        day.addEventListener('click', function() {
            const date = this.dataset.date;
            showTimeSlots(date);
        });
    });
    
    backdrop.addEventListener('click', closeModal);
    
    function showTimeSlots(date) {
        selectedDate = date;
        let timeSlots = generateTimeSlots();
        timeSlotsContainer.innerHTML = '';
        
        const now = new Date();
        const selectedDateObj = new Date(date);
        const isToday = selectedDateObj.toDateString() === now.toDateString();
        
        timeSlots.forEach(time => {
            const [hours, minutes] = time.split(':');
            const slotTime = new Date(selectedDateObj);
            slotTime.setHours(parseInt(hours), parseInt(minutes), 0);
            
            const slot = document.createElement('div');
            slot.textContent = time;
            
            if (isToday && slotTime <= now) {
                slot.className = 'time-slot past';
                slot.style.backgroundColor = '#f5f5f5';
                slot.style.color = '#adb5bd';
                slot.style.cursor = 'not-allowed';
            } else {
                slot.className = 'time-slot';
                fetch(`booking_calendar.php?check_status=1&date=${date}&time=${time}`)
                    .then(response => response.text())
                    .then(status => {
                        if (status === 'pending') {
                            slot.style.backgroundColor = '#FFD700';
                            slot.style.color = '#000000';
                            slot.style.cursor = 'not-allowed';
                        } else if (status === 'approved' || status === 'booked') {
                            slot.style.backgroundColor = '#FF9999';
                            slot.style.color = '#000000';
                            slot.style.cursor = 'not-allowed';
                        } else if (status === 'rejected' || status === 'available') {
                            slot.addEventListener('click', () => selectTimeSlot(slot, time));
                        }
                    });
            }
            
            timeSlotsContainer.appendChild(slot);
        });
        
        modal.style.display = 'block';
        backdrop.style.display = 'block';
    }
    
    submitButton.addEventListener('click', () => {
        if (selectedDate && selectedTime) {
            bookAppointment(selectedDate, selectedTime);
        }
    });

    function selectTimeSlot(slotElement, time) {
        document.querySelectorAll('.time-slot').forEach(slot => {
            slot.classList.remove('selected');
        });
        
        slotElement.classList.add('selected');
        selectedTime = time;
        
        submitButton.disabled = false;
    }

    function generateTimeSlots() {
        const slots = [];
        const start = 9;
        const end = 17;
        
        for(let hour = start; hour < end; hour++) {
            slots.push(`${hour}:00`);
            slots.push(`${hour}:30`);
        }
        
        return slots;
    }
    
    function bookAppointment(date, time) {
        const formData = new FormData();
        formData.append('date', date);
        formData.append('time', time);
        formData.append('notes', document.getElementById('appointmentNotes').value);

        fetch('booking_calendar.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.success);
            if (data.success) {
                closeModal();
                location.reload();
            }
        })
        .catch(error => {
            showMessage('An error occurred while booking the appointment.', false);
        });
    }

    function showMessage(message, isSuccess) {
        const messageContainer = document.getElementById('messageContainer');
        const messageElement = document.createElement('div');
        messageElement.className = `message ${isSuccess ? 'success' : 'error'}`;
        messageElement.textContent = message;
        messageContainer.appendChild(messageElement);

        setTimeout(() => {
            messageElement.classList.add('fade-out');
            setTimeout(() => {
                messageContainer.removeChild(messageElement);
            }, 500);
        }, 3000);
    }
    
    function closeModal() {
        modal.style.display = 'none';
        backdrop.style.display = 'none';
        selectedDate = null;
        selectedTime = null;
        submitButton.disabled = true;
        document.getElementById('appointmentNotes').value = '';
        document.querySelector('.char-counter').textContent = '0/500';
    }

    notesTextarea.addEventListener('input', function() {
        const count = this.value.length;
        charCounter.textContent = `${count}/500`;
    });
});
</script>

</body>
</html>

