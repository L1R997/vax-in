<?php
include './../config.php';
include './template/header.php';
?>

<style>
    /* Custom CSS for User Interactive Elements */
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .card-img-wrapper {
        height: 240px;
        overflow: hidden;
        position: relative;
    }

    .card-img-wrapper img {
        transition: transform 0.5s ease;
        height: 100%;
        width: 100%;
        object-fit: cover;
    }

    .hover-lift:hover .card-img-wrapper img {
        transform: scale(1.05);
    }

    .discount-badge {
        position: absolute;
        top: 15px;
        left: 0;
        background: linear-gradient(45deg, #dc3545, #ff6b6b);
        color: white;
        padding: 5px 15px;
        font-weight: bold;
        font-size: 0.85rem;
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        z-index: 2;
    }

    .filter-btn {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .filter-btn.active,
    .filter-btn:hover {
        background-color: #0d6efd;
        color: white;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }

    .filter-btn-outline {
        border-color: #e9ecef;
        color: #6c757d;
    }

    .price-tag {
        font-family: 'Poppins', sans-serif;
    }

    /* Star Rating CSS */
    .rating-stars {
        color: #ffc107;
        font-size: 0.9rem;
    }

    .review-link {
        cursor: pointer;
        color: #6c757d;
        text-decoration: none;
        font-size: 0.85rem;
        transition: color 0.2s;
    }

    .review-link:hover {
        color: #0d6efd;
        text-decoration: underline;
    }

    /* Review Modal Styling */
    .review-item {
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    .review-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .reviewer-avatar {
        width: 40px;
        height: 40px;
        background: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-weight: bold;
    }
</style>

<body>
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <?php include './template/navbar.php'; ?>

    <div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://www.thecreedguy.com/wp-content/uploads/2021/07/FB_IMG_1627169465176.jpg') center/cover;">
        <div class="container py-5 text-center">
            <h1 class="display-3 text-white animated slideInDown fw-bold">Find Your Perfect Stay</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center animated slideInUp mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-white">Home</a></li>
                    <li class="breadcrumb-item text-white active" aria-current="page">Book Room</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container-xxl py-4">
        <div class="container text-center">
            <div class="wow fadeInUp" data-wow-delay="0.1s" style="max-width: 700px; margin: 0 auto;">
                <h6 class="section-title text-center text-primary text-uppercase">Our Accommodations</h6>
                <h1 class="mb-4">Explore Our <span class="text-primary">Premium Rooms</span></h1>
                <p class="mb-4 text-secondary">From cozy couple suites to spacious family rooms, experience comfort and luxury with Beach-Front Resort.</p>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="d-flex justify-content-center flex-wrap gap-2 wow fadeInUp" data-wow-delay="0.2s">
            <button class="btn filter-btn btn-primary active" data-filter="all"><i class="fas fa-layer-group me-2"></i>All Rooms</button>
            <?php
            $type_sql = "SELECT * FROM room_type_tbl ORDER BY room_type_name ASC";
            $type_result = mysqli_query($conn, $type_sql);
            while ($type = mysqli_fetch_assoc($type_result)) {
                $type_slug = strtolower(str_replace(' ', '-', $type['room_type_name']));
                echo '<button class="btn filter-btn filter-btn-outline" data-filter="' . $type_slug . '">' . htmlspecialchars($type['room_type_name']) . '</button>';
            }
            ?>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row g-4">
            <?php
            // 1. UPDATED QUERY: Calculates AVG Rating and Count using Subqueries
            $room_sql = "SELECT r.*, t.room_type_name, 
                                d.discount_name, d.discount_percent, d.start_date, d.end_date,
                                (SELECT AVG(rating) FROM reviews_tbl WHERE room_id = r.room_id) as avg_rating,
                                (SELECT COUNT(*) FROM reviews_tbl WHERE room_id = r.room_id) as review_count
                         FROM rooms_tbl r
                         LEFT JOIN room_type_tbl t ON r.room_type_id = t.room_type_id
                         LEFT JOIN discount_tbl d ON r.discount_id = d.discount_id
                         ORDER BY r.available DESC, avg_rating DESC"; // Ordered by best rated

            $room_result = mysqli_query($conn, $room_sql);
            $today = date('Y-m-d');

            while ($room = mysqli_fetch_assoc($room_result)) {
                $room_slug = strtolower(str_replace(' ', '-', $room['room_type_name']));

                // Promo Logic
                $active_discount = 0;
                if (!empty($room['discount_percent'])) {
                    if ($today >= $room['start_date'] && $today <= $room['end_date']) {
                        $active_discount = $room['discount_percent'];
                    }
                }
                $orig_price = $room['price'];
                $final_price = ($active_discount > 0) ? $orig_price - ($orig_price * ($active_discount / 100)) : $orig_price;

                // RATING LOGIC
                $rating = round($room['avg_rating'], 1); // Round to 1 decimal (e.g., 4.5)
                $count = $room['review_count'];
                $full_stars = floor($rating);
                $half_star = ($rating - $full_stars) >= 0.5 ? 1 : 0;
                $empty_stars = 5 - ($full_stars + $half_star);
            ?>
                <div class="col-md-6 col-lg-4 wow fadeInUp product-item" data-wow-delay="0.1s" data-category="<?= $room_slug ?>">
                    <div class="card border-0 rounded-4 shadow-sm h-100 hover-lift overflow-hidden">
                        <div class="card-img-wrapper">
                            <img src="./../user/admin/<?= htmlspecialchars($room['image']) ?>" alt="<?= htmlspecialchars($room['room_name']) ?>">
                            <?php if ($active_discount > 0): ?>
                                <div class="discount-badge"><i class="fas fa-tags me-1"></i> <?= $active_discount ?>% OFF</div>
                            <?php endif; ?>
                            <div class="position-absolute bottom-0 end-0 bg-white text-primary px-3 py-1 m-3 rounded-pill fw-bold small shadow">
                                <?= htmlspecialchars($room['room_type_name']) ?>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="card-title fw-bold text-dark mb-0"><?= htmlspecialchars($room['room_name']) ?></h5>
                                <?php if ($room['available'] <= 0): ?>
                                    <span class="badge bg-secondary">Fully Booked</span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-white"><?= $room['available'] ?> Available</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2 d-flex align-items-center">
                                <div class="rating-stars me-2">
                                    <?php
                                    if ($count > 0) {
                                        for ($i = 0; $i < $full_stars; $i++) echo '<i class="fas fa-star"></i>';
                                        if ($half_star) echo '<i class="fas fa-star-half-alt"></i>';
                                        for ($i = 0; $i < $empty_stars; $i++) echo '<i class="far fa-star"></i>';
                                    } else {
                                        for ($i = 0; $i < 5; $i++) echo '<i class="far fa-star text-muted"></i>';
                                    }
                                    ?>
                                </div>
                                <?php if ($count > 0): ?>
                                    <a href="room_reviews.php?room_id=<?= $room['room_id'] ?>" class="review-link text-decoration-none">
                                        (<?= $count ?> Reviews) <i class="fas fa-chevron-right small ms-1"></i>
                                    </a>
                                    <span class="badge bg-light text-dark border ms-2"><?= $rating ?>/5</span>
                                <?php else: ?>
                                    <small class="text-muted" style="font-size: 0.8rem;">No reviews yet</small>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted small mb-3 line-clamp-2">
                                <?= mb_strimwidth(htmlspecialchars($room['room_description']), 0, 80, "...") ?>
                            </p>

                            <div class="d-flex justify-content-between mb-3 text-secondary small">
                                <span><i class="fa fa-user me-2 text-primary"></i>Max: <?= $room['max_guest'] ?> Guests</span>
                            </div>

                            <hr class="my-2" style="opacity: 0.1;">

                            <div class="mt-auto d-flex align-items-center justify-content-between">
                                <div class="price-tag">
                                    <?php if ($active_discount > 0): ?>
                                        <small class="text-decoration-line-through text-muted" style="font-size: 0.85rem;">₱<?= number_format($orig_price, 2) ?></small>
                                        <div class="fw-bold text-danger fs-5">₱<?= number_format($final_price, 2) ?></div>
                                    <?php else: ?>
                                        <small class="text-muted" style="font-size: 0.85rem;">Starts at</small>
                                        <div class="fw-bold text-dark fs-5">₱<?= number_format($orig_price, 2) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($room['available'] > 0): ?>
                                        <button class="btn btn-primary rounded-pill px-4 py-2 book-now-btn shadow-sm" data-room-id="<?= $room['room_id'] ?>">
                                            Book Now <i class="fas fa-arrow-right ms-1"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-light text-muted rounded-pill px-4 py-2" disabled>Unavailable</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="modal fade" id="reviewsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalRoomName">Room Reviews</h5>
                        <p class="text-muted small mb-0">See what others are saying</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="reviewsContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading reviews...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="container-fluid bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <small>&copy; Beach-Front Resort. All Right Reserved.</small>
        </div>
    </footer>

    <?php include './template/script.php'; ?>

    <script>
        // Filtering Logic
        const filterButtons = document.querySelectorAll('.filter-btn');
        const products = document.querySelectorAll('.product-item');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('filter-btn-outline');
                });
                btn.classList.remove('filter-btn-outline');
                btn.classList.add('btn-primary', 'active');

                const filter = btn.getAttribute('data-filter');
                products.forEach(prod => {
                    if (filter === 'all' || prod.getAttribute('data-category') === filter) {
                        prod.classList.remove('d-none');
                        prod.classList.add('animate__animated', 'animate__fadeIn');
                    } else {
                        prod.classList.add('d-none');
                        prod.classList.remove('animate__animated', 'animate__fadeIn');
                    }
                });
            });
        });

        // Booking Redirect
        document.querySelectorAll('.book-now-btn').forEach(button => {
            button.addEventListener('click', () => {
                window.location.href = `book_room?room_id=${button.getAttribute('data-room-id')}`;
            });
        });

        // OPEN REVIEWS MODAL FUNCTION
        function openReviewsModal(roomId, roomName) {
            document.getElementById('modalRoomName').innerText = "Reviews for " + roomName;
            var modal = new bootstrap.Modal(document.getElementById('reviewsModal'));
            modal.show();

            // Fetch Reviews using AJAX
            const container = document.getElementById('reviewsContent');
            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

            fetch(`fetch_reviews.php?room_id=${roomId}`)
                .then(response => response.text())
                .then(data => {
                    container.innerHTML = data;
                })
                .catch(err => {
                    container.innerHTML = '<p class="text-center text-danger">Failed to load reviews.</p>';
                });
        }
    </script>
</body>

</html>