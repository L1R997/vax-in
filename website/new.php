<?php
include './../config.php';
include './template/header.php';

// Get the room_id from GET
if (!isset($_GET['room_id'])) {
    echo "<script>alert('No room selected.'); window.location.href='index.php';</script>";
    exit;
}

$room_id = mysqli_real_escape_string($conn, $_GET['room_id']);

// 1. UPDATED QUERY: Fetch Room + Active Discount Details
$sql = "SELECT r.*, t.room_type_name, 
                d.discount_name, d.discount_percent, d.start_date, d.end_date 
        FROM rooms_tbl r
        LEFT JOIN room_type_tbl t ON r.room_type_id = t.room_type_id
        LEFT JOIN discount_tbl d ON r.discount_id = d.discount_id
        WHERE r.room_id = '$room_id'";

$result = mysqli_query($conn, $sql);
$room = mysqli_fetch_assoc($result);

if (!$room) {
    die("Room not found.");
}

// 2. VALIDATE DISCOUNT DATE
$today = date('Y-m-d');
$active_discount = 0;
$promo_name = "";

if (!empty($room['discount_percent'])) {
    if ($today >= $room['start_date'] && $today <= $room['end_date']) {
        $active_discount = $room['discount_percent'];
        $promo_name = $room['discount_name'];
    }
}

// Calculate Prices for Display
$orig_price = $room['price'];
$final_price = $orig_price;
if ($active_discount > 0) {
    $final_price = $orig_price - ($orig_price * ($active_discount / 100));
}

// 3. FETCH ENTRANCE FEE (Kukunin ang presyo sa database)
$fee_sql = "SELECT entrance_fee_amount FROM entrance_fee_tbl LIMIT 1";
$fee_res = mysqli_query($conn, $fee_sql);
$fee_data = mysqli_fetch_assoc($fee_res);
$entrance_rate = $fee_data['entrance_fee_amount'] ?? 0;
?>

