<?php
include './../config.php';
include './template/header.php';

// Get the room_id from GET
if (!isset($_GET['room_id'])) {
    die("No room selected.");
}

$room_id = $_GET['room_id'];

// Fetch room details
$sql = "SELECT r.*, t.room_type_name 
        FROM rooms_tbl r
        LEFT JOIN room_type_tbl t ON r.room_type_id = t.room_type_id
        WHERE r.room_id = '$room_id'";

$result = mysqli_query($conn, $sql);
$room = mysqli_fetch_assoc($result);

if (!$room) {
    die("Room not found.");
}
?>

<body>
    <?php include './template/navbar.php'; ?>

    <div class="container py-5">
        <div class="row g-5">
            <!-- Room Details -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <img src="./../user/admin/<?= $room['image'] ?>" class="card-img-top rounded-top-4" alt="<?= $room['room_type_name'] ?>">
                    <div class="card-body text-center">
                        <h3 class="card-title text-primary fw-bold"><?= $room['room_type_name'] ?></h3>
                        <p class="fw-bold text-dark mb-1">₱<?= number_format($room['price'], 2) ?> / night</p>
                        <p class="text-muted mb-1">Max Guests: <?= $room['max_guest'] ?></p>
                        <p class="text-muted mb-1"><?= $room['available'] ?> rooms available</p>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <h4 class="mb-4 text-primary fw-bold">Book This Room</h4>
                    <form action="confirm_booking.php" method="POST">
                        <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">

                        <div class="mb-3">
                            <label for="guest_name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="guest_name" name="guest_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="guest_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="guest_email" name="guest_email" required>
                        </div>

                        <div class="mb-3">
                            <label for="guest_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="guest_phone" name="guest_phone" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="checkIn" class="form-label">Check-in Date</label>
                                <input type="date" class="form-control" id="checkIn" name="checkIn" required>
                            </div>
                            <div class="col-md-6">
                                <label for="checkOut" class="form-label">Check-out Date</label>
                                <input type="date" class="form-control" id="checkOut" name="checkOut" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Nights</label>
                                <input type="text" class="form-control" id="totalNights" name="totalNights" readonly>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <label for="guests" class="form-label">Number of Guests</label>
                            <select class="form-select" id="guests" name="guests" required>
                                <option value="" selected disabled>Select</option>
                                <?php
                                for ($i = 1; $i <= $room['max_guest']; $i++) {
                                    echo "<option value='$i'>$i Guest" . ($i > 1 ? 's' : '') . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="total_price" class="form-label">Total Price</label>
                            <input type="text" class="form-control" id="total_price" name="total_price" readonly
                                value="₱<?= number_format($room['price'], 2) ?>">
                        </div>

                        <style>
                            /* Premium button style */
                            .btn-success-premium {
                                background: linear-gradient(135deg, #28a745, #20c997);
                                color: #fff;
                                font-weight: 600;
                                font-size: 1.1rem;
                                padding: 12px 25px;
                                border-radius: 50px;
                                border: none;
                                box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
                                transition: all 0.3s ease;
                            }

                            .btn-success-premium:hover {
                                transform: translateY(-3px);
                                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                                background: linear-gradient(135deg, #20c997, #28a745);
                                color: #fff;
                            }

                            .btn-success-premium:focus {
                                outline: none;
                                box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.5);
                            }
                        </style>

                        <!-- Button HTML -->
                        <button type="button" id="confirmBookingBtn" class="btn btn-success-premium w-100">
                            <i class="fas fa-check me-2"></i>Confirm Booking
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <br><br>
    <br><br>

    <?php include './template/script.php'; ?>
    <script>
        // Grab elements
        const checkIn = document.getElementById('checkIn');
        const checkOut = document.getElementById('checkOut');
        const totalNights = document.getElementById('totalNights');
        const totalPrice = document.getElementById('total_price');
        const guestsSelect = document.getElementById('guests');

        const pricePerNight = <?= $room['price'] ?>; // From PHP
        const maxGuests = <?= $room['max_guest'] ?>; // From PHP

        function calculateNightsAndPrice() {
            if (checkIn.value && checkOut.value) {
                const start = new Date(checkIn.value);
                const end = new Date(checkOut.value);

                // Calculate difference in days
                const diffTime = end.getTime() - start.getTime();
                const diffDays = diffTime / (1000 * 60 * 60 * 24);

                if (diffDays > 0) {
                    totalNights.value = diffDays;
                    totalPrice.value = '₱' + (pricePerNight * diffDays).toLocaleString();
                } else {
                    totalNights.value = 0;
                    totalPrice.value = 'Invalid dates';
                }
            } else {
                totalNights.value = '';
                totalPrice.value = '';
            }
        }

        // Event listeners
        checkIn.addEventListener('change', calculateNightsAndPrice);
        checkOut.addEventListener('change', calculateNightsAndPrice);

        // Restrict guests to max allowed
        guestsSelect.addEventListener('change', () => {
            const selected = parseInt(guestsSelect.value);
            if (selected > maxGuests) {
                alert(`The selected room can only accommodate up to ${maxGuests} guest(s).`);
                guestsSelect.value = '';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('confirmBookingBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Confirm Booking?',
                text: "Do you want to confirm this reservation?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Book Now!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.querySelector('form');
                    const formData = new FormData(form);

                    // Remove currency symbol from total_price
                    const totalPrice = formData.get('total_price').replace(/[^0-9.]/g, '');
                    formData.set('total_price', totalPrice);

                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while we confirm your reservation.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    fetch('confirm_booking.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Reserved!',
                                    text: 'Your reservation has been successfully made.',
                                    timer: 2500,
                                    showConfirmButton: false
                                }).then(() => window.location.href = 'room');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops!',
                                    text: data.message
                                });
                            }
                        })
                        .catch(err => console.error(err));
                }
            });
        });
    </script>




</body>

</html>