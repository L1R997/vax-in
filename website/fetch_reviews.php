<?php
include './../config.php'; // Adjust path based on your folder structure

if (isset($_GET['room_id'])) {
    $room_id = intval($_GET['room_id']);

    // Fetch Reviews JOIN with Reservation to get guest name if needed (Assuming reviews_tbl has reservation_id)
    // Or if reviews_tbl has guest_name directly. This assumes reviews_tbl is linked to reservation_tbl
    $sql = "SELECT rv.*, r.guest_name, r.created_at as stay_date
            FROM reviews_tbl rv
            JOIN reservation_tbl r ON rv.reservation_id = r.reservation_id
            WHERE rv.room_id = '$room_id'
            ORDER BY rv.created_at DESC";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="d-flex align-items-center mb-4 bg-light p-3 rounded">';

        // Calculate Summary for Modal Header
        $avg_query = mysqli_query($conn, "SELECT AVG(rating) as avg, COUNT(*) as count FROM reviews_tbl WHERE room_id = '$room_id'");
        $summary = mysqli_fetch_assoc($avg_query);
        $avg = round($summary['avg'], 1);

        echo '<h1 class="mb-0 fw-bold text-primary me-3">' . $avg . '</h1>';
        echo '<div>';
        echo '<div class="text-warning">';
        for ($i = 0; $i < 5; $i++) {
            echo ($i < round($avg)) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
        }
        echo '</div>';
        echo '<small class="text-muted">Based on ' . $summary['count'] . ' reviews</small>';
        echo '</div>';
        echo '</div>';

        // List Reviews
        while ($row = mysqli_fetch_assoc($result)) {
            $stars = intval($row['rating']);
            $date = date("M d, Y", strtotime($row['created_at']));
            $guestInitial = strtoupper(substr($row['guest_name'], 0, 1));

            echo '<div class="review-item animate__animated animate__fadeIn">';
            echo '  <div class="d-flex">';
            echo '    <div class="reviewer-avatar me-3">' . $guestInitial . '</div>';
            echo '    <div class="flex-grow-1">';
            echo '      <div class="d-flex justify-content-between align-items-center mb-1">';
            echo '        <h6 class="mb-0 fw-bold">' . htmlspecialchars($row['guest_name']) . '</h6>';
            echo '        <small class="text-muted" style="font-size:0.75rem">' . $date . '</small>';
            echo '      </div>';
            echo '      <div class="text-warning small mb-2">';
            for ($i = 0; $i < 5; $i++) {
                echo ($i < $stars) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
            }
            echo '      </div>';
            echo '      <p class="text-secondary mb-0 bg-light p-2 rounded small">' . nl2br(htmlspecialchars($row['feedback'])) . '</p>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo '<div class="text-center py-5">';
        echo '  <i class="far fa-comment-dots display-1 text-light mb-3"></i>';
        echo '  <p class="text-muted">No reviews yet for this room.</p>';
        echo '</div>';
    }
}