<style>
    .step {
        flex: 1;
        text-align: center;
        padding: 10px;
        border-bottom: 3px solid #e9ecef;
        color: #aaa;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .step:hover {
        background-color: rgba(13, 110, 253, 0.05);
        border-bottom-color: #0d6efd;
    }

    .step.active {
        border-color: #0d6efd;
        color: #0d6efd;
        font-weight: bold;
    }

    .step.completed {
        border-color: #198754;
        color: #198754;
    }

    .step.completed::before {
        content: "✓ ";
        margin-right: 5px;
    }


    .room-card-sticky {
        position: sticky;
        top: 20px;
        z-index: 10;
        transition: top 0.3s;
    }

    .room-img-container {
        position: relative;
        height: 280px;
        overflow: hidden;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .room-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .room-img-container:hover img {
        transform: scale(1.05);
    }

    .promo-badge {
        position: absolute;
        top: 15px;
        left: 0;
        background: linear-gradient(45deg, #dc3545, #ff6b6b);
        color: white;
        padding: 5px 15px;
        font-weight: bold;
        font-size: 0.9rem;
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
    }

    .amenity-icon {
        width: 35px;
        height: 35px;
        background-color: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d6efd;
        margin-right: 10px;
    }

    /* Hide island hop checkbox until a boat is selected */
    .include-island {
        display: none;
        margin-left: auto;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<body>
    <?php include './template/navbar.php'; ?>

    <div class="container mt-5 mb-5">

        <div class="d-lg-none mb-4">
            <div class="card shadow-sm border-0 bg-primary text-white rounded-4" data-bs-toggle="collapse"
                href="#mobileRoomInfo" role="button">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i> Booking Summary</h6>
                        <small><?= htmlspecialchars($room['room_name']) ?></small>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold">₱<?= number_format($final_price, 2) ?></span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </div>
                </div>
            </div>
            <div class="collapse mt-2" id="mobileRoomInfo">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <img src="./../user/admin/<?= htmlspecialchars($room['image']) ?>" class="img-fluid"
                        style="max-height: 200px; object-fit: cover;">
                    <div class="card-body bg-light">
                        <p class="small text-muted mb-0"><?= htmlspecialchars($room['room_description']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-lg-4 d-none d-lg-block">
                <div class="card shadow-lg border-0 rounded-4 room-card-sticky">
                    <div class="room-img-container">
                        <img src="./../user/admin/<?= htmlspecialchars($room['image']) ?>" alt="Room Image">

                        <?php if ($active_discount > 0): ?>
                            <div class="promo-badge">
                                <i class="fas fa-tags me-1"></i> <?= $active_discount ?>% OFF PROMO
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-4">
                        <h3 class="text-primary fw-bold mb-1"><?= htmlspecialchars($room['room_name']) ?></h3>
                        <h6 class="text-uppercase text-muted fw-bold mb-3">
                            <?= htmlspecialchars($room['room_type_name']) ?>
                        </h6>

                        <div class="mb-4 p-3 bg-light rounded-3">
                            <?php if ($active_discount > 0): ?>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span
                                        class="text-muted text-decoration-line-through">₱<?= number_format($orig_price, 2) ?></span>
                                    <span class="badge bg-danger">Save <?= $active_discount ?>%</span>
                                </div>
                                <div class="fs-2 fw-bold text-success">₱<?= number_format($final_price, 2) ?> <small
                                        class="fs-6 text-muted text-dark">/ night</small></div>
                                <small class="text-danger fw-bold"><i class="fas fa-fire me-1"></i>
                                    <?= htmlspecialchars($promo_name) ?> Applied!</small>
                            <?php else: ?>
                                <div class="fs-2 fw-bold text-dark">₱<?= number_format($orig_price, 2) ?> <small
                                        class="fs-6 text-muted">/ night</small></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="amenity-icon"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <small class="text-muted d-block">Capacity</small>
                                <strong>Max <?= $room['max_guest'] ?> Guests</strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="amenity-icon"><i class="bi bi-door-open-fill"></i></div>
                            <div>
                                <small class="text-muted d-block">Availability</small>
                                <strong><?= $room['available'] ?> Rooms Left</strong>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25">
                        <p class="text-muted small mb-0">
                            <?= htmlspecialchars($room['room_description']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-12">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <div class="d-flex mb-5 pb-2 border-bottom overflow-auto" style="white-space: nowrap;">
                            <div class="step active">Step 1 <br><small>Info</small></div>
                            <div class="step">Step 2 <br><small>Events</small></div>
                            <div class="step">Step 3 <br><small>Boat</small></div>
                            <div class="step">Step 4 <br><small>Services</small></div>
                            <div class="step">Step 5 <br><small>Rental</small></div>
                            <div class="step">Step 6 <br><small>Payment</small></div>
                            <div class="step">Step 7 <br><small>Confirm</small></div>
                        </div>

                        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Tip:</strong> You can click on any step above to navigate directly! ✓ marks
                            completed steps.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <form id="multiStepForm" enctype="multipart/form-data" method="POST">
                            <input type="hidden" name="room_id" value="<?= $room_id ?>">

                            <div class="form-step">
                                <h4 class="mb-4 text-primary fw-bold">Guest Information</h4>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Last Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg required" required
                                            oninput="this.value = this.value.toUpperCase(); updateFullName();"
                                            name="guest_last_name" placeholder="Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">First Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg required" required
                                            oninput="this.value = this.value.toUpperCase(); updateFullName();"
                                            name="guest_first_name" placeholder="Juan">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Middle Name</label>
                                        <input type="text" class="form-control form-control-lg"
                                            oninput="this.value = this.value.toUpperCase(); updateFullName();"
                                            name="guest_middle_name" placeholder="Santos">
                                    </div>
                                    <input type="hidden" name="guest_name" id="guest_name_hidden">
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Contact Number</label>
                                        <input type="number" class="form-control form-control-lg required"
                                            oninput="this.value = this.value.slice(0, 11);" name="guest_phone"
                                            placeholder="09123456789">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <input type="email" class="form-control form-control-lg required"
                                            name="guest_email" placeholder="email@example.com">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Check-in - Check-out</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-calendar-range"></i></span>
                                            <input type="text" id="date_range"
                                                class="form-control form-control-lg bg-white" placeholder="Select Dates"
                                                readonly>
                                        </div>
                                        <input type="hidden" name="checkin" id="checkin">
                                        <input type="hidden" name="checkout" id="checkout">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Guests</label>
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center form-control-lg"
                                                type="button" id="guestDropdown" data-bs-toggle="dropdown"
                                                data-bs-auto-close="outside" aria-expanded="false">
                                                <span id="guestSummary">1 Adult, 0 Children, 0 Seniors</span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="dropdown-menu p-3 w-100 shadow border-0 rounded-3"
                                                aria-labelledby="guestDropdown" style="min-width: 300px;">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">Adults</h6><small
                                                            class="text-muted">Ages 18-59</small>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-circle"
                                                            onclick="updateCount('adult', -1)"
                                                            style="width:32px;height:32px;">-</button>
                                                        <span class="mx-3 fw-bold" id="adultCount">1</span>
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-circle"
                                                            onclick="updateCount('adult', 1)"
                                                            style="width:32px;height:32px;">+</button>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">Children</h6><small
                                                            class="text-muted">Ages &lt;18</small>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-circle"
                                                            onclick="updateCount('child', -1)"
                                                            style="width:32px;height:32px;">-</button>
                                                        <span class="mx-3 fw-bold" id="childCount">0</span>
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-circle"
                                                            onclick="updateCount('child', 1)"
                                                            style="width:32px;height:32px;">+</button>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">Seniors</h6><small
                                                            class="text-muted">Ages 60+</small>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-circle"
                                                            onclick="updateCount('senior', -1)"
                                                            style="width:32px;height:32px;">-</button>
                                                        <span class="mx-3 fw-bold" id="seniorCount">0</span>
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-circle"
                                                            onclick="updateCount('senior', 1)"
                                                            style="width:32px;height:32px;">+</button>
                                                    </div>
                                                </div>
                                                <div id="adultAgesContainer" class="border-top pt-3 mt-2"><small
                                                        class="text-muted d-block mb-2 fw-bold">Adult Details:</small>
                                                </div>
                                                <div id="childAgesContainer" class="border-top pt-3 mt-2 d-none"><small
                                                        class="text-muted d-block mb-2 fw-bold">Child Details:</small>
                                                </div>
                                                <div id="seniorAgesContainer" class="border-top pt-3 mt-2 d-none"><small
                                                        class="text-muted d-block mb-2 fw-bold">Senior Details:</small>
                                                </div>
                                                 
                                                <input type="hidden" name="adults" id="inputAdults" value="1">
                                                <input type="hidden" name="children" id="inputChildren" value="0">
                                                <input type="hidden" name="seniors" id="inputSeniors" value="0">
                                                <input type="hidden" id="totalGuests" name="guests" value="1">
                                                <input type="hidden" id="inputGender" name="inputGender" value="Male">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tourist Type</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="tourist_type" id="touristLocal"
                                                value="Local" checked>
                                            <label class="btn btn-outline-primary w-50" for="touristLocal">Local</label>
                                            <input type="radio" class="btn-check" name="tourist_type"
                                                id="touristForeign" value="Foreign">
                                            <label class="btn btn-outline-primary w-50"
                                                for="touristForeign">Foreign</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-primary bg-opacity-10 border-0 rounded-3 mt-4">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-primary">Total Room Cost</h6>
                                            <small class="text-muted"><span id="nights_display">0</span> Nights
                                                Stay</small>
                                        </div>
                                        <div class="text-end">
                                            <input type="text"
                                                class="fw-bold fs-4 bg-transparent border-0 text-end text-primary"
                                                style="width: 150px;" id="room_cost" name="room_cost" readonly
                                                placeholder="₱0.00">
                                            <input type="hidden" id="nights" name="totalNights">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-step d-none">
                                <h4 class="mb-4 text-primary fw-bold">Resort Events</h4>
                                <p class="text-muted mb-4">Select events you'd like to attend during your stay:</p>
                                <div class="row g-3" id="eventsContainer">
                                    <?php
                                    $today = date('Y-m-d');
                                    $events_query = mysqli_query($conn, "SELECT * FROM event_tbl WHERE event_date >= '$today'ORDER BY event_date ASC");
                                    if (mysqli_num_rows($events_query) > 0) {
                                        while ($event = mysqli_fetch_assoc($events_query)) {
                                            $event_datetime = strtotime($event['event_date'] . ' ' . $event['event_time']);
                                            $formatted_date = date("F d, Y", strtotime($event['event_date']));
                                            $formatted_time = date("h:i A", $event_datetime);
                                            $formatted_end = !empty($event['event_end_time']) ? date("h:i A", strtotime($event['event_date'] . ' ' . $event['event_end_time'])) : '';
                                            ?>
                                            <div class="col-md-6">
                                                <div class="card event-option-card border-2 cursor-pointer"
                                                    style="cursor: pointer; transition: all 0.3s; border-color: #e9ecef;">
                                                    <div class="card-body">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input event-checkbox" type="checkbox"
                                                                name="events[]" value="<?= $event['event_id'] ?>"
                                                                id="event<?= $event['event_id'] ?>"
                                                                data-event-name="<?= htmlspecialchars($event['event_name']) ?>"
                                                                data-guests="1">
                                                            <label class="form-check-label w-100"
                                                                for="event<?= $event['event_id'] ?>" style="cursor: pointer;">
                                                                <h6 class="fw-bold mb-2" style="color: #0d6efd;">
                                                                    <?= htmlspecialchars($event['event_name']) ?>
                                                                </h6>
                                                                <small class="text-muted d-block">📅
                                                                    <?= $formatted_date ?></small>
                                                                <small class="text-muted d-block">🕐
                                                                    <?= $formatted_time ?>
                                                                    <?php if ($formatted_end)
                                                                        echo " - " . $formatted_end; ?></small>
                                                                <small
                                                                    class="text-muted d-block mt-2"><?= htmlspecialchars(substr($event['description'], 0, 50)) ?>...</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    } else {
                                        echo '<div class="col-12"><p class="text-muted text-center">No events available for your booking period</p></div>';
                                    }
                                    ?>
                                </div>

                                <div id="selectedEventsInfo" class="mt-4 d-none">
                                    <h6 class="fw-bold mb-3">Your Selected Events:</h6>
                                    <div id="selectedEventsList"></div>
                                </div>
                            </div>

                            <div class="form-step d-none">
                                <h4 class="mb-4 text-primary fw-bold">Boat Rental</h4>
                                <p class="text-muted mb-4">Pumili ng byahe para sa inyong island adventure. Pwede rin
                                    mag-add ng island hopping!</p>

                                <div class="table-responsive bg-white rounded-4 shadow-sm border">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Select</th>
                                                <th>Destination</th>
                                                <th>Scope </th>
                                                <th>Duration</th>
                                                <th>Guests</th>
                                                <th>Base Fee</th>
                                                <th class="pe-4">Island Hop</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $boat_rental_fee = $conn->query("SELECT * FROM boat_rental_fee_tbl");
                                            if ($boat_rental_fee->num_rows > 0) {
                                                while ($boat = $boat_rental_fee->fetch_assoc()) {
                                                    ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="form-check form-switch fs-5 mb-0">
                                                                <input class="form-check-input boat-check shadow-none"
                                                                    type="checkbox" value="<?= $boat['rental_id'] ?>"
                                                                    data-boat="<?= $boat['amount'] ?>"
                                                                    data-island="<?= $boat['island_hopping_amount'] ?>"
                                                                    style="cursor: pointer;">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold text-dark fs-6">
                                                                <?= htmlspecialchars($boat['destination']) ?>
                                                            </div>
                                                            <?php if ($boat['is_vice_versa'] == 1): ?>
                                                                <span class="badge bg-info text-dark rounded-pill"
                                                                    style="font-size: 0.7em;">Vice Versa</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted"><i
                                                                    class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($boat['description'] ?: 'No Set Scope') ?></small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border"><i
                                                                    class="bi bi-clock me-1"></i><?= $boat['num_days'] ?>
                                                                Day/s</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border"><i
                                                                    class="bi bi-people-fill me-1"></i><?= $boat['min_guest'] . '-' . $boat['max_guest'] ?></span>
                                                        </td>
                                                        <td><strong
                                                                class="text-primary">₱<?= number_format($boat['amount'], 2) ?></strong>
                                                        </td>
                                                        <td class="pe-4">
                                                            <?php if ($boat['island_hopping_amount'] > 0): ?>
                                                                <div class="form-check">
                                                                    <input type="checkbox"
                                                                        class="include-island form-check-input border-secondary shadow-none"
                                                                        style="cursor: pointer;">
                                                                    <label class="form-check-label small fw-bold text-success">
                                                                        +₱<?= number_format($boat['island_hopping_amount'], 2) ?>
                                                                    </label>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="small text-muted fst-italic">Not Available</span>
                                                                <input type="checkbox"
                                                                    class="include-island form-check-input d-none">
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div
                                    class="d-flex justify-content-end align-items-center mt-4 p-3 bg-light rounded-3 border">
                                    <h5 class="mb-0 me-3 text-muted">Total Boat Fee:</h5>
                                    <h3 class="mb-0 text-success fw-bold">₱<span id="totalAmount">0.00</span></h3>
                                </div>
                            </div>
                            <div class="form-step d-none">
                                <h4 class="mb-4 text-primary fw-bold">Additional Services</h4>
                                <div class="row g-3">
                                    <?php
                                    $services_query = $conn->query("SELECT * FROM services_tbl");
                                    while ($service = $services_query->fetch_assoc()) { ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 border rounded-3 p-3 d-flex flex-row align-items-center">
                                                <div class="form-check me-3">
                                                    <input class="form-check-input service-check" type="checkbox"
                                                        name="services[]" value="<?= $service['service_id'] ?>"
                                                        data-price="<?= $service['service_price'] ?>">
                                                </div>
                                                <img src="./../user/admin/uploads/<?= $service['service_image'] ?>"
                                                    style="width:60px; height:60px; object-fit:cover; border-radius:8px;"
                                                    class="me-3">
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?= $service['service_name'] ?></h6>
                                                    <small
                                                        class="text-muted">₱<?= number_format($service['service_price'], 2) ?></small>
                                                    <?php if (!empty($service['service_description']) || !empty($service['service_inclusions'])): ?>
                                                        <div class="service-details mt-2 d-none small text-muted">
                                                            <?php if (!empty($service['service_description'])): ?>
                                                                <div class="mb-1">
                                                                    <?= nl2br(htmlspecialchars($service['service_description'])) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (!empty($service['service_inclusions'])): ?>
                                                                <div class="fw-bold">Inclusions:</div>
                                                                <ul class="mb-0">
                                                                    <?php foreach (explode(',', $service['service_inclusions']) as $inc) {
                                                                        echo '<li>' . htmlspecialchars(trim($inc)) . '</li>';
                                                                    } ?>
                                                                </ul>
                                                            <?php endif; ?>
                                                        </div>
                                                        <a href="#" class="service-detail-toggle small">View details</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-step d-none">
                                <h4 class="mb-4 text-primary fw-bold">Equipment Rentals</h4>
                                <div class="row g-3">
                                    <?php
                                    $rental_query = $conn->query("SELECT * FROM rentals_tbl");
                                    while ($rental = $rental_query->fetch_assoc()) { ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 border rounded-3 p-3 d-flex flex-row align-items-center">
                                                <div class="form-check me-3">
                                                    <div class="rental-duration mt-2 d-none">
                                                        <label class="small text-muted">Duration</label>
                                                        <select class="form-select form-select-sm rental-duration-select"
                                                            data-base-hours="<?= $rental['hours'] ?>" style="width:120px;">
                                                            <?php
                                                            $base = (int) $rental['hours'];
                                                            $maxBlocks = 8; // allow up to 8 blocks (adjustable)
                                                            for ($m = 1; $m <= $maxBlocks; $m++) {
                                                                $hrs = $base * $m;
                                                                echo "<option value=\"$m\">{$hrs} hrs ({$m}x)</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <input class="form-check-input rental-check" type="checkbox"
                                                        name="rentals[]" value="<?= $rental['rental_id'] ?>"
                                                        data-price="<?= $rental['rental_price'] ?>">
                                                </div>
                                                <img src="./../user/admin/uploads/rentals/<?= $rental['rental_image'] ?>"
                                                    style="width:60px; height:60px; object-fit:cover; border-radius:8px;"
                                                    class="me-3">
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?= $rental['rental_name'] ?></h6>
                                                    <small
                                                        class="text-muted">₱<?= number_format($rental['rental_price'], 2) ?>
                                                        / <?= $rental['hours'] ?>hr</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-step d-none">
                                <h4 class="mb-4 text-primary fw-bold">Payment Details</h4>

                                <!-- Summary of All Selected Items -->
                                <div class="card border-0 bg-white mb-4 shadow-sm">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center"
                                        style="cursor: pointer;" data-bs-toggle="collapse" href="#summaryPanel">
                                        <h6 class="mb-0 fw-bold">Booking Summary</h6>
                                        <i class="bi bi-chevron-down"></i>
                                    </div>
                                    <div class="collapse show" id="summaryPanel">
                                        <div class="card-body">
                                            <!-- Events Summary -->
                                            <div class="mb-3">
                                                <h6 class="fw-bold text-primary mb-2">Events</h6>
                                                <div id="summary_events" class="small text-muted ps-3">
                                                    <p class="mb-0">No events selected</p>
                                                </div>
                                            </div>

                                            <!-- Boat Summary -->
                                            <div class="mb-3">
                                                <h6 class="fw-bold text-primary mb-2">Boat Rental</h6>
                                                <div id="summary_boat" class="small text-muted ps-3">
                                                    <p class="mb-0">No boat selected</p>
                                                </div>
                                            </div>

                                            <!-- Services Summary -->
                                            <div class="mb-3">
                                                <h6 class="fw-bold text-primary mb-2">Services</h6>
                                                <div id="summary_services" class="small text-muted ps-3">
                                                    <p class="mb-0">No services selected</p>
                                                </div>
                                            </div>

                                            <!-- Rentals Summary -->
                                            <div class="mb-3">
                                                <h6 class="fw-bold text-primary mb-2">Equipment Rentals</h6>
                                                <div id="summary_rentals" class="small text-muted ps-3">
                                                    <p class="mb-0">No rentals selected</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light p-4 rounded-4 mb-4 border">
                                    <div class="d-flex justify-content-between mb-2 text-muted"><span>Room Fee</span>
                                        <input class="border-0 bg-transparent text-end fw-bold text-dark"
                                            id="room_payment" readonly>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2 text-primary">
                                        <span>Entrance Fee (<span id="guest_summary_count">1</span> Guests)</span>
                                        <input class="border-0 bg-transparent text-end fw-bold text-primary"
                                            id="entrance_payment" readonly>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2 text-muted"><span>Boat Fee</span>
                                        <input class="border-0 bg-transparent text-end" id="boat_rentals_payment"
                                            readonly>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 text-muted"><span>Services
                                            Fee</span> <input class="border-0 bg-transparent text-end"
                                            id="services_payment" readonly></div>
                                    <div class="d-flex justify-content-between mb-2 text-muted"><span>Rentals Fee</span>
                                        <input class="border-0 bg-transparent text-end" id="rentals_payment" readonly>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-bold">Total Amount</span>
                                        <input type="text" name="total_payments"
                                            class="fs-4 fw-bold text-dark text-end border-0 bg-transparent"
                                            id="total_payment" readonly>
                                        <input type="hidden" name="total_entrance_fee" id="total_entrance_fee_value">
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Payment Method</label>
                                        <select class="form-select form-select-lg required" id="payment_type"
                                            name="payment_type">
                                            <option value="">Select Method</option>
                                            <?php
                                            $query = $conn->query("SELECT * FROM payment_type_tbl");
                                            while ($row = $query->fetch_assoc()) {
                                                echo "<option value='{$row['payment_type_id']}' data-number='{$row['payment_type_number']}'>{$row['payment_type_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-muted">Account Number</label>
                                        <input type="text" class="form-control form-control-lg" id="payment_number"
                                            style="pointer-events: none; user-select: none; background-color: #e9ecef; color: #6c757d;"
                                            disabled readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary">Payment Option</label>
                                        <select class="form-select form-select-lg required border-primary"
                                            id="payment_option" name="payment_option">
                                            <option value="full">Full Payment</option>
                                            <option value="downpayment">50% Downpayment</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Reference No.</label>
                                        <input type="text" class="form-control form-control-lg required"
                                            name="reference_number" placeholder="Enter Ref No.">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Amount to Pay Now</label>
                                        <input type="text"
                                            class="form-control form-control-lg fw-bold text-success bg-light"
                                            id="amount_to_pay_display" readonly>
                                        <input type="hidden" name="final_payable" id="final_payable">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Proof of Payment</label>
                                        <input type="file" class="form-control form-control-lg required"
                                            name="payment_screenshot" accept="image/*">
                                    </div>
                                </div>

                                <div class="alert alert-info mt-3 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill fs-4 me-2"></i>
                                    <div>
                                        A minimum of <strong>50% downpayment</strong> is required to confirm your
                                        reservation.
                                        Balance can be paid upon arrival.
                                    </div>
                                </div>
                            </div>

                            <div class="form-step d-none text-center py-4">
                                <i class="bi bi-check-circle-fill text-success display-1"></i>
                                <h3 class="mt-3 fw-bold">Almost Done!</h3>
                                <p class="text-muted">Please review your details. By clicking confirm, you agree to our
                                    terms.</p>

                                <div class="form-check d-inline-block text-start bg-light p-3 rounded-3 mt-3">
                                    <input class="form-check-input" type="checkbox" id="termsCheckbox">
                                    <label class="form-check-label" for="termsCheckbox">
                                        I agree to the <a href="#termsModal" data-bs-toggle="modal">Terms and
                                            Conditions</a>
                                    </label>
                                </div>

                                <div class="mt-4">
                                    <button type="button" id="confirmBookingBtn"
                                        class="btn btn-success btn-lg px-5 rounded-pill shadow" disabled>
                                        Confirm Booking
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                                <button type="button" id="prevBtn" class="btn btn-outline-secondary px-4 rounded-pill"
                                    disabled>Back</button>
                                <button type="button" id="nextBtn"
                                    class="btn btn-primary px-4 rounded-pill shadow-sm">Next</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height:400px; overflow-y:auto;">
                    <h6>Beach Front Resort</h6>
                    <p>Welcome to Beach Front Resort! By booking a stay, visiting, or using our facilities, you agree to
                        comply with the following Terms and Conditions.</p>

                    <h6>1. Reservations & Payments</h6>
                    <ol>
                        <li>Reservations can be made through our official booking channels.</li>
                        <li>A reservation is only confirmed upon receipt of full payment via GCash or Bank Transfer.
                        </li>
                        <li>All rates are subject to change without prior notice but will not affect confirmed bookings.
                        </li>
                    </ol>

                    <h6>2. Check-in & Check-out</h6>
                    <ol>
                        <li>For early check-in at 8:00 AM, check-out time is set at 5:00 AM the next day (21-hour stay).
                        </li>
                        <li>Early check-in (12:00 NN) or late check-out is subject to room availability and extra
                            charges.</li>
                        <li>Guests must present reservation copy and payment proof upon check-in.</li>
                    </ol>

                    <h6>3. Cancellations & Refunds</h6>
                    <ol>
                        <li>Cancellations made at least 7 days before arrival are eligible for a full refund.</li>
                        <li>Cancellations 1–2 days before arrival are non-refundable.</li>
                        <li>No-shows are strictly non-refundable.</li>
                        <li>Refunds processed within 14 business days via original payment method.</li>
                    </ol>

                    <h6>4. Rooms & Capacity</h6>
                    <ol>
                        <li>Rooms accommodate 2-15 persons depending on size.</li>
                        <li>Extra bed requests allowed at ₱200/set.</li>
                    </ol>

                    <h6>5. General Policies</h6>
                    <ol>
                        <li>Maintain cleanliness and proper conduct.</li>
                        <li>Damages to property will be charged accordingly.</li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. GUEST DROPDOWN LOGIC
        let adults = 1;
        let children = 0;
        let seniors = 0;
        const maxGuests = <?= $room['max_guest'] ?>;

        // ensure age selectors reflect the default counts
        document.addEventListener('DOMContentLoaded', syncAgeSelectors);

        function updateCount(type, change) {
            const total = adults + children + seniors;
            if (type === 'adult') {
                if (change === 1 && total < maxGuests) adults++;
                if (change === -1 && adults > 1) adults--;
            } else if (type === 'child') {
                if (change === 1 && total < maxGuests) {
                    children++;
                }
                if (change === -1 && children > 0) {
                    children--;
                }
            } else if (type === 'senior') {
                if (change === 1 && total < maxGuests) seniors++;
                if (change === -1 && seniors > 0) seniors--;
            }
            // Update UI & Hidden Inputs
            document.getElementById('adultCount').innerText = adults;
            document.getElementById('childCount').innerText = children;
            document.getElementById('seniorCount').innerText = seniors;
            document.getElementById('inputAdults').value = adults;
            document.getElementById('inputChildren').value = children;
            document.getElementById('inputSeniors').value = seniors;
            document.getElementById('totalGuests').value = adults + children + seniors;

            let summary = `${adults} Adult${adults > 1 ? 's' : ''}`;
            if (children > 0) summary += `, ${children} Child${children > 1 ? 'ren' : ''}`;
            if (seniors > 0) summary += `, ${seniors} Senior${seniors > 1 ? 's' : ''}`;
            document.getElementById('guestSummary').innerText = summary;

            const ageContainer = document.getElementById('childAgesContainer');
            children > 0 ? ageContainer.classList.remove('d-none') : ageContainer.classList.add('d-none');

            // keep age selector lists synchronized
            syncAgeSelectors();
        }

        function addChildAgeSelect(index) {
            const container = document.getElementById('childAgesContainer');
            const div = document.createElement('div');

            // DITO MO ILALAGAY YUNG PARA SA CHILD
            div.className = 'mb-2 d-flex align-items-center justify-content-between gap-2 child-row-entry';

            let ageOps = '<option value="" disabled selected>Age</option>';
            for (let i = 0; i <= 17; i++) ageOps += `<option value="${i}">${i}</option>`;

            div.innerHTML = `
        <span class="small fw-bold">Child ${index}</span>
        <select class="form-control form-control-sm w-auto required" name="child_ages[]">${ageOps}</select>
        <select class="form-control form-control-sm w-auto required" name="child_genders[]">
            <option value="" disabled selected>Gender</option>
            <option value="Male">Male</option><option value="Female">Female</option>
        </select>`;
            container.appendChild(div);
        }

        function removeChildAgeSelect() {
            const container = document.getElementById('childAgesContainer');
            if (container.lastChild) container.removeChild(container.lastChild);
        }

        // Adult age selectors (18-59)
        function addAdultAgeSelect(index) {
            const container = document.getElementById('adultAgesContainer');
            const div = document.createElement('div');

            // DITO MO ILALAGAY YUNG PARA SA ADULT
            div.className = 'mb-2 d-flex align-items-center justify-content-between gap-2 adult-row-entry';

            let ageOps = '<option value="" disabled selected>Age</option>';
            for (let i = 18; i <= 59; i++) ageOps += `<option value="${i}">${i}</option>`;

            div.innerHTML = `
        <span class="small fw-bold">Adult ${index}</span>
        <select class="form-control form-control-sm w-auto required" name="adult_ages[]">${ageOps}</select>
        <select class="form-control form-control-sm w-auto required" name="adult_genders[]">
            <option value="" disabled selected>Gender</option>
            <option value="Male">Male</option><option value="Female">Female</option>
        </select>`;
            container.appendChild(div);
        }

        function removeAdultAgeSelect() {
            const container = document.getElementById('adultAgesContainer');
            if (container.lastChild) container.removeChild(container.lastChild);
        }

        // Senior age selectors (60+)
        function addSeniorAgeSelect(index) {
            const container = document.getElementById('seniorAgesContainer');
            const div = document.createElement('div');

            // DITO MO ILALAGAY YUNG PARA SA SENIOR
            div.className = 'mb-2 d-flex align-items-center justify-content-between gap-2 senior-row-entry';

            let ageOps = '<option value="" disabled selected>Age</option>';
            for (let i = 60; i <= 100; i++) ageOps += `<option value="${i}">${i}</option>`;

            div.innerHTML = `
        <span class="small fw-bold">Senior ${index}</span>
        <select class="form-control form-control-sm w-auto required" name="senior_ages[]">${ageOps}</select>
        <select class="form-control form-control-sm w-auto required" name="senior_genders[]">
            <option value="" disabled selected>Gender</option>
            <option value="Male">Male</option><option value="Female">Female</option>
        </select>`;
            container.appendChild(div);
        }

        function removeSeniorAgeSelect() {
            const container = document.getElementById('seniorAgesContainer');
            if (container.lastChild) container.removeChild(container.lastChild);
        }

        // Ensure the number of age selectors matches the counts
        function syncAgeSelectors() {
            const aC = document.getElementById('adultAgesContainer');
            const cC = document.getElementById('childAgesContainer');
            const sC = document.getElementById('seniorAgesContainer');

            // ADULTS: Magsisimula na ito sa 1 dahil sa querySelector logic
            while (aC.querySelectorAll('.adult-row-entry').length < adults) {
                addAdultAgeSelect(aC.querySelectorAll('.adult-row-entry').length + 1);
            }
            while (aC.querySelectorAll('.adult-row-entry').length > adults) {
                aC.removeChild(aC.lastChild);
            }

            // CHILDREN
            while (cC.querySelectorAll('.child-row-entry').length < children) {
                addChildAgeSelect(cC.querySelectorAll('.child-row-entry').length + 1);
            }
            while (cC.querySelectorAll('.child-row-entry').length > children) {
                cC.removeChild(cC.lastChild);
            }
            cC.classList.toggle('d-none', children === 0);

            // SENIORS
            while (sC.querySelectorAll('.senior-row-entry').length < seniors) {
                addSeniorAgeSelect(sC.querySelectorAll('.senior-row-entry').length + 1);
            }
            while (sC.querySelectorAll('.senior-row-entry').length > seniors) {
                sC.removeChild(sC.lastChild);
            }
            sC.classList.toggle('d-none', seniors === 0);
        }

        // 2. ROOM COST LOGIC (With Discount)
        const roomPrice = <?= $room['price'] ?>;
        const discountPercent = <?= $active_discount ?>;
        const entranceRate = <?= $entrance_rate ?>; // Galing sa PHP

        function calculateRoomCost() {
            let checkinVal = document.getElementById("checkin").value;
            let checkoutVal = document.getElementById("checkout").value;

            if (checkinVal && checkoutVal) {
                let d1 = new Date(checkinVal);
                let d2 = new Date(checkoutVal);

                if (d2 > d1) {
                    let diff = d2 - d1;
                    let nights = diff / (1000 * 3600 * 24);

                    document.getElementById("nights").value = nights;
                    document.getElementById("nights_display").textContent = nights;

                    let total = nights * roomPrice;
                    if (discountPercent > 0) {
                        total = total - (total * (discountPercent / 100));
                    }

                    document.getElementById("room_cost").value = "₱" + total.toLocaleString(undefined, {
                        minimumFractionDigits: 2
                    });
                    document.getElementById("room_cost").setAttribute("data-value", total);
                    if (typeof calculateSummary === "function") calculateSummary();
                }
            }
        }

        flatpickr("#date_range", {
            mode: "range",
            minDate: "today",
            dateFormat: "Y-m-d",
            showMonths: 2,
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    document.getElementById("checkin").value = instance.formatDate(selectedDates[0], "Y-m-d");
                    document.getElementById("checkout").value = instance.formatDate(selectedDates[1], "Y-m-d");
                    calculateRoomCost();
                }
            }
        });

        // 3. STEP LOGIC & PAYMENT
        // automatically clear invalid styles when user types
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.required').forEach(input => {
                input.addEventListener('input', () => input.classList.remove('is-invalid'));
            });
            // disable island hop checkboxes initially
            document.querySelectorAll('.include-island').forEach(ii => {
                ii.disabled = true;
                ii.checked = false;
            });
            // Add gender radio button listeners
            document.querySelectorAll('input[name="gender"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    document.getElementById('inputGender').value = this.value;
                });
            });
        });
        const steps = document.querySelectorAll(".step");
        const formSteps = document.querySelectorAll(".form-step");
        const nextBtn = document.getElementById("nextBtn");
        const prevBtn = document.getElementById("prevBtn");
        let currentStep = 0;

        function showStep(step) {
            formSteps.forEach((fs, index) => {
                fs.classList.toggle("d-none", index !== step);
            });
            steps.forEach((s, index) => {
                s.classList.remove("active", "completed");
                if (index < step) s.classList.add("completed");
                if (index === step) s.classList.add("active");
            });
            prevBtn.disabled = step === 0;
            nextBtn.textContent = step === formSteps.length - 1 ? "" : "Next";

            if (step === 5) calculateSummary(); // Payment Step is now index 5 (after Events)

            // Scroll to top of form
            document.querySelector('.card-body').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function validateStep(step) {
            // Simple validation
            const inputs = formSteps[step].querySelectorAll(".required");
            let valid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add("is-invalid");
                    valid = false;
                } else {
                    input.classList.remove("is-invalid");
                }
            });
            return valid;
        }

        // Make steps clickable
        steps.forEach((step, index) => {
            step.addEventListener("click", () => {
                // Allow going backward without validation
                if (index < currentStep) {
                    currentStep = index;
                    showStep(currentStep);
                    return;
                }

                // For going forward, validate current step first
                if (index > currentStep) {
                    // Validate all steps from current to target step
                    for (let i = currentStep; i < index; i++) {
                        if (!validateStep(i)) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Complete Current Step',
                                text: 'Please fill in all required fields before moving to the next step',
                                confirmButtonText: 'OK'
                            });
                            return;
                        }
                    }
                    currentStep = index;
                    showStep(currentStep);
                } else if (index === currentStep) {
                    // Already on this step
                    return;
                }
            });
        });

        nextBtn.addEventListener("click", () => {
            // update full name each time we advance so hidden value stays current
            updateFullName();
            if (!validateStep(currentStep)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Complete This Step',
                    text: 'Please fill in all required fields',
                    confirmButtonText: 'OK'
                });
                return;
            }
            if (currentStep < formSteps.length - 1) {
                currentStep++;
                showStep(currentStep);
            } else {
                // Not triggering submit here, handled by confirm button
            }
        });

        prevBtn.addEventListener("click", () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Summary Calculation
        function calculateSummary() {
            let roomCost = parseFloat(document.getElementById("room_cost").getAttribute("data-value")) || 0;

            // Entrance Fee (everyone pays)
            let totalGuestsCount = adults + children + seniors;
            let totalEntrance = totalGuestsCount * entranceRate;

            let serviceTotal = 0;
            document.querySelectorAll(".service-check:checked").forEach(cb => serviceTotal += parseFloat(cb.dataset.price));

            let rentalTotal = 0;
            document.querySelectorAll(".rental-check:checked").forEach(cb => {
                let basePrice = parseFloat(cb.dataset.price);
                let card = cb.closest('.card');
                let sel = card ? card.querySelector('.rental-duration-select') : null;
                let multiplier = 1;
                if (sel) multiplier = parseInt(sel.value) || 1;
                rentalTotal += basePrice * multiplier;
            });

            let boatTotal = 0;
            document.querySelectorAll(".boat-check:checked").forEach(cb => {
                let p = parseFloat(cb.dataset.boat);
                let island = parseFloat(cb.dataset.island);
                let inc = cb.closest('tr').querySelector('.include-island').checked;
                boatTotal += inc ? (p + island) : p;
            });

            // UPDATE SUMMARY PANELS
            // Events Summary
            let selectedEvents = document.querySelectorAll('.event-checkbox:checked');
            let eventsHtml = '';
            if (selectedEvents.length > 0) {
                eventsHtml = '<ul class="list-unstyled mb-0">';
                selectedEvents.forEach(cb => {
                    eventsHtml += `<li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i>${cb.dataset.eventName}</li>`;
                });
                eventsHtml += '</ul>';
            } else {
                eventsHtml = '<p class="mb-0 text-muted">No events selected</p>';
            }
            document.getElementById('summary_events').innerHTML = eventsHtml;

            // Boat Summary
            let boatSummaryHtml = '';
            let boatChecked = document.querySelector('.boat-check:checked');
            if (boatChecked) {
                let boatRow = boatChecked.closest('tr');
                let dest = boatRow.cells[1].textContent.trim();
                let price = parseFloat(boatChecked.dataset.boat);
                let islandPrice = parseFloat(boatChecked.dataset.island);
                let hasIsland = boatRow.querySelector('.include-island').checked;
                let total = hasIsland ? price + islandPrice : price;
                boatSummaryHtml = `
                    <div class="mb-1"><strong>${dest}</strong></div>
                    <div class="mb-1">Base: ₱${price.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                if (hasIsland) {
                    boatSummaryHtml += `<br>Island Hopping: +₱${islandPrice.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                    boatSummaryHtml += `<br><span class="text-success fw-bold">Total: ₱${total.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>`;
                } else {
                    boatSummaryHtml += `<br><span class="text-success fw-bold">Total: ₱${total.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>`;
                }
                boatSummaryHtml += '</div>';
            } else {
                boatSummaryHtml = '<p class="mb-0 text-muted">No boat selected</p>';
            }
            document.getElementById('summary_boat').innerHTML = boatSummaryHtml;

            // Services Summary
            let selectedServices = document.querySelectorAll('.service-check:checked');
            let servicesHtml = '';
            if (selectedServices.length > 0) {
                servicesHtml = '<ul class="list-unstyled mb-0">';
                selectedServices.forEach(cb => {
                    let name = cb.closest('.card').querySelector('h6').textContent.trim();
                    let price = parseFloat(cb.dataset.price);
                    servicesHtml += `<li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i>${name} - ₱${price.toLocaleString(undefined, { minimumFractionDigits: 2 })}</li>`;
                });
                servicesHtml += '</ul>';
            } else {
                servicesHtml = '<p class="mb-0 text-muted">No services selected</p>';
            }
            document.getElementById('summary_services').innerHTML = servicesHtml;

            // Rentals Summary
            let selectedRentals = document.querySelectorAll('.rental-check:checked');
            let rentalsHtml = '';
            if (selectedRentals.length > 0) {
                rentalsHtml = '<ul class="list-unstyled mb-0">';
                selectedRentals.forEach(cb => {
                    let card = cb.closest('.card');
                    let name = card.querySelector('h6').textContent.trim();
                    let basePrice = parseFloat(cb.dataset.price);
                    let sel = card.querySelector('.rental-duration-select');
                    let multiplier = sel ? (parseInt(sel.value) || 1) : 1;
                    let baseHours = sel ? (parseInt(sel.dataset.baseHours) || 1) : 1;
                    let totalHours = baseHours * multiplier;
                    let itemTotal = basePrice * multiplier;
                    rentalsHtml += `<li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i><strong>${name}</strong><br><small>₱${basePrice.toLocaleString(undefined, { minimumFractionDigits: 2 })} per ${baseHours} hrs × ${multiplier} = ${totalHours} hrs = <span class="text-success fw-bold">₱${itemTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span></small></li>`;
                });
                rentalsHtml += '</ul>';
            } else {
                rentalsHtml = '<p class="mb-0 text-muted">No rentals selected</p>';
            }
            document.getElementById('summary_rentals').innerHTML = rentalsHtml;

            // Update visible total amount in Step 3 table
            const totalAmountEl = document.getElementById('totalAmount');
            if (totalAmountEl) {
                totalAmountEl.innerText = boatTotal.toLocaleString(undefined, { minimumFractionDigits: 2 });
            }

            // Displays
            document.getElementById("room_payment").value = "₱" + roomCost.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            document.getElementById("services_payment").value = "₱" + serviceTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            document.getElementById("rentals_payment").value = "₱" + rentalTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            document.getElementById("boat_rentals_payment").value = "₱" + boatTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });

            // Entrance Display
            document.getElementById("guest_summary_count").innerText = totalGuestsCount;
            document.getElementById("entrance_payment").value = "₱" + totalEntrance.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            document.getElementById("total_entrance_fee_value").value = totalEntrance;

            // Grand Total
            let total = roomCost + totalEntrance + serviceTotal + rentalTotal + boatTotal;
            document.getElementById("total_payment").value = "₱" + total.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });

            // Calculate Payable Amount (Downpayment vs Full)
            const paymentOption = document.getElementById("payment_option").value;
            let payable = total;

            if (paymentOption === 'downpayment') {
                payable = total * 0.5;
            }

            document.getElementById("amount_to_pay_display").value = "₱" + payable.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            document.getElementById("final_payable").value = payable;
        }

        // Attach listeners for dynamic calculation
        // General: recalc when any relevant checkbox/input changes
        document.querySelectorAll("input[type=checkbox]").forEach(cb => {
            cb.addEventListener('change', calculateSummary);
        });

        document.getElementById("payment_option").addEventListener("change", calculateSummary);

        // Boat selection: allow only one selection at a time. When a boat is selected,
        // disable other boat checkboxes and show the island-hop option only for the selected row.
        document.querySelectorAll('.boat-check').forEach(cb => {
            cb.addEventListener('change', function () {
                if (this.checked) {
                    // disable other boat choices
                    document.querySelectorAll('.boat-check').forEach(o => {
                        if (o !== this) o.disabled = true;
                    });

                    // show island hop only for this row
                    const inc = this.closest('tr').querySelector('.include-island');
                    if (inc) {
                        inc.style.display = 'inline-block';
                        inc.disabled = false;
                    }

                    // hide island hop for other rows
                    document.querySelectorAll('.include-island').forEach(ii => {
                        if (ii !== inc) {
                            ii.style.display = 'none';
                            ii.checked = false;
                            ii.disabled = true;
                        }
                    });
                } else {
                    // unchecked -> allow user to change selection
                    document.querySelectorAll('.boat-check').forEach(o => o.disabled = false);
                    document.querySelectorAll('.include-island').forEach(ii => {
                        ii.style.display = 'none';
                        ii.checked = false;
                        ii.disabled = true;
                    });
                }
                calculateSummary();
            });
        });

        // Service details toggles
        document.querySelectorAll('.service-detail-toggle').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const details = this.previousElementSibling;
                if (!details) return;
                details.classList.toggle('d-none');
                this.textContent = details.classList.contains('d-none') ? 'View details' : 'Hide details';
            });
        });

        // Rental selection: show duration select when rental is checked
        document.querySelectorAll('.rental-check').forEach(cb => {
            cb.addEventListener('change', function () {
                const card = this.closest('.card');
                const dur = card ? card.querySelector('.rental-duration') : null;
                const sel = card ? card.querySelector('.rental-duration-select') : null;
                if (this.checked) {
                    if (dur) {
                        dur.classList.remove('d-none');
                        if (sel) sel.disabled = false;
                    }
                    // Optionally disable other rentals if you want single rental selection
                } else {
                    if (dur) {
                        dur.classList.add('d-none');
                        if (sel) {
                            sel.disabled = true;
                            sel.value = '1';
                        }
                    }
                }
                calculateSummary();
            });
        });

        // Recalculate when rental duration changes
        document.querySelectorAll('.rental-duration-select').forEach(s => {
            s.addEventListener('change', calculateSummary);
        });

        // Initialize rental-duration selects as disabled/hidden
        document.querySelectorAll('.rental-duration').forEach(div => {
            const sel = div.querySelector('.rental-duration-select');
            if (sel) sel.disabled = true;
            div.classList.add('d-none');
        });
        // island hop toggles affect total as well
        document.querySelectorAll('.include-island').forEach(ii => {
            ii.addEventListener('change', calculateSummary);
        });

        // Event checkbox handling for visual feedback and summary
        document.querySelectorAll('.event-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const card = this.closest('.event-option-card');
                if (this.checked) {
                    card.style.borderColor = '#0d6efd';
                    card.style.backgroundColor = '#f0f6ff';
                } else {
                    card.style.borderColor = '#e9ecef';
                    card.style.backgroundColor = 'white';
                }
                updateSelectedEventsDisplay();
            });

            // Make the entire card clickable
            const card = checkbox.closest('.event-option-card');
            card.addEventListener('click', function (e) {
                if (e.target !== checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });

        function updateSelectedEventsDisplay() {
            const selectedEvents = document.querySelectorAll('.event-checkbox:checked');
            const selectedEventsList = document.getElementById('selectedEventsList');
            const selectedEventsInfo = document.getElementById('selectedEventsInfo');

            if (selectedEvents.length > 0) {
                selectedEventsInfo.classList.remove('d-none');
                selectedEventsList.innerHTML = '';

                selectedEvents.forEach(checkbox => {
                    const eventName = checkbox.dataset.eventName;
                    const eventId = checkbox.value;

                    const eventItem = document.createElement('div');
                    eventItem.className = 'alert alert-info alert-dismissible fade show mb-2';
                    eventItem.innerHTML = `
                        <strong>${eventName}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    selectedEventsList.appendChild(eventItem);
                });
            } else {
                selectedEventsInfo.classList.add('d-none');
            }
        }

        // Function to combine name inputs into hidden full name field
        function updateFullName() {
            const first = document.querySelector('input[name="guest_first_name"]').value.trim();
            const middle = document.querySelector('input[name="guest_middle_name"]').value.trim();
            const last = document.querySelector('input[name="guest_last_name"]').value.trim();
            let full = '';
            if (first) full += first;
            if (middle) full += (full ? ' ' : '') + middle;
            if (last) full += (full ? ' ' : '') + last;
            document.getElementById('guest_name_hidden').value = full;
        }

        // Confirm Button Logic
        const checkbox = document.getElementById("termsCheckbox");
        const confirmBtn = document.getElementById("confirmBookingBtn");
        checkbox.addEventListener('change', () => confirmBtn.disabled = !checkbox.checked);

        confirmBtn.addEventListener("click", function () {
            updateFullName();

            Swal.fire({
                title: "Confirm Booking?",
                text: "Do you want to confirm this reservation?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, Book Now!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById("multiStepForm");
                    let formData = new FormData(form);

                    // --- CALCULATE GENDER TOTALS ---
                    // --- CALCULATE GENDER TOTALS ---
                    let maleCount = 0;
                    let femaleCount = 0;

                    // Kunin ang lahat ng genders mula sa lahat ng dropdowns (Adult 1, Adult 2, etc.)
                    ['adult_genders[]', 'child_genders[]', 'senior_genders[]'].forEach(name => {
                        document.querySelectorAll(`select[name="${name}"]`).forEach(sel => {
                            if (sel.value === "Male") maleCount++;
                            else if (sel.value === "Female") femaleCount++;
                        });
                    });

                    formData.append('total_male', maleCount);
                    formData.append('total_female', femaleCount);

                    // Append boat rentals logic (Keep your existing code)
                    document.querySelectorAll('.boat-check').forEach((cb) => {
                        if (cb.checked) {
                            const parts = [
                                cb.value,
                                cb.closest('tr').querySelector('.include-island').checked ? 1 : 0,
                                cb.closest('tr').querySelector('.include-island').checked ? (parseFloat(cb.dataset.boat) + parseFloat(cb.dataset.island)) : parseFloat(cb.dataset.boat)
                            ];
                            formData.append('boat_rentals[]', parts.join(':'));
                        }
                    });

                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch("confirm_booking.php", { method: "POST", body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === "success") {
                                Swal.fire("Success!", data.message, "success").then(() => {
                                    window.location.href = "tracking";
                                });
                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        });
                }
            });
        });

        document.getElementById("payment_type").addEventListener("change", function () {
            let selectedOption = this.options[this.selectedIndex];
            document.getElementById("payment_number").value = selectedOption.getAttribute("data-number") || "";
        });

        // Modal Accept Button Logic
        document.querySelector("#termsModal .btn-primary").addEventListener("click", function () {
            document.getElementById("termsCheckbox").checked = true;
            document.getElementById("confirmBookingBtn").disabled = false;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>