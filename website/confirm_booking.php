<?php
// Prevent PHP errors from breaking JSON response
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

include './../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './../mailer/src/Exception.php';
require './../mailer/src/PHPMailer.php';
require './../mailer/src/SMTP.php';

$response = ["status" => "error", "message" => "Unknown error"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect POST data
    $room_id = $_POST['room_id'] ?? null;
    $guest_name = $_POST['guest_name'] ?? '';
    $guest_email = $_POST['guest_email'] ?? '';
    $guest_phone = $_POST['guest_phone'] ?? '';
    $check_in = $_POST['checkin'] ?? '';
    $check_out = $_POST['checkout'] ?? '';
    $guests = (int) ($_POST['guests'] ?? 0);
    $total_nights = (int) ($_POST['totalNights'] ?? 0);
    $total_price = isset($_POST['total_payments']) ? floatval(str_replace([',', '₱'], '', $_POST['total_payments'])) : 0;

    // NEW: Aggregated Genders from JavaScript
    $total_male = (int) ($_POST['total_male'] ?? 0);
    $total_female = (int) ($_POST['total_female'] ?? 0);
    $tracking_number = 'RES' . time() . rand(100, 999);

    // SQL Protection
    $room_id_safe = mysqli_real_escape_string($conn, $room_id);

    // Check room info for email
    $room_sql = "SELECT room_type_name FROM rooms_tbl 
                 INNER JOIN room_type_tbl ON room_type_tbl.room_type_id = rooms_tbl.room_type_id 
                 WHERE room_id = '$room_id_safe'";
    $room_result = mysqli_query($conn, $room_sql);
    $room_data = mysqli_fetch_assoc($room_result);
    $room_name = $room_data['room_type_name'] ?? 'Unknown';

    if ($guests > 0) {
        // 1. Insert reservation (Including Gender Totals)
        $insert_sql = "INSERT INTO reservation_tbl 
            (room_id, guest_name, guest_email, guest_phone, check_in, check_out, guests, total_nights, total_price, tracking_number, total_male, total_female) 
            VALUES (
                '$room_id_safe',
                '" . mysqli_real_escape_string($conn, $guest_name) . "',
                '" . mysqli_real_escape_string($conn, $guest_email) . "',
                '" . mysqli_real_escape_string($conn, $guest_phone) . "',
                '" . mysqli_real_escape_string($conn, $check_in) . "',
                '" . mysqli_real_escape_string($conn, $check_out) . "',
                '$guests', '$total_nights', '$total_price', '$tracking_number', '$total_male', '$total_female'
            )";

        if (mysqli_query($conn, $insert_sql)) {
            $reservation_id = mysqli_insert_id($conn);

            // 2. Insert Individual Guests with Genders
            $adult_ages = $_POST['adult_ages'] ?? [];
            $adult_genders = $_POST['adult_genders'] ?? [];
            $child_ages = $_POST['child_ages'] ?? [];
            $child_genders = $_POST['child_genders'] ?? [];
            $senior_ages = $_POST['senior_ages'] ?? [];
            $senior_genders = $_POST['senior_genders'] ?? [];
            $primary_gender = $_POST['inputGender'] ?? 'Male';

            $guest_stmt = $conn->prepare("INSERT INTO reservation_guests_tbl (reservation_id, age, category, gender, pwd) VALUES (?, ?, ?, ?, ?)");

            // Adults
            foreach ($adult_ages as $i => $age) {
                $gen = ($i === 0) ? $primary_gender : ($adult_genders[$i] ?? 'Male');
                $cat = 'Adult';
                $pwd = 'No';
                $age_val = (int) $age;
                $guest_stmt->bind_param("iisss", $reservation_id, $age_val, $cat, $gen, $pwd);
                $guest_stmt->execute();
            }
            // Children
            foreach ($child_ages as $i => $age) {
                $gen = $child_genders[$i] ?? 'Male';
                $cat = 'Child';
                $pwd = 'No';
                $age_val = (int) $age;
                $guest_stmt->bind_param("iisss", $reservation_id, $age_val, $cat, $gen, $pwd);
                $guest_stmt->execute();
            }
            // Seniors
            foreach ($senior_ages as $i => $age) {
                $gen = $senior_genders[$i] ?? 'Male';
                $cat = 'Senior';
                $pwd = 'No';
                $age_val = (int) $age;
                $guest_stmt->bind_param("iisss", $reservation_id, $age_val, $cat, $gen, $pwd);
                $guest_stmt->execute();
            }
            $guest_stmt->close();

            // 3. Events
            if (!empty($_POST['events']) && is_array($_POST['events'])) {
                foreach ($_POST['events'] as $event_id) {
                    $eid = (int) $event_id;
                    mysqli_query($conn, "INSERT INTO event_booking_tbl (tracking_number, event_id, number_of_guests, status) VALUES ('$tracking_number', '$eid', '$guests', 'Pending')");
                }
            }

            // 4. Services
            if (!empty($_POST['services']) && is_array($_POST['services'])) {
                foreach ($_POST['services'] as $sid) {
                    $sid = (int) $sid;
                    mysqli_query($conn, "INSERT INTO reservation_services_tbl (reservation_id, tracking_number, service_id) VALUES ('$reservation_id','$tracking_number','$sid')");
                }
            }

            // 5. Boat Rentals
            if (!empty($_POST['boat_rentals']) && is_array($_POST['boat_rentals'])) {
                foreach ($_POST['boat_rentals'] as $boat) {
                    $parts = explode(':', $boat);
                    $bid = (int) $parts[0];
                    $island = (int) ($parts[1] ?? 0);
                    $amt = (float) ($parts[2] ?? 0);
                    mysqli_query($conn, "INSERT INTO reservation_boat_rentals_tbl (reservation_id, tracking_number, rental_id, include_island, amount) VALUES ('$reservation_id', '$tracking_number', '$bid', '$island', '$amt')");
                }
            }

            // 6. Equipment Rentals
            if (!empty($_POST['rentals']) && is_array($_POST['rentals'])) {
                foreach ($_POST['rentals'] as $rid) {
                    $rid = (int) $rid;
                    mysqli_query($conn, "INSERT INTO reservation_rentals_tbl (reservation_id, tracking_number, rental_id) VALUES ('$reservation_id','$tracking_number','$rid')");
                }
            }

            // 7. Payment Upload
            if (!empty($_POST['payment_type']) && !empty($_POST['reference_number'])) {
                $pay_type = (int) $_POST['payment_type'];
                $ref_num = mysqli_real_escape_string($conn, $_POST['reference_number']);
                $pay_opt = mysqli_real_escape_string($conn, $_POST['payment_option'] ?? 'full');
                $amt_paid = (float) ($_POST['final_payable'] ?? $total_price);
                $screenshot = null;

                if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
                    $screenshot = time() . "_" . basename($_FILES['payment_screenshot']['name']);
                    $target_dir = __DIR__ . "/uploads/";
                    if (!file_exists($target_dir))
                        mkdir($target_dir, 0777, true);
                    move_uploaded_file($_FILES["payment_screenshot"]["tmp_name"], $target_dir . $screenshot);
                }

                mysqli_query($conn, "INSERT INTO reservation_payments_tbl (reservation_id, tracking_number, payment_type, reference_number, proof_image, amount, payment_option) VALUES ('$reservation_id','$tracking_number','$pay_type','$ref_num','$screenshot','$amt_paid', '$pay_opt')");
            }

            // --- EMAIL PREPARATION ---
            $services_list = "<ul>";
            $s_res = mysqli_query($conn, "SELECT s.service_name FROM reservation_services_tbl rs JOIN services_tbl s ON s.service_id = rs.service_id WHERE rs.reservation_id = '$reservation_id'");
            while ($r = mysqli_fetch_assoc($s_res))
                $services_list .= "<li>✔️ " . htmlspecialchars($r['service_name']) . "</li>";
            $services_list .= "</ul>";

            $payment_status_text = ($pay_opt == 'downpayment') ? '50% Downpayment' : 'Full Payment';
            $balance_amount = $total_price - $amt_paid;
            $balance_text = ($balance_amount > 0) ? "<tr><td style='padding:10px;'><span>⚠️ <strong>Balance Due</strong></span></td><td style='padding:10px; color:#dc3545;'>₱" . number_format($balance_amount, 2) . "</td></tr>" : "";

            // Send confirmation email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'beachfrontresort149@gmail.com';
                $mail->Password = 'pseidlyetewnfcof';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('beachfrontresort149@gmail.com', 'Beach Front Resort');
                $mail->addAddress($guest_email, $guest_name);
                $mail->isHTML(true);
                $mail->Subject = 'Reservation Confirmation';
                $mail->Body = "
                    <div style='font-family:Arial,sans-serif; color:#333; max-width:600px; margin:auto; border:1px solid #e0e0e0; border-radius:10px; overflow:hidden;'>
                        <div style='background:linear-gradient(135deg, #28a745, #20c997); padding:20px; text-align:center; color:#fff;'>
                            <h2 style='margin:0;'>✅ Reservation Received</h2>
                            <p>Tracking ID: $tracking_number</p>
                        </div>
                        <div style='padding:20px;'>
                            <p>Hi <strong>$guest_name</strong>,</p>
                            <table style='width:100%; border-collapse:collapse;'>
                                <tr style='background:#f9f9f9;'><td style='padding:10px;'>🏨 <strong>Room</strong></td><td>$room_name</td></tr>
                                <tr><td style='padding:10px;'>👥 <strong>Guests</strong></td><td>$guests ($total_male Male, $total_female Female)</td></tr>
                                <tr style='background:#f9f9f9;'><td style='padding:10px;'>📅 <strong>Check-in</strong></td><td>$check_in</td></tr>
                                <tr><td style='padding:10px;'>📅 <strong>Check-out</strong></td><td>$check_out</td></tr>
                                <tr style='background:#f9f9f9;'><td style='padding:10px;'>💰 <strong>Paid</strong></td><td>₱" . number_format($amt_paid, 2) . " ($payment_status_text)</td></tr>
                                $balance_text
                            </table>
                            <h3>🛎️ Services</h3> $services_list
                            <p>Please present your Tracking Number upon arrival. Thank you!</p>
                        </div>
                    </div>";

                $mail->send();
                $response = ["status" => "success", "message" => "Booking confirmed!", "tracking_number" => $tracking_number];
            } catch (Exception $e) {
                $response = ["status" => "success", "message" => "Saved, but email failed: " . $mail->ErrorInfo];
            }
        } else {
            $response = ["status" => "error", "message" => "DB Error: " . mysqli_error($conn)];
        }
    }
}
echo json_encode($response);
exit;