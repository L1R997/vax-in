<?php
include './../config.php'; // Adjust path kung kinakailangan
include './template/header.php';
?>

<body>
    <!-- Spinner Start (Loading Animation) -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center"
        style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- Spinner End -->

    <!-- Custom CSS for Landing Page -->
    <style>
        /* Font & Body */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Hero/Carousel Section */
        .carousel-item {
            height: 90vh;
            /* Full screen height feel */
            min-height: 500px;
            background: #000;
        }

        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
            opacity: 0.7;
            /* Darken image slightly for text readability */
        }

        .carousel-caption {
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.6));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .carousel-title {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* Feature Cards */
        .feature-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            border: none;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px auto;
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }

        /* Buttons */
        .btn-resort {
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .btn-resort-primary {
            background-color: #f6c23e;
            border: none;
            color: #333;
        }

        .btn-resort-primary:hover {
            background-color: #e0b036;
            transform: scale(1.05);
            color: #000;
        }

        /* Section Titles */
        .section-title {
            position: relative;
            display: inline-block;
            color: #4e73df;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .section-title::after {
            content: "";
            width: 50px;
            height: 3px;
            background: #f6c23e;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        @media (max-width: 768px) {
            .carousel-title {
                font-size: 2rem;
            }

            .carousel-item {
                height: 70vh;
            }
        }
    </style>

    <!-- ==================== WRAPPER START ==================== -->
    <div id="wrapper">

        <!-- Note: Sidebar is usually not included in a landing page, but if your template structure requires it, keep it. 
             If this is a public landing page, you might want to hide the sidebar. -->
        <?php // include './../template/sidebar.php'; 
        ?>

        <div id="content-wrapper" class="d-flex flex-column p-0">
            <div id="content" class="p-0">

                <!-- Navbar Include -->
                <?php include './template/navbar.php'; ?>

                <!-- ==================== HERO CAROUSEL ==================== -->
                <div id="resortCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="0" class="active"></button>
                        <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#resortCarousel" data-bs-slide-to="2"></button>
                    </div>

                    <div class="carousel-inner">
                        <!-- Slide 1 -->
                        <div class="carousel-item active">
                            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80" class="d-block w-100" alt="Beach View">
                            <div class="carousel-caption">
                                <div class="container">
                                    <h5 class="text-warning text-uppercase fw-bold mb-3 animate__animated animate__fadeInDown">Welcome to Paradise</h5>
                                    <h1 class="carousel-title text-white mb-4 animate__animated animate__zoomIn">Beach-Front Resort</h1>
                                    <p class="lead text-white mb-5 animate__animated animate__fadeInUp" style="max-width: 700px; margin: 0 auto;">
                                        Experience the ultimate relaxation with our white sandy beaches and crystal clear waters. Your perfect getaway starts here.
                                    </p>
                                    <a href="room.php" class="btn btn-resort btn-resort-primary animate__animated animate__fadeInUp">Check Rooms</a>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1600&q=80" class="d-block w-100" alt="Luxury Rooms">
                            <div class="carousel-caption">
                                <div class="container">
                                    <h5 class="text-warning text-uppercase fw-bold mb-3 animate__animated animate__fadeInDown">Luxury & Comfort</h5>
                                    <h1 class="carousel-title text-white mb-4 animate__animated animate__zoomIn">Stay in Style</h1>
                                    <p class="lead text-white mb-5 animate__animated animate__fadeInUp" style="max-width: 700px; margin: 0 auto;">
                                        From cozy cottages to premium suites, we offer accommodations that feel like a home away from home.
                                    </p>
                                    <a href="room.php" class="btn btn-resort btn-resort-primary animate__animated animate__fadeInUp">Book Now</a>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=1600&q=80" class="d-block w-100" alt="Amenities">
                            <div class="carousel-caption">
                                <div class="container">
                                    <h5 class="text-warning text-uppercase fw-bold mb-3 animate__animated animate__fadeInDown">Fun & Relaxation</h5>
                                    <h1 class="carousel-title text-white mb-4 animate__animated animate__zoomIn">Unforgettable Moments</h1>
                                    <p class="lead text-white mb-5 animate__animated animate__fadeInUp" style="max-width: 700px; margin: 0 auto;">
                                        Enjoy our pool, dining services, and recreational activities designed for the whole family.
                                    </p>
                                    <a href="service.php" class="btn btn-resort btn-resort-primary animate__animated animate__fadeInUp">Our Services</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#resortCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#resortCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <!-- ==================== CAROUSEL END ==================== -->

                <!-- ==================== FEATURES SECTION ==================== -->
                <div class="container-xxl py-5">
                    <div class="container">
                        <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                            <h6 class="section-title">Our Best</h6>
                            <h1 class="mb-3 fw-bold text-dark">Why Choose Us?</h1>
                            <p class="text-muted">We are dedicated to providing you with the best experience possible through our top-notch amenities and services.</p>
                        </div>

                        <div class="row g-4">
                            <!-- Feature 1 -->
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-umbrella-beach"></i>
                                    </div>
                                    <h4 class="mb-3 fw-bold text-dark">Private Beach</h4>
                                    <p class="text-muted mb-0">Enjoy exclusive access to our pristine shoreline, perfect for sunbathing and morning walks.</p>
                                </div>
                            </div>

                            <!-- Feature 2 -->
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-swimmer"></i>
                                    </div>
                                    <h4 class="mb-3 fw-bold text-dark">Swimming Pool</h4>
                                    <p class="text-muted mb-0">Take a dip in our well-maintained pools, suitable for both kids and adults.</p>
                                </div>
                            </div>

                            <!-- Feature 3 -->
                            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                                <div class="feature-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                    <h4 class="mb-3 fw-bold text-dark">Dining & Bar</h4>
                                    <p class="text-muted mb-0">Savor delicious local and international cuisines prepared by our expert chefs.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ==================== FEATURES END ==================== -->

                <!-- ==================== EVENTS SECTION ==================== -->
                <div class="container-xxl py-5">
                    <div class="container">
                        <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                            <h6 class="section-title">Upcoming Events</h6>
                            <h1 class="mb-3 fw-bold text-dark">Exciting Activities Awaiting You</h1>
                            <p class="text-muted">Discover and participate in our exclusive events designed to make your stay unforgettable.</p>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="text-center mb-5">
                            <button class="btn btn-sm btn-light border border-primary me-2 filter-btn active" data-filter="all">All Events</button>
                            <button class="btn btn-sm btn-light border border-primary me-2 filter-btn" data-filter="upcoming">Upcoming</button>
                            <button class="btn btn-sm btn-light border border-primary filter-btn" data-filter="past">Past Events</button>
                        </div>

                        <!-- Events Grid -->
                        <div class="row g-4" id="eventsContainer">
                            <?php
                            $events_query = mysqli_query($conn, "SELECT * FROM event_tbl ORDER BY event_date ASC");
                            $today = date('Y-m-d');
                            while ($event = mysqli_fetch_assoc($events_query)) {
                                $event_date = $event['event_date'];
                                $is_upcoming = ($event_date >= $today) ? 'upcoming' : 'past';
                                $event_datetime = strtotime($event_date . ' ' . $event['event_time']);
                                $formatted_date = date("M d, Y", strtotime($event_date));
                                $formatted_time = date("h:i A", $event_datetime);
                                $days_away = ceil((strtotime($event_date) - strtotime($today)) / (60 * 60 * 24));
                            ?>
                                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s" data-event-filter="<?php echo $is_upcoming; ?>">
                                    <div class="event-card h-100 rounded-4 overflow-hidden shadow-lg" style="background: white; transition: all 0.3s ease; cursor: pointer; border: 2px solid #f0f0f0;">
                                        <!-- Event Header with Icon -->
                                        <div style="background: linear-gradient(135deg, #4e73df, #224abe); padding: 25px; color: white; text-align: center; position: relative;">
                                            <div style="font-size: 2.5rem; margin-bottom: 10px;">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <h5 class="fw-bold mb-0" style="font-size: 1.1rem;"><?php echo substr($event['event_name'], 0, 30); ?></h5>
                                            <?php if ($is_upcoming == 'upcoming' && $days_away <= 7 && $days_away >= 0) { ?>
                                                <span class="badge bg-warning text-dark mt-2" style="position: absolute; top: 10px; right: 10px;">Coming Soon</span>
                                            <?php } ?>
                                        </div>

                                        <!-- Event Body -->
                                        <div style="padding: 25px;">
                                            <!-- Description -->
                                            <p class="text-muted small mb-4" style="line-height: 1.6; min-height: 50px;">
                                                <?php echo substr($event['description'], 0, 100) . (strlen($event['description']) > 100 ? '...' : ''); ?>
                                            </p>

                                            <!-- Event Details -->
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center mb-3" style="color: #555;">
                                                    <i class="fas fa-calendar" style="color: #4e73df; margin-right: 10px; width: 20px; text-align: center;"></i>
                                                    <span><?php echo $formatted_date; ?></span>
                                                </div>
                                                <div class="d-flex align-items-center mb-3" style="color: #555;">
                                                    <i class="fas fa-clock" style="color: #4e73df; margin-right: 10px; width: 20px; text-align: center;"></i>
                                                    <span><?php echo $formatted_time; ?></span>
                                                </div>
                                                <?php if ($days_away > 0 && $is_upcoming == 'upcoming') { ?>
                                                    <div class="d-flex align-items-center" style="color: #28a745; font-weight: 600;">
                                                        <i class="fas fa-hourglass-end" style="margin-right: 10px; width: 20px; text-align: center;"></i>
                                                        <span><?php echo $days_away . ' day' . ($days_away != 1 ? 's' : '') . ' away'; ?></span>
                                                    </div>
                                                <?php } else if ($is_upcoming == 'past') { ?>
                                                    <div class="d-flex align-items-center" style="color: #999;">
                                                        <i class="fas fa-check-circle" style="margin-right: 10px; width: 20px; text-align: center;"></i>
                                                        <span>Event Ended</span>
                                                    </div>
                                                <?php } ?>
                                            </div>

                                            <!-- Learn More Button -->
                                            <a href="event.php" class="btn btn-sm btn-resort btn-resort-primary w-100" style="text-decoration: none;">
                                                <i class="fas fa-arrow-right me-2"></i>View All Events
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- No Events Message -->
                        <div class="text-center mt-5" id="noEventsMsg" style="display: none;">
                            <p class="text-muted" style="font-size: 1.1rem;">
                                <i class="fas fa-calendar-x" style="color: #ccc; font-size: 2rem;"></i><br>
                                No events in this category.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Events Filter Script -->
                <script>
                    document.querySelectorAll('.filter-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const filter = this.dataset.filter;
                            
                            // Update active button
                            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                            this.classList.add('active');
                            
                            // Filter events
                            const events = document.querySelectorAll('[data-event-filter]');
                            let visibleCount = 0;
                            
                            events.forEach(event => {
                                if (filter === 'all' || event.dataset.eventFilter === filter) {
                                    event.style.display = 'block';
                                    visibleCount++;
                                } else {
                                    event.style.display = 'none';
                                }
                            });
                            
                            // Show/hide no events message
                            document.getElementById('noEventsMsg').style.display = visibleCount === 0 ? 'block' : 'none';
                        });
                    });

                    // Add hover effect to event cards
                    document.querySelectorAll('.event-card').forEach(card => {
                        card.addEventListener('mouseenter', function() {
                            this.style.transform = 'translateY(-10px)';
                            this.style.boxShadow = '0 20px 40px rgba(78, 115, 223, 0.2)';
                            this.style.borderColor = '#4e73df';
                        });
                        card.addEventListener('mouseleave', function() {
                            this.style.transform = 'translateY(0)';
                            this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
                            this.style.borderColor = '#f0f0f0';
                        });
                    });
                </script>

                <style>
                    .filter-btn {
                        transition: all 0.3s ease;
                        font-weight: 600;
                        color: #4e73df;
                    }

                    .filter-btn:hover {
                        background-color: #f0f2f5 !important;
                        border-color: #4e73df !important;
                        color: #4e73df;
                    }

                    .filter-btn.active {
                        background-color: #4e73df !important;
                        color: white !important;
                        border-color: #4e73df !important;
                    }

                    .event-card {
                        border-left: 5px solid #4e73df;
                    }
                </style>
                <!-- ==================== EVENTS END ==================== -->

                <!-- ==================== ABOUT TEASER ==================== -->
                <div class="container-fluid py-5 my-5" style="background: #f0f2f5;">
                    <div class="container">
                        <div class="row g-5 align-items-center">
                            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                                <div class="row g-0 about-bg rounded overflow-hidden">
                                    <div class="col-6 text-start">
                                        <img class="img-fluid w-100 rounded-start" src="../uploads/polo.jpg" style="margin-top: 25%;">
                                    </div>
                                    <div class="col-6 text-start">
                                        <img class="img-fluid w-100 rounded-end" src="https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                                <h6 class="section-title text-start">About Us</h6>
                                <h1 class="mb-4 fw-bold text-dark">Relaxation Meets Adventure</h1>
                                <p class="mb-4 text-muted">Located in the heart of nature, Beach-Front Resort offers a serene escape from the hustle and bustle of daily life. With our modern amenities and warm hospitality, we ensure every guest leaves with a smile.</p>

                                <div class="row g-4 mb-4">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center border-start border-5 border-primary px-3">
                                            <h1 class="flex-shrink-0 display-5 text-primary mb-0">6</h1>
                                            <div class="ps-4">
                                                <p class="mb-0">Years of</p>
                                                <h6 class="text-uppercase mb-0">Experience</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center border-start border-5 border-primary px-3">
                                            <h1 class="  text-primary mb-0" data-toggle="counter-up">
                                                <?php
                                                $room_count = $conn->query("SELECT COUNT(*) as total FROM `rooms_tbl`")->fetch_assoc()['total'];
                                                echo $room_count;
                                                ?>
                                            </h1>
                                            <div class="ps-4">
                                                <p class="mb-0">Rooms &</p>
                                                <h6 class="text-uppercase mb-0">Cottages</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a class="btn btn-primary py-3 px-5 rounded-pill shadow" href="about.php">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ==================== ABOUT END ==================== -->

            </div>



        </div>
    </div>
    <!-- ==================== WRAPPER END ==================== -->

    <!-- Scroll to Top Button-->
    <br><br><br>
    <br><br><br>
    <footer class="container-fluid bg-dark text-white py-3 d-none d-lg-block">
        <div class="text-center">
            © 2025 Beach-Front Resort. All Rights Reserved.
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <?php include './template/script.php'; ?>

    <!-- Optional: Initialize WOW.js for animations if available in your template/script.php, else simple CSS animations apply -->

</body>

</html>