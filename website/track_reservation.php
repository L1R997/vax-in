<?php
include './../config.php';
header('Content-Type: application/json');

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['tracking_number'])) {
    $tracking = mysqli_real_escape_string($conn, $data['tracking_number']);

    // 1. Fetch Reservation + Room + Payment Info
    $sql = "SELECT r.*, rm.room_name, rm.image, rtm.room_type_name,
                   p.amount as amount_paid, p.payment_option, p.reference_number, r.status as payment_status,
                   pt.payment_type_name
            FROM reservation_tbl r
            JOIN rooms_tbl rm ON r.room_id = rm.room_id
            JOIN room_type_tbl rtm ON rtm.room_type_id = rm.room_type_id
            LEFT JOIN reservation_payments_tbl p ON r.tracking_number = p.tracking_number
            LEFT JOIN payment_type_tbl pt ON p.payment_type = pt.payment_type_id
            WHERE r.tracking_number = '$tracking' LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $reservation_id = $row['reservation_id'];

        // Status Text Logic
        $status_map = [
            1 => '⏳ Pending Approval',
            2 => '✅ Confirmed',
            3 => '🏨 Checked In',
            4 => '🏁 Completed',
            7 => '⚠️ Request Cancellation',
            8 => '📅 Request Reschedule',
            9 => '❌ Cancelled'
        ];
        $status_text = $status_map[$row['status']] ?? '❓ Unknown Status';

        // Fetch Services
        $services = [];
        $srv_sql = "SELECT s.service_name, s.service_price FROM reservation_services_tbl rs JOIN services_tbl s ON rs.service_id = s.service_id WHERE rs.reservation_id = '$reservation_id'";
        $srv_res = mysqli_query($conn, $srv_sql);
        while ($s = mysqli_fetch_assoc($srv_res)) {
            $services[] = ['name' => $s['service_name'], 'price' => $s['service_price']];
        }

        // Fetch Rentals
        $rentals = [];
        $rnt_sql = "SELECT r.rental_name, r.rental_price FROM reservation_rentals_tbl rr JOIN rentals_tbl r ON rr.rental_id = r.rental_id WHERE rr.reservation_id = '$reservation_id'";
        $rnt_res = mysqli_query($conn, $rnt_sql);
        while ($r = mysqli_fetch_assoc($rnt_res)) {
            $rentals[] = ['name' => $r['rental_name'], 'price' => $r['rental_price']];
        }

        // Fetch Boat Rentals
        $boats = [];
        $boat_sql = "SELECT b.amount, br.destination, b.include_island, br.island_hopping_amount 
                     FROM reservation_boat_rentals_tbl b 
                     JOIN boat_rental_fee_tbl br ON b.rental_id = br.rental_id 
                     WHERE b.tracking_number = '$tracking'";
        $boat_res = mysqli_query($conn, $boat_sql);
        while ($b = mysqli_fetch_assoc($boat_res)) {
            $boats[] = [
                'name' => $b['destination'] . ($b['include_island'] ? ' (+Island Hopping)' : ''),
                'price' => $b['amount']
            ];
        }

        // Payment Calculations
        $total_price = floatval($row['total_price']);
        $amount_paid = floatval($row['amount_paid'] ?? 0);
        $balance = $total_price - $amount_paid;

        // BUSINESS LOGIC FIX: Force balance to 0 if booking is Completed (Status 4)
        if ($row['status'] == 4) {
            $balance = 0;
            // Para mag-match yung binayad sa total sa dashboard ng customer
            $amount_paid = $total_price;
        }

        $payment_option = $row['payment_option'] == 'downpayment' ? '50% Downpayment' : 'Full Payment';

        echo json_encode([
            'success' => true,
            'reservation' => [
                'tracking_number' => $row['tracking_number'],
                'guest_name' => $row['guest_name'],
                'room_name' => $row['room_name'],
                'room_type' => $row['room_type_name'],
                'room_image' => $row['image'],
                'check_in' => date("M d, Y", strtotime($row['check_in'])),
                'check_out' => date("M d, Y", strtotime($row['check_out'])),
                'guests' => $row['guests'],
                'total_price' => number_format($total_price, 2),
                'amount_paid' => number_format($amount_paid, 2),
                'balance' => number_format($balance, 2),
                'payment_option' => $payment_option,
                'payment_method' => $row['payment_type_name'] ?? 'N/A',
                'status_text' => $status_text,
                'status' => $row['status'],
                'services' => $services,
                'rentals' => $rentals,
                'boats' => $boats
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tracking number not found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>