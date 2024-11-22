<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
$user = $_SESSION['user'];

$stmt = $pdo->query("SELECT DISTINCT type FROM accommodations");
$accommodationTypes = $stmt->fetchAll();

$successMessage = '';
if (isset($_SESSION['booking_success'])) {
    $successMessage = $_SESSION['booking_success'];
    unset($_SESSION['booking_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_booking'])) {
        $stmt = $pdo->prepare('DELETE FROM pet_boarding WHERE id = ? AND user_id = ?');
        $stmt->execute([$_POST['booking_id'], $user['id']]);
        header("Location: book_pet_boarding.php");
        exit();
    }
    
    if (isset($_POST['delete_all'])) {
        $stmt = $pdo->prepare('DELETE FROM pet_boarding WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        header("Location: book_pet_boarding.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Pet Boarding</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/book_pet_boarding.css">
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
            <li><a href="book_pet_boarding.php" class="active"><i class="fas fa-hotel"></i>Book Pet Hotel</a></li>
            <li><a href="services.php"><i class="fas fa-list"></i>Services & Prices</a></li>
            <li><a href="pet_boarding_rates.php"><i class="fas fa-money-bill"></i>Pet Boarding Rates</a></li>
            <li><a href="feedback.php"><i class="fas fa-comments"></i>Feedback & Reviews</a></li>
            <li><a href="user_information.php"><i class="fas fa-user"></i>User Information</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="top-bar__title">Pet Boarding</h1>
            <div class="top-bar__actions">
            <a href="notifications.php" class="btn-grooming-notifications">Notifications</a>
            <a href="logout.php" class="btn-grooming-logout">Logout</a>
            </div>
        </div>
        <br>
        <?php if ($successMessage): ?>
        <div class="success-message" id="successMessage">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
        <?php endif; ?>

        <div class="content-grid">
            <div class="booking-section">
                <form action="process_booking.php" method="POST">
                    <div class="form-group">
                        <label for="pet_name">Pet Name</label>
                        <input type="text" id="pet_name" name="pet_name" required placeholder="Enter pet's name">
                    </div>
                    
                    <div class="form-group">
                        <label for="pet_type">Pet Type</label>
                        <select id="pet_type" name="pet_type" required>
                            <option value="">Select Pet Type</option>
                            <option value="dog">Dog</option>
                            <option value="cat">Cat</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="check_in">Check-in Date</label>
                        <div class="date-input-container">
                            <input type="date" id="check_in" name="check_in" required>
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="check_out">Check-out Date</label>
                        <div class="date-input-container">
                            <input type="date" id="check_out" name="check_out" required>
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="accommodation_type">Accommodation Type</label>
                        <select id="accommodation_type" name="accommodation_type" required>
                            <option value="">Select Accommodation</option>
                            <option value="cage">Cage</option>
                            <option value="room">Room</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="accommodation_number">Accommodation Number</label>
                        <select id="accommodation_number" name="accommodation_number" required>
                            <option value="">Select Number</option>    
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Special Notes</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Any special requirements or notes for your pet"></textarea>
                    </div>

                    <button type="submit" class="book-now-btn">Book Now</button>
                </form>
            </div>

            <div class="appointments-section">
                <h2>My Bookings</h2>
                <?php
                $stmt = $pdo->prepare('
                    SELECT pb.*, a.type as accommodation_type, a.number as accommodation_number 
                    FROM pet_boarding pb
                    JOIN accommodations a ON pb.accommodation_id = a.id
                    WHERE pb.user_id = ? 
                    ORDER BY pb.check_in DESC
                ');
                $stmt->execute([$user['id']]);
                $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <?php if (count($bookings) > 0): ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete all bookings?');">
                        <button type="submit" name="delete_all" class="delete-all-btn">Delete All Bookings</button>
                    </form>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="appointment-card <?php echo $booking['status']; ?>">
                            <div class="appointment-details"><strong>Pet Name:</strong> <?php echo htmlspecialchars($booking['pet_name']); ?></div>
                            <div class="appointment-details"><strong>Pet Type:</strong> <?php echo htmlspecialchars($booking['pet_type']); ?></div>
                            <div class="appointment-details"><strong>Accommodation:</strong> <?php echo htmlspecialchars($booking['accommodation_type'] . ' ' . $booking['accommodation_number']); ?></div>
                            <div class="appointment-details"><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['check_in']); ?></div>
                            <div class="appointment-details"><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['check_out']); ?></div>
                            <div class="appointment-details"><strong>Status:</strong> <span class="status-<?php echo $booking['status']; ?>"><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></span></div>
                            <?php if (!empty($booking['notes'])): ?>
                                <div class="appointment-details"><strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?></div>
                            <?php endif; ?>
                            <?php if ($booking['status'] === 'rejected' && !empty($booking['rejection_reason'])): ?>
                                <div class="appointment-details rejection-reason">
                                    <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($booking['rejection_reason']); ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <button type="submit" name="delete_booking" class="delete-btn">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-appointments">You have no pet boarding bookings at this time.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayString = today.toISOString().split('T')[0];
        
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        checkInInput.setAttribute('min', todayString);
        checkOutInput.setAttribute('min', todayString);
        
        checkInInput.addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            if (selectedDate < today) {
                this.value = todayString;
            }
        });
        
        checkOutInput.addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            if (selectedDate < today) {
                this.value = todayString;
            }
        });

        checkInInput.addEventListener('change', function() {
            checkOutInput.setAttribute('min', this.value);
            if (checkOutInput.value < this.value) {
                checkOutInput.value = this.value;
            }
        });

        function updateAvailableNumbers() {
            const type = document.getElementById('accommodation_type').value;
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const numberSelect = document.getElementById('accommodation_number');
            
            if (!type) {
                numberSelect.innerHTML = '<option value="">Select Number</option>';
                return;
            }

            numberSelect.innerHTML = '<option value="">Loading...</option>';
            
            fetch('check_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `type=${encodeURIComponent(type)}&check_in=${encodeURIComponent(checkIn)}&check_out=${encodeURIComponent(checkOut)}`
            })
            .then(response => response.json())
            .then(numbers => {
                const selectText = type === 'cage' ? 'Select Cage Number' : 'Select Room Number';
                numberSelect.innerHTML = `<option value="">${selectText}</option>`;
                numbers.forEach(num => {
                    numberSelect.innerHTML += `<option value="${num}">${type} ${num}</option>`;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                numberSelect.innerHTML = '<option value="">Error loading numbers</option>';
            });
        }

        document.getElementById('accommodation_type').addEventListener('change', updateAvailableNumbers);
        document.getElementById('check_in').addEventListener('change', updateAvailableNumbers);
        document.getElementById('check_out').addEventListener('change', updateAvailableNumbers);

        const typeSelect = document.getElementById('accommodation_type');
        if (typeSelect.value) {
            updateAvailableNumbers();
        }

        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.style.display = 'block';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3500);
        }
    </script>
</body>
</html>