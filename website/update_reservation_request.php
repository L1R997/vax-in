<?php
include './../config.php';

header('Content-Type: application/json');

// 1. Get raw POST data (JSON)
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// 2. Validate Input
if (isset($data['tracking_number']) && isset($data['status'])) {

    $tracking = mysqli_real_escape_string($conn, $data['tracking_number']);
    $status = intval($data['status']); // 7 = Request Cancel, 8 = Request Resched

    // ----------------------------------------
    // LOGIC FOR RESCHEDULE (Status 8)
    // ----------------------------------------
    if ($status == 8) {
        // Check if new dates are provided
        if (isset($data['dates']['newCheckIn']) && isset($data['dates']['newCheckOut'])) {
            $new_check_in = mysqli_real_escape_string($conn, $data['dates']['newCheckIn']);
            $new_check_out = mysqli_real_escape_string($conn, $data['dates']['newCheckOut']);

            // Fetch current reservation dates
            $res = mysqli_query($conn, "SELECT check_in, check_out FROM reservation_tbl WHERE tracking_number='$tracking'");
            $row = mysqli_fetch_assoc($res);

            if ($row) {
                $old_check_in = $row['check_in'];
                $old_check_out = $row['check_out'];

                // Save request to resched_tbl
                $insert = "INSERT INTO resched_tbl 
                    (tracking_number, old_check_in, old_check_out, new_check_in, new_check_out, resched_date, status)
                    VALUES ('$tracking', '$old_check_in', '$old_check_out', '$new_check_in', '$new_check_out', NOW(), 0)";

                if (!mysqli_query($conn, $insert)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to save reschedule details: ' . mysqli_error($conn)]);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Reservation not found for rescheduling.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing new dates for reschedule.']);
            exit;
        }
    }

    // ----------------------------------------
    // COMMON STATUS UPDATE (For both Cancel & Resched)
    // ----------------------------------------
    // Status 7 = User Requested Cancellation
    // Status 8 = User Requested Reschedule (details saved in resched_tbl above)

    $update = "UPDATE reservation_tbl SET status = '$status' WHERE tracking_number = '$tracking'";

    if (mysqli_query($conn, $update)) {
        echo json_encode(['success' => true, 'message' => 'Request submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update error: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
}
