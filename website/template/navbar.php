<?php
// Get current page filename to set active state
$page = basename($_SERVER['PHP_SELF'], '.php');
?>

<style>
    /* =========================================
       1. FONTS & UTILITIES
       ========================================= */
    :root {
        --primary-color: #4e73df;
        --secondary-color: #224abe;
        --text-dark: #2c3e50;
        --text-muted: #858796;
    }

    body {
        font-family: 'Poppins', sans-serif;
        /* Ensure font is loaded in header */
    }

    /* =========================================
       2. TOP UTILITY BAR (Desktop Only)
       New Feature: Display Contact Info immediately
       ========================================= */
    .top-utility-bar {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        font-size: 0.85rem;
        padding: 8px 0;
        color: var(--text-dark);
    }

    .top-utility-bar a {
        color: var(--text-dark);
        text-decoration: none;
        transition: color 0.2s;
    }

    .top-utility-bar a:hover {
        color: var(--primary-color);
    }

    /* =========================================
       3. DESKTOP NAVBAR STYLES (Improved)
       ========================================= */
    .navbar-desktop {
        background: #fff;
        padding: 5px 0;
        /* More breathing room */
        transition: all 0.3s ease;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    /* Sticky state shadow (Applied via JS if needed, or default) */
    .navbar-desktop.scrolled {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        padding: 5px 0;
        /* Shrink slightly on scroll */
    }

    .navbar-brand-text h5 {
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    /* Navigation Links */
    .nav-item .nav-link {
        font-weight: 500;
        color: var(--text-dark);
        padding: 8px 18px !important;
        font-size: 0.95rem;
        position: relative;
        transition: color 0.3s;
    }

    .nav-item .nav-link:hover,
    .nav-item .nav-link.active {
        color: var(--primary-color) !important;
    }

    /* Pills Style Indicator instead of Underline (More Modern) */
    .nav-item .nav-link.active {
        background-color: rgba(78, 115, 223, 0.1);
        border-radius: 50px;
        color: var(--primary-color) !important;
        font-weight: 600;
    }

    /* Remove the old underline effect to keep it clean */
    .nav-item .nav-link::after {
        display: none;
    }

    /* CTA Button (Book Now) */
    .btn-book-desktop {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        padding: 10px 28px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-book-desktop:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        background: linear-gradient(135deg, #375ad1, #1a3a9c);
    }

    /* =========================================
       4. MOBILE BOTTOM NAV STYLES (Kept Same)
       ========================================= */
    .mobile-bottom-nav {
        background: #ffffff;
        box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.1);
        border-top-left-radius: 25px;
        border-top-right-radius: 25px;
        z-index: 1050;
        height: 70px;
        padding-bottom: env(safe-area-inset-bottom);
    }

    .mobile-nav-item {
        color: #858796;
        font-size: 0.7rem;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 20%;
        transition: all 0.3s;
    }

    .mobile-nav-item i {
        font-size: 1.3rem;
        margin-bottom: 3px;
        transition: transform 0.2s;
    }

    .mobile-nav-item.active {
        color: var(--primary-color);
        font-weight: 700;
    }

    .mobile-nav-item.active i {
        transform: translateY(-3px);
    }

    .fab-wrapper {
        position: relative;
        top: -25px;
        display: flex;
        justify-content: center;
        width: 20%;
    }

    .fab-btn {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 8px 20px rgba(78, 115, 223, 0.4);
        border: 4px solid #f8f9fa;
        transition: transform 0.2s;
        text-decoration: none;
    }

    .fab-btn i {
        font-size: 1.5rem;
    }

    .fab-btn:active {
        transform: scale(0.95);
    }

    .fab-text {
        position: absolute;
        bottom: 5px;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    @media (max-width: 991.98px) {

        .navbar-desktop,
        .top-utility-bar {
            display: none !important;
        }
    }
</style>


<nav class="navbar navbar-expand-lg sticky-top navbar-desktop d-none d-lg-flex" id="mainNavbar">
    <div class="container">
        <a href="index.php" class="navbar-brand d-flex align-items-center gap-3">
            <img src="../uploads/solo_logo.jpg" alt="Logo" width="55" height="55" class="rounded-circle shadow-sm" style="object-fit: cover;">
            <div class="navbar-brand-text">
                <h5 class="m-0">Beach-Front</h5>
                <small class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">RESORT & GETAWAY</small>
            </div>
        </a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a href="index" class="nav-link <?= ($page == 'index') ? 'active' : '' ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a href="about" class="nav-link <?= ($page == 'about') ? 'active' : '' ?>">About</a>
                </li>
                
                <li class="nav-item">
                    <a href="tracking" class="nav-link <?= ($page == 'tracking') ? 'active' : '' ?>">Track Booking</a>
                </li>

                <li class="nav-item ms-3">
                    <a href="room" class="btn btn-primary btn-book-desktop rounded-pill text-white fw-bold shadow-sm">
                        Book Now
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<nav class="navbar navbar-light bg-white sticky-top shadow-sm d-lg-none px-3 py-2">
    <a href="index" class="navbar-brand d-flex align-items-center gap-2">
        <img src="../uploads/solo_logo.jpg" alt="Logo" width="40" height="40" class="rounded-circle border">
        <div class="navbar-brand-text">
            <h6 class="m-0 fw-bold text-primary">Beach-Front</h6>
        </div>
    </a>
    <a href="room" class="btn btn-outline-primary btn-sm rounded-pill px-3 ms-auto">Book</a>
</nav>

<nav class="navbar fixed-bottom mobile-bottom-nav d-flex d-lg-none justify-content-between align-items-center px-2">
    <a href="index" class="mobile-nav-item <?= ($page == 'index') ? 'active' : '' ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="service" class="mobile-nav-item <?= ($page == 'service') ? 'active' : '' ?>">
        <i class="fas fa-concierge-bell"></i>
        <span>Services</span>
    </a>
    <div class="fab-wrapper">
        <a href="room" class="fab-btn">
            <i class="fas fa-calendar-check"></i>
        </a>
        <span class="fab-text">Book</span>
    </div>
    <a href="tracking" class="mobile-nav-item <?= ($page == 'tracking') ? 'active' : '' ?>">
        <i class="fas fa-search"></i>
        <span>Track</span>
    </a>
    <a href="about" class="mobile-nav-item <?= ($page == 'about') ? 'active' : '' ?>">
        <i class="fas fa-info-circle"></i>
        <span>About</span>
    </a>
</nav>

<script>
    document.addEventListener("scroll", function() {
        const navbar = document.querySelector(".navbar-desktop");
        if (window.scrollY > 50) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });
</script>