 <?php
 
 include './../config.php';
    include './template/header.php';
    ?>

 <body>
     <!-- Spinner Start -->
     <div id="spinner"
         class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
         <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
     </div>
     <!-- Spinner End -->



     <!-- Font Awesome -->
     <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

     <?php
        include './template/navbar.php';
        ?>


     <!-- Page Header Start -->
     <div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s">
         <div class="container py-5">
             <h1 class="display-3 text-white animated slideInRight">About Us</h1>
             <nav aria-label="breadcrumb">
                 <ol class="breadcrumb animated slideInRight mb-0">
                     <li class="breadcrumb-item"><a href="#">Home</a></li>
                     <li class="breadcrumb-item"><a href="#">Pages</a></li>
                     <li class="breadcrumb-item active" aria-current="page">About Us</li>
                 </ol>
             </nav>
         </div>
     </div>
     <!-- Page Header End -->



     <!-- About Start -->
     <div class="container-xxl py-5">
         <div class="container">
             <div class="row g-5">
                 <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                     <p class="fw-medium text-uppercase text-primary mb-2">About Beach-Front Resort</p>
                     <h1 class="display-5 mb-4">Your Smart Beach Resort Reservation & Management System</h1>
                     <p class="mb-4">
                         Beach-Front Resort is a modern platform built to make beach resort reservations and service management
                         simple, fast, and stress-free. From booking cozy rooms to planning unforgettable events,
                         Beach-Front Resort ensures a smooth and hassle-free experience for guests and resort staff alike.
                     </p>

                     <div class="d-flex align-items-center mb-4">
                         <div class="flex-shrink-0 bg-primary p-4 text-center rounded-3 shadow">
                             <h1 class="display-5 text-white">24/7</h1>
                             <h6 class="text-white mb-0">Seamless</h6>
                             <h6 class="text-white">Support</h6>
                         </div>
                         <div class="ms-4">
                             <p><i class="fa fa-check text-primary me-2"></i>Easy Room Reservations</p>
                             <p><i class="fa fa-check text-primary me-2"></i>Real-Time Availability</p>
                             <p><i class="fa fa-check text-primary me-2"></i>Event & Function Bookings</p>
                             <p><i class="fa fa-check text-primary me-2"></i>Secure Online Payments</p>
                             <p class="mb-0"><i class="fa fa-check text-primary me-2"></i>Dedicated Customer Service</p>
                         </div>
                     </div>

                     <div class="row pt-2">
                         <div class="col-sm-6">
                             <div class="d-flex align-items-center">
                                 <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                     <i class="fa fa-envelope-open text-white"></i>
                                 </div>
                                 <div class="ms-3">
                                     <p class="mb-2">Email us</p>
                                     <h5 class="mb-0">beachfrontresort149@gmail.com</h5>
                                 </div>
                             </div>
                         </div>
                         <div class="col-sm-7   ">
                             <div class="d-flex align-items-center">
                                 <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                     <i class="fa fa-phone-alt text-white"></i>
                                 </div>
                                 <div class="ms-3">
                                     <p class="mb-2">Call us</p>
                                     <h5 class="mb-0">+63 905 300 7306</h5>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>

             </div>
         </div>
     </div>

     <!-- About End -->


     <!-- Facts Start -->
     

     <!-- PureCounter.js -->
     <script src="https://cdn.jsdelivr.net/npm/purecounterjs@1.5.0/dist/purecounter_vanilla.js"></script>


     <!-- Location Section -->
     <div class="container-xxl py-5 my-5">
         <div class="container">
             <div class="text-center mb-5">
                 <p class="fw-medium text-uppercase text-primary mb-2">Our Location</p>
                 <h1 class="display-5 mb-3">Visit Beach-Front Resort Beach Resort</h1>
                 <p class="fs-5 mb-0 text-secondary">
                     Located in Polo, Santa Cruz, Marinduque, Beach-Front Resort Beach Resort is the perfect destination for
                     families, friends, and couples.
                     Enjoy pristine beaches, crystal-clear waters, breathtaking sunsets, and a relaxing atmosphere that
                     combines luxury with nature.
                 </p>
             </div>

             <div class="row justify-content-center">
                 <div class="col-lg-10">
                     <div class="map-container rounded-4 overflow-hidden shadow-lg">
                         <iframe
                             src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3875.123456789!2d121.066!3d13.436!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397eabcdef1234%3A0xabcdef1234567890!2sPolo%2C%20Santa%20Cruz%2C%20Marinduque!5e0!3m2!1sen!2sph!4v1690000000000!5m2!1sen!2sph"
                             width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                             referrerpolicy="no-referrer-when-downgrade">
                         </iframe>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <!-- Optional Custom CSS for Premium Style -->
     <style>
         .map-container iframe {
             border-radius: 20px;
             border: 2px solid rgba(0, 0, 0, 0.1);
             transition: transform 0.3s ease, box-shadow 0.3s ease;
         }

         .map-container iframe:hover {
             transform: scale(1.02);
             box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
         }

         .text-primary {
             color: #0d6efd !important;
             /* Beach-Front Resort theme color */
         }

         .text-secondary {
             color: #555 !important;
             /* subtle, professional text */
         }
     </style>

     <br><br><br>
     <br><br><br>
     <footer class="container-fluid bg-dark text-white py-3 d-none d-lg-block">
         <div class="text-center">
             © 2025 Beach-Front Resort. All Rights Reserved.
         </div>
     </footer>

     <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded-circle back-to-top"><i
             class="bi bi-arrow-up"></i></a>

     <?php
        include './template/script.php';
        ?>
 </body>

 </html>