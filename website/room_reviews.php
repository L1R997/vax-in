<?php
include './../config.php';
include './template/header.php'; // Siguraduhin tama path nito

if (!isset($_GET['room_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$room_id = intval($_GET['room_id']);

// 1. Fetch Room Details & Aggregate Stats
$room_sql = "SELECT r.room_name, r.image, t.room_type_name
             FROM rooms_tbl r
             JOIN room_type_tbl t ON r.room_type_id = t.room_type_id
             WHERE r.room_id = '$room_id'";
$room_res = mysqli_query($conn, $room_sql);
$room = mysqli_fetch_assoc($room_res);

// 2. Fetch Average & Count
$stat_sql = "SELECT AVG(rating) as avg, COUNT(*) as total FROM reviews_tbl WHERE room_id = '$room_id'";
$stat_res = mysqli_fetch_assoc(mysqli_query($conn, $stat_sql));
$avg_rating = round($stat_res['avg'], 1);
$total_reviews = $stat_res['total'];

// 3. Fetch Star Breakdown (Ilan ang 5 stars, 4 stars, etc.)
$counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$breakdown_sql = "SELECT rating, COUNT(*) as cnt FROM reviews_tbl WHERE room_id = '$room_id' GROUP BY rating";
$breakdown_res = mysqli_query($conn, $breakdown_sql);
while ($row = mysqli_fetch_assoc($breakdown_res)) {
    $counts[$row['rating']] = $row['cnt'];
}
?>

<style>
    body {
        background-color: #f8f9fa;
    }

    /* Header Section with Room Image Background */
    .review-header {
        position: relative;
        height: 250px;
        background: url('./../user/admin/<?= $room['image'] ?>') center/cover no-repeat;
        /* Adjust path */
        border-bottom-left-radius: 30px;
        border-bottom-right-radius: 30px;
        margin-bottom: 80px;
        /* Space for the floating card */
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.8));
        border-bottom-left-radius: 30px;
        border-bottom-right-radius: 30px;
    }

    /* Floating Summary Card */
    .summary-card {
        margin-top: -100px;
        /* Pull up */
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 25px;
    }

    /* Progress Bars for Stars */
    .progress {
        height: 8px;
        border-radius: 10px;
        background-color: #f1f1f1;
    }

    .progress-bar {
        background-color: #ffc107;
        border-radius: 10px;
    }

    /* Review Item */
    .review-card {
        background: white;
        border: none;
        border-radius: 15px;
        margin-bottom: 15px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s;
    }

    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .avatar-circle {
        width: 45px;
        height: 45px;
        background: #e9ecef;
        border-radius: 50%;
        color: #555;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .back-btn {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 50px;
        padding: 8px 20px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .back-btn:hover {
        background: white;
        color: #333;
    }
</style>

<a href="book_room" class="back-btn"><i class="fas fa-arrow-left me-2"></i> Back to Rooms</a>

<div class="review-header">
    <div class="overlay d-flex flex-column justify-content-end p-4 text-white">
        <h5 class="text-uppercase opacity-75 mb-1"><?= htmlspecialchars($room['room_type_name']) ?></h5>
        <h1 class="fw-bold mb-5"><?= htmlspecialchars($room['room_name']) ?></h1>
    </div>
</div>

<div class="container" style="max-width: 800px;">

    <div class="card summary-card mb-4 animate__animated animate__fadeInUp">
        <div class="row align-items-center">
            <div class="col-4 text-center border-end">
                <h1 class="display-3 fw-bold text-dark mb-0"><?= $avg_rating ?></h1>
                <div class="text-warning mb-1">
                    <?php for ($i = 0; $i < 5; $i++) echo ($i < round($avg_rating)) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star text-muted"></i>'; ?>
                </div>
                <small class="text-muted"><?= $total_reviews ?> Verified Reviews</small>
            </div>

            <div class="col-8 ps-4">
                <?php
                for ($s = 5; $s >= 1; $s--) {
                    $percent = ($total_reviews > 0) ? ($counts[$s] / $total_reviews) * 100 : 0;
                ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="small fw-bold me-2" style="width: 10px;"><?= $s ?></span>
                        <i class="fas fa-star text-warning small me-2"></i>
                        <div class="progress flex-grow-1">
                            <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%"></div>
                        </div>
                        <span class="small text-muted ms-2" style="width: 30px; text-align: right;"><?= $counts[$s] ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <h5 class="mb-3 fw-bold text-secondary">Guest Comments</h5>

    <div class="review-feed">
        <?php
        // Fetch Reviews
        $reviews_sql = "SELECT rv.*, r.guest_name, r.created_at as stay_date
                        FROM reviews_tbl rv
                        JOIN reservation_tbl r ON rv.reservation_id = r.reservation_id
                        WHERE rv.room_id = '$room_id'
                        ORDER BY rv.created_at DESC";
        $reviews_res = mysqli_query($conn, $reviews_sql);

        if (mysqli_num_rows($reviews_res) > 0) {
            while ($rv = mysqli_fetch_assoc($reviews_res)) {
                $initial = strtoupper(substr($rv['guest_name'], 0, 1));
                $stars = intval($rv['rating']);
                $date = date("F d, Y", strtotime($rv['created_at']));
        ?>
                <div class="review-card animate__animated animate__fadeIn">
                    <div class="d-flex">
                        <div class="avatar-circle me-3"><?= $initial ?></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($rv['guest_name']) ?></h6>
                                <small class="text-muted"><?= $date ?></small>
                            </div>
                            <div class="mb-2">
                                <?php for ($i = 0; $i < 5; $i++) echo ($i < $stars) ? '<i class="fas fa-star text-warning small"></i>' : '<i class="far fa-star text-muted small"></i>'; ?>
                                <span class="ms-2 badge bg-light text-dark"><?= ($stars >= 4) ? 'Satisfied Guest' : 'Verified Stay' ?></span>
                            </div>
                            <p class="text-secondary mb-0" style="line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($rv['feedback'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<div class="text-center py-5 text-muted">
                    <i class="far fa-comment-alt display-1 mb-3 opacity-25"></i>
                    <p>No reviews yet for this room. Be the first to book!</p>
                  </div>';
        }
        ?>
    </div>

    <div style="height: 50px;"></div>
</div>

<?php include './template/script.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />