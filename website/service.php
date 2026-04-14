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
             <h1 class="display-3 text-white animated slideInRight">Services</h1>
             <nav aria-label="breadcrumb">
                 <ol class="breadcrumb animated slideInRight mb-0">
                     <li class="breadcrumb-item"><a href="index">Home</a></li>
                     <li class="breadcrumb-item"><a href="#">Pages</a></li>
                     <li class="breadcrumb-item active" aria-current="page">Services</li>
                 </ol>
             </nav>
         </div>
     </div>
     <!-- Page Header End -->



     <div class="container-xxl py-5 mb-5">
         <div class="container">
             <!-- Section Header -->
             <div class="text-center mx-auto pb-4 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 650px;">
                 <p class="fw-medium text-uppercase text-primary mb-2 letter-spacing-1">Our Services</p>
                 <h1 class="display-5 fw-bold mb-4">What We Offer at Beach-Front Resort</h1>
                 <p class="text-muted">Relax, dine, and celebrate by the shore — discover the services we provide to make your stay memorable.</p>
             </div>

             <div class="row g-4">
                 <?php
                    $select_services = mysqli_query($conn, 'SELECT * FROM services_tbl WHERE status = 1');
                    while ($service = mysqli_fetch_assoc($select_services)) {
                    ?>
                     <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                         <div class="service-card p-4 text-center bg-white h-100 rounded-4 shadow-lg position-relative overflow-hidden">

                             <!-- Service Image -->
                             <div class="service-icon mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle overflow-hidden">
                                 <img src="./../user/admin/uploads/<?= $service['service_image'] ?>"
                                     alt="<?= $service['service_name'] ?>"
                                     class="img-fluid service-img">
                             </div>

                             <!-- Service Info -->
                             <h4 class="fw-bold mb-2 text-dark">
                                 <?= $service['service_name'] ?>
                             </h4>
                             <p class="text-muted mb-3 small">
                                 <?= $service['service_description'] ?>
                             </p>

                             <!-- Price -->
                             <span class="badge bg-gradient px-3 py-2 fs-6 text-dark">
                                 ₱<?= number_format($service['service_price'], 2) ?>
                             </span>
                         </div>
                     </div>
                 <?php } ?>
             </div>
         </div>

         <style>
             /* Service Card Styling */
             .service-card {
                 transition: transform 0.3s ease, box-shadow 0.3s ease;
             }

             .service-card:hover {
                 transform: translateY(-12px);
                 box-shadow: 0 1rem 2rem rgba(13, 110, 253, 0.25);
             }

             /* Image Circle */
             .service-icon {
                 width: 100px;
                 height: 100px;
                 background: #f8f9fa;
                 border: 3px solid #0d6efd;
             }

             /* Image Styling */
             .service-img {
                 width: 100%;
                 height: 100%;
                 object-fit: cover;
                 transition: 0.4s ease;
             }

             .service-icon:hover .service-img {
                 transform: scale(1.1);
             }

             /* Price Badge */
             .bg-gradient {
                 background: linear-gradient(135deg, #0d6efd, #6610f2);
                 color: #fff;
                 border-radius: 20px;
             }

             /* Letter Spacing */
             .letter-spacing-1 {
                 letter-spacing: 2px;
             }
         </style>
     </div>


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