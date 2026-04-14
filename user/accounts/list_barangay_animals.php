<?php
include '../../config.php';
 
// 1. KUNIN ANG BARANGAY ID MULA SA URL (Dashboard Drill-down)
// Kung walang ID na ipinasa, ibig sabihin gusto nilang makita lahat.
$barangay_id = isset($_GET['barangay_id']) ? $_GET['barangay_id'] : '';

// 2. KUNIN ANG PANGALAN NG BARANGAY PARA SA HEADER
$brgy_name_display = "All Barangays (Municipality-Wide)";
$where_clause = "";

if (!empty($barangay_id)) {
    $b_query = mysqli_query($conn, "SELECT barangay_name FROM barangay_tbl WHERE barangay_id = '$barangay_id'");
    if ($b_row = mysqli_fetch_assoc($b_query)) {
        $brgy_name_display = "Brgy. " . $b_row['barangay_name'];
        $where_clause = "WHERE a.barangay_id = '$barangay_id'";
    }
}

// 3. QUICK STATS LOGIC (Ilan ang aso, pusa, etc. sa barangay na ito?)
$stats_sql = "SELECT s.species_name, COUNT(a.record_id) as count 
              FROM animal_tbl a 
              JOIN species_tbl s ON a.species_id = s.species_id 
              $where_clause 
              GROUP BY s.species_id";
$stats_res = mysqli_query($conn, $stats_sql);
$animal_stats = [];
$total_animals = 0;
if ($stats_res) {
    while ($row = mysqli_fetch_assoc($stats_res)) {
        $animal_stats[$row['species_name']] = $row['count'];
        $total_animals += $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './../template/header.php' ?>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .brgy-header-card {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-badge {
            background-color: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .stat-badge strong { font-size: 1.1rem; margin-left: 5px; color: #f6c23e; }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include './../template/sidebar.php' ?>
        <div id="content-wrapper" class="d-flex flex-column bg-light">
            <div id="content">
                <?php include './../template/navbar.php'; ?>
                
                <div class="container-fluid pt-4">

                    <div class="d-sm-flex align-items-center justify-content-between mb-3">
                        <h1 class="h3 mb-0 fw-bold" style="color: #1b4332 !important;">
                            <i class="fas fa-paw mr-2"></i>Animals Masterlist
                        </h1>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm font-weight-bold shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-arrow-left mr-2"></i> Go Back
                        </a>
                    </div>

                    <div class="brgy-header-card">
                        <h4 class="font-weight-bold mb-3"><i class="fas fa-map-marker-alt mr-2 text-warning"></i> <?= htmlspecialchars($brgy_name_display) ?></h4>
                        
                        <div>
                            <span class="stat-badge">Total Animals: <strong><?= number_format($total_animals) ?></strong></span>
                            <?php foreach ($animal_stats as $species => $count): ?>
                                <span class="stat-badge"><?= htmlspecialchars($species) ?>: <strong><?= number_format($count) ?></strong></span>
                            <?php endforeach; ?>
                            <?php if (empty($animal_stats)): ?>
                                <span class="text-light small font-italic">No animals registered in this location yet.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card shadow mb-4" style="border-top: 4px solid #2d6a4f;">
                        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold" style="color: #1b4332;">Detailed Records</h6>
                            <?php if(empty($barangay_id)): ?>
                                <a href="animals.php" class="btn btn-sm text-white" style="background-color: #2d6a4f; border-radius: 8px;">
                                    <i class="fas fa-plus-circle me-2"></i> Go to Registration
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="animalTable" width="100%" cellspacing="0">
                                    <thead style="background-color: #f8f9fa; color: #1b4332;">
                                        <tr>
                                            <th>Animal ID</th>
                                            <th>Pet / Tag Name</th>
                                            <th>Species & Details</th>
                                            <th>Owner Information</th>
                                            <th>Health Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // ADVANCED QUERY: Checks if the animal exists in the vaccination table
                                        $sql = "SELECT a.*, b.barangay_name, s.species_name,
                                                (SELECT COUNT(log_id) FROM vaccination_tbl WHERE animal_id = a.record_id) as vax_count
                                                FROM animal_tbl a 
                                                LEFT JOIN barangay_tbl b ON a.barangay_id = b.barangay_id 
                                                LEFT JOIN species_tbl s ON a.species_id = s.species_id 
                                                $where_clause 
                                                ORDER BY a.created_at DESC";
                                                
                                        $result = mysqli_query($conn, $sql);
                                        
                                        if($result && mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                // Dynamic Icon based on species
                                                $icon = "fa-paw";
                                                if (stripos($row['species_name'], 'dog') !== false || stripos($row['species_name'], 'canine') !== false) $icon = "fa-dog text-primary";
                                                if (stripos($row['species_name'], 'cat') !== false || stripos($row['species_name'], 'feline') !== false) $icon = "fa-cat text-warning";
                                                
                                                // Vaccination Status Logic
                                                $is_vaccinated = $row['vax_count'] > 0;
                                                $health_badge = $is_vaccinated ? '<span class="badge badge-success p-2 w-100"><i class="fas fa-check-circle mr-1"></i> Vaccinated</span>' : '<span class="badge badge-danger p-2 w-100"><i class="fas fa-exclamation-triangle mr-1"></i> Unvaccinated</span>';
                                        ?>
                                                <tr>
                                                    <td class="font-weight-bold align-middle" style="color: #2d6a4f;">
                                                        <i class="fas fa-qrcode mr-1 text-muted"></i> <?= htmlspecialchars($row['animal_id_tag']) ?>
                                                    </td>
                                                    
                                                    <td class="font-weight-bold text-dark align-middle">
                                                        <i class="fas <?= $icon ?> mr-2"></i><?= htmlspecialchars($row['animal_name']) ?: '<span class="text-muted italic">No Name</span>' ?>
                                                    </td>
                                                    
                                                    <td class="align-middle">
                                                        <span class="badge badge-info p-1 mb-1"><?= htmlspecialchars($row['species_name']) ?></span>
                                                        <?= $row['is_fixed'] ? '<span class="badge badge-secondary p-1 mb-1">Kapon</span>' : '' ?>
                                                        <?= $row['is_stray'] ? '<span class="badge badge-warning text-dark p-1 mb-1">Stray</span>' : '' ?>
                                                        <br>
                                                        <small class="text-muted"><?= htmlspecialchars($row['breed']) ?> (<?= $row['sex'] ?>) - <?= htmlspecialchars($row['color']) ?></small>
                                                    </td>
                                                    
                                                    <td class="align-middle">
                                                        <i class="fas fa-user text-muted mr-1"></i> <strong><?= htmlspecialchars($row['owner_name']) ?></strong><br>
                                                        <small class="text-muted"><i class="fas fa-phone-alt mr-1"></i> <?= htmlspecialchars($row['contact_no']) ?: 'N/A' ?></small>
                                                    </td>
                                                    
                                                    <td class="align-middle text-center">
                                                        <?= $health_badge ?>
                                                    </td>
                                                    
                                                    <td class="text-center align-middle">
                                                        <a href="vaccination_logs.php?animal_id=<?= $row['record_id'] ?>" class="btn btn-success btn-sm shadow-sm font-weight-bold" style="background-color: #52b788; border: none;">
                                                            <i class="fas fa-syringe mr-1"></i> Vax Logs
                                                        </a>
                                                    </td>
                                                </tr>
                                        <?php 
                                            } 
                                        } 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include './../template/footer.php'; ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <?php include './../template/script.php'; ?>
    
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with advanced filtering options
            $('#animalTable').DataTable({
                "pageLength": 15,
                "language": { "search": "Search Animals:" },
                // Sort by ID descending (newest first)
                "order": [[ 0, "desc" ]]
            });
        });
    </script>
</body>
</html>