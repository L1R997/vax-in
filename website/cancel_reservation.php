<?php
include './../config.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['tracking_number'])) {
    $tracking = mysqli_real_escape_string($conn, $data['tracking_number']);
    $update = "UPDATE reservation_tbl SET status = 7 WHERE tracking_number = '$tracking'";
    if (mysqli_query($conn, $update)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
