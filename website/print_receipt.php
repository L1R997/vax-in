<?php
include './../config.php';

if (!isset($_GET['tracking'])) {
    echo "Invalid request.";
    exit;
}

$tracking = mysqli_real_escape_string($conn, $_GET['tracking']);

// --- UTILITY FUNCTIONS ---

// FIX: Defined formatCurrency here to prevent "undefined function" error
function formatCurrency($amount)
{
    return '₱' . number_format($amount, 2);
}

function formatReceiptLine($item, $amount, $qty = 1)
{
    // Format: "2x Item Name       1,000.00"
    $qty_display = ($qty > 1) ? $qty . 'x ' : '   ';
    // Truncate item name to fit thermal paper width (approx 18 chars)
    $item_part = str_pad(substr($item, 0, 20), 20, ' ', STR_PAD_RIGHT);
    $amount_part = str_pad(number_format($amount, 2), 10, ' ', STR_PAD_LEFT);
    return $qty_display . $item_part . $amount_part . "\n";
}

// --- 1. MAIN RESERVATION DATA FETCH ---
$sql = "SELECT r.*, rm.room_name, rm.price as room_rate, rt.room_type_name, 
        p.reference_number, p.payment_option, 
        (SELECT SUM(amount) FROM reservation_payments_tbl WHERE tracking_number = r.tracking_number) as total_paid
        FROM reservation_tbl r
        JOIN rooms_tbl rm ON r.room_id = rm.room_id
        JOIN room_type_tbl rt ON rm.room_type_id = rt.room_type_id  
        LEFT JOIN reservation_payments_tbl p ON r.tracking_number = p.tracking_number
        WHERE r.tracking_number = '$tracking' LIMIT 1";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "Reservation not found.";
    exit;
}

$res = mysqli_fetch_assoc($result);
$reservation_id = $res['reservation_id'];

// --- 2. FETCH ITEMIZED CHARGES ---

// Services
$services = [];
$sql_services = "SELECT s.service_name, s.service_price FROM reservation_services_tbl rs 
                 JOIN services_tbl s ON rs.service_id = s.service_id 
                 WHERE rs.tracking_number = '$tracking'";
$res_services = mysqli_query($conn, $sql_services);
while ($srv = mysqli_fetch_assoc($res_services)) {
    $services[] = $srv;
}

// Rentals
$rentals = [];
$sql_rentals = "SELECT rnt.rental_name, rnt.rental_price FROM reservation_rentals_tbl rr 
                JOIN rentals_tbl rnt ON rr.rental_id = rnt.rental_id 
                WHERE rr.tracking_number = '$tracking'";
$res_rentals = mysqli_query($conn, $sql_rentals);
while ($rnt = mysqli_fetch_assoc($res_rentals)) {
    $rentals[] = $rnt;
}

// Equipment Loans
$loan_charges = [];
$sql_loans = "SELECT req.quantity_loaned, eq.equipment_name, (req.quantity_loaned * req.unit_price) AS loan_total
              FROM reservation_equipment_tbl req
              JOIN equipment_tbl eq ON req.equipment_id = eq.equipment_id
              WHERE req.reservation_id = '$reservation_id'";
$res_loans = mysqli_query($conn, $sql_loans);
while ($loan = mysqli_fetch_assoc($res_loans)) {
    $loan_charges[] = $loan;
}


// --- 3. CALCULATIONS ---
$total_room_charges = floatval($res['total_price']);
// Note: Assuming r.total_price in your DB already includes room + initial services.
// If r.total_price ONLY includes room, you must add services/rentals here.
// Based on your previous logic, total_price usually stores the grand total at booking.

// Calculate totals for display purposes
$total_loan_charge = array_sum(array_column($loan_charges, 'loan_total'));

// Final computation
$GRAND_TOTAL_DUE = $total_room_charges + $total_loan_charge;
$total_paid = floatval($res['total_paid'] ?? 0);
$balance_due = $GRAND_TOTAL_DUE - $total_paid;

