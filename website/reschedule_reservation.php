<?php
include './../config.php';

// Handle reschedule request via fetch (JSON)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $tracking_number = $data['tracking_number'] ?? '';
    $new_check_in = $data['new_check_in'] ?? '';
    $new_check_out = $data['new_check_out'] ?? '';

    $res = mysqli_query($conn, "SELECT check_in, check_out FROM reservation_tbl WHERE tracking_number='$tracking_number'");
    $row = mysqli_fetch_assoc($res);
    $old_check_in = $row['check_in'];
    $old_check_out = $row['check_out'];

    $insert = mysqli_query($conn, "INSERT INTO resched_tbl 
        (tracking_number, old_check_in, old_check_out, new_check_in, new_check_out, resched_date, status)
        VALUES ('$tracking_number', '$old_check_in', '$old_check_out', '$new_check_in', '$new_check_out', NOW(), 0)");

    $update = mysqli_query($conn, "UPDATE reservation_tbl SET status = 8 WHERE tracking_number='$tracking_number'");

    // Return JSON response (para compatible sa fetch)
    header('Content-Type: application/json');
    if ($insert && $update) {
        echo json_encode(['success' => true, 'message' => 'Reschedule request sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
    exit; // Stop PHP here (so HTML below doesn’t load on POST)
}
?>

<?php
// 🔹 This part only runs when page is loaded (GET request)
include './template/header.php';
?>