// Dates
$checkIn = date("M d, Y", strtotime($res['check_in']));
$checkOut = date("M d, Y", strtotime($res['check_out']));
$current_time = date("M d, Y h:i A");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt - <?= $tracking ?></title>
    <style>
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }

            body {
                margin: 0;
                padding: 5px;
            }
        }

        body {
            font-size: 12px;
            font-family: 'Courier New', monospace;
            width: 78mm;
            /* Slightly less than 80mm to prevent overflow */
            margin: 0 auto;
            padding: 10px;
            background-color: white;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .bold {
            font-weight: bold;
        }

        .logo {
            width: 60px;
            height: auto;
            margin-bottom: 5px;
        }

        /* Layout for Receipt Items */
        .item-row {
            display: flex;
            justify-content: space-between;
        }

        .item-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60%;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="center">
        <!-- Ensure image path is correct relative to this file -->
        <img src="../../uploads/solo_logo.jpg" class="logo" onerror="this.style.display='none'"><br>
        <span class="bold" style="font-size: 14px;">BEACHFRONT RESORT</span><br>
        <span>Polo, Santa Cruz Marinduque</span><br>
        <span>Contact: 09053007306</span>
    </div>

    <div class="line"></div>

    <div style="text-align: left;">
        <span class="bold">OFFICIAL RECEIPT</span><br>
        Date: <?= $current_time ?><br>
        Ref#: <?= $res['tracking_number'] ?>
    </div>

    <div class="line"></div>

    <div>
        <span class="bold">GUEST:</span> <?= strtoupper($res['guest_name']) ?><br>
        <span class="bold">ROOM:</span> <?= $res['room_name'] ?> (<?= $res['room_type_name'] ?>)<br>
        <span class="bold">STAY:</span> <?= $res['total_nights'] ?> Nights<br>
        <span style="font-size: 11px;">(<?= $checkIn ?> to <?= $checkOut ?>)</span>
    </div>

    <div class="line"></div>

    <div class="item-row bold">
        <span>DESCRIPTION</span>
        <span>AMOUNT</span>
    </div>

    <div class="line"></div>

    <!-- ROOM CHARGE -->
    <div class="item-row">
        <span>Room Charge</span>
        <span><?= number_format($res['total_price'], 2) ?></span>
    </div>

    <!-- SERVICES & RENTALS (If not included in total_price, adjust logic) -->
    <?php if (!empty($services) || !empty($rentals)): ?>
        <div style="margin-top: 5px; font-style: italic;">-- Inclusions --</div>
        <?php foreach ($services as $s): ?>
            <div class="item-row" style="padding-left: 10px;">
                <span class="item-name"><?= $s['service_name'] ?></span>
                <span>(Inc)</span>
            </div>
        <?php endforeach; ?>
        <?php foreach ($rentals as $r): ?>
            <div class="item-row" style="padding-left: 10px;">
                <span class="item-name"><?= $r['rental_name'] ?></span>
                <span>(Inc)</span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- EQUIPMENT LOAN CHARGES -->
    <?php if (!empty($loan_charges)): ?>
        <div style="margin-top: 5px; font-style: italic;">-- Equipment Loans --</div>
        <?php foreach ($loan_charges as $loan): ?>
            <div class="item-row" style="padding-left: 5px;">
                <span class="item-name">
                    <?= $loan['quantity_loaned'] ?>x <?= $loan['equipment_name'] ?>
                </span>
                <span><?= number_format($loan['loan_total'], 2) ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="line"></div>

    <!-- TOTALS -->
    <div class="right" style="font-size: 13px; line-height: 1.6;">
        <div>TOTAL AMOUNT: <span class="bold"><?= formatCurrency($GRAND_TOTAL_DUE) ?></span></div>
        <div>DEPOSIT/PAID: <?= formatCurrency($total_paid) ?></div>

        <div style="margin-top: 5px; border-top: 1px solid #000; padding-top: 5px;">
            <?php if ($balance_due > 0): ?>
                <!-- If there is a balance, we show it as 'Paid Now' on the receipt -->
                <div>BALANCE PAID: <span class="bold"><?= formatCurrency($balance_due) ?></span></div>
                <div style="margin-top: 5px; border-top: 1px double #000; padding-top: 2px;">
                    STATUS: <span class="bold">FULLY PAID</span>
                </div>
            <?php elseif ($balance_due < 0): ?>
                CHANGE/REFUND: <span class="bold"><?= formatCurrency(abs($balance_due)) ?></span>
            <?php else: ?>
                <span class="bold">FULLY PAID</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="line"></div>

    <div class="center" style="margin-top: 10px;">
        <span class="bold">THANK YOU!</span><br>
        <span>Please come again.</span>
    </div>

</body>

</html>