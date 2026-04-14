<?php
session_start();

// Cybersecurity: Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: ./../../");
    exit();
}

$userid = $_SESSION['userid'] ?? null;
// Cybersecurity: Fetch user role from session for Role-Based Access Control (RBAC)
// Roles: 'Administrator', 'Support Staff', 'Field Vaccinator'
$user_role = $_SESSION['role'] ?? 'Field Vaccinator';

$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Define grouped pages based on VAX-IN requirements
$record_pages = ['animals', 'add_animal', 'vaccination_logs'];
$report_pages = ['reports_barangay', 'reports_summary', 'reports_mimaropa', 'vaccination_status', 'list_barangay_animals'];
$admin_pages = ['manage_accounts', 'pending_accounts', 'activity_log'];
?>

<!-- <style>
    /* ENTERPRISE STICKY SIDEBAR FIX */
    .sidebar {
        position: sticky !important;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        z-index: 1000;
    }
    .sidebar::-webkit-scrollbar {
        width: 1px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }
</style> -->
<style>
    /* 1. Main Sidebar Background - "Green Agri" Gradient */
    #accordionSidebar {
        background: linear-gradient(180deg, #1b4332 0%, #2d6a4f 100%) !important;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    /* 2. Navigation Links Styling */
    .nav-item .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    /* 3. Hover Effect */
    .nav-item .nav-link:hover {
        color: #fff !important;
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }

    /* 4. Active/Current Page Styling - Soft Mint Accent */
    .nav-item.active>.nav-link {
        background-color: rgba(255, 255, 255, 0.2) !important;
        color: #fff !important;
        font-weight: bold;
        border-left: 4px solid #74c69d;
        /* Mint Green Border */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* 5. Icons Styling */
    .nav-link i {
        color: #b7e4c7;
        /* Light Green Icons */
        margin-right: 10px;
    }

    .nav-item.active .nav-link i {
        color: #fff;
    }

    /* 6. Section Headings */
    .sidebar-heading {
        color: rgba(255, 255, 255, 0.6) !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 15px;
        margin-bottom: 5px;
    }

    /* 7. Dropdown/Collapse Menu Styling */
    .collapse-inner {
        background-color: #f8f9fa !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border: none;
    }

    .collapse-item {
        color: #495057 !important;
        margin: 2px 5px;
        border-radius: 5px;
        transition: 0.2s;
    }

    .collapse-item:hover {
        background-color: #e9ecef !important;
        color: #1b4332 !important;
        /* Deep Green Text on Hover */
        padding-left: 15px !important;
    }

    .collapse-item.active {
        background-color: #1b4332 !important;
        color: #fff !important;
        font-weight: 600;
    }

    /* Logo Styling */
    .sidebar-brand-icon img {
        border: 2px solid rgba(255, 255, 255, 0.7) !important;
        transition: transform 0.3s;
    }

    .sidebar-brand-icon:hover img {
        transform: scale(1.1);
        border-color: #fff !important;
    }
</style>

<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center py-3" href="index">
        <div class="sidebar-brand-icon">
            <img src="./../img/logo.png" width="60" height="60" style="border-radius:50%; object-fit: cover;"
                alt="VAX-IN Logo">
        </div>
        <div class="sidebar-brand-text mx-3">VAX-IN <sup>MOGPOG MAO</sup></div>
    </a>

    <hr class="sidebar-divider my-2">

    <li class="nav-item <?= $current_page == 'index' ? 'active' : '' ?>">
        <a class="nav-link" href="index">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block my-1">

    <div class="sidebar-heading">Data Management</div>

    <li class="nav-item <?= in_array($current_page, $record_pages) ? 'active' : '' ?>">
        <a class="nav-link <?= in_array($current_page, $record_pages) ? '' : 'collapsed' ?>" href="#"
            data-toggle="collapse" data-target="#records"
            aria-expanded="<?= in_array($current_page, $record_pages) ? 'true' : 'false' ?>" aria-controls="records">
            <i class="fas fa-fw fa-paw"></i>
            <span>Pet Records</span>
        </a>
        <div id="records" class="collapse <?= in_array($current_page, $record_pages) ? 'show' : '' ?>"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Manage Pets:</h6>
                <a class="collapse-item <?= $current_page == 'animals' ? 'active' : '' ?>" href="animals">Animals
                    Masterlist</a>
                <a class="collapse-item <?= $current_page == 'vaccination_logs' ? 'active' : '' ?>"
                    href="vaccination_logs">Vaccination History</a>
            </div>
        </div>
    </li>

    <?php if ($user_role === 'Administrator' || $user_role === 'Support Staff'): ?>
        <hr class="sidebar-divider d-none d-md-block my-1">

        <div class="sidebar-heading">Analytics</div>

        <li class="nav-item <?= in_array($current_page, $report_pages) ? 'active' : '' ?>">
            <a class="nav-link <?= in_array($current_page, $report_pages) ? '' : 'collapsed' ?>" href="#"
                data-toggle="collapse" data-target="#reports"
                aria-expanded="<?= in_array($current_page, $report_pages) ? 'true' : 'false' ?>" aria-controls="reports">
                <i class="fas fa-fw fa-file-pdf"></i>
                <span>Reports (PDF)</span>
            </a>
            <div id="reports" class="collapse <?= in_array($current_page, $report_pages) ? 'show' : '' ?>"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Generate Forms:</h6>
                    <a class="collapse-item <?= $current_page == 'list_barangay_animals' ? 'active' : '' ?>"
                        href="list_barangay_animals">Animal List </a>
                    <a class="collapse-item <?= $current_page == 'reports_barangay' ? 'active' : '' ?>"
                        href="reports_barangay">By Barangay</a>
                    <a class="collapse-item <?= $current_page == 'reports_mimaropa' ? 'active' : '' ?>"
                        href="reports_mimaropa">Report MIMAROPA
                        <br> Accomplishment</a>
                    <a class="collapse-item <?= $current_page == 'reports_summary' ? 'active' : '' ?>"
                        href="reports_summary">Vaccine Summary</a>
                    <a class="collapse-item <?= $current_page == 'vaccination_status' ? 'active' : '' ?>"
                        href="vaccination_status">Vaccination Status </a>
                </div>
            </div>
        </li>
    <?php endif; ?>
    <?php if ($user_role === 'Administrator'): ?>
        <hr class="sidebar-divider d-none d-md-block my-1">

        <div class="sidebar-heading">System Setup</div>

        <?php
        $maintenance_pages = ['maintenance_barangay', 'maintenance_pets', 'maintenance_vaccines'];
        ?>

        <li class="nav-item <?= in_array($current_page, $maintenance_pages) ? 'active' : '' ?>">
            <a class="nav-link <?= in_array($current_page, $maintenance_pages) ? '' : 'collapsed' ?>" href="#"
                data-toggle="collapse" data-target="#maintenance"
                aria-expanded="<?= in_array($current_page, $maintenance_pages) ? 'true' : 'false' ?>"
                aria-controls="maintenance">
                <i class="fas fa-fw fa-cogs"></i>
                <span>Maintenance</span>
            </a>
            <div id="maintenance" class="collapse <?= in_array($current_page, $maintenance_pages) ? 'show' : '' ?>"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">System Tables:</h6>
                    <a class="collapse-item <?= $current_page == 'maintenance_barangay' ? 'active' : '' ?>"
                        href="maintenance_barangay">Manage Barangays</a>
                    <a class="collapse-item <?= $current_page == 'maintenance_pets' ? 'active' : '' ?>"
                        href="maintenance_pets">Pet Categories</a>
                    <a class="collapse-item <?= $current_page == 'maintenance_vaccines' ? 'active' : '' ?>"
                        href="maintenance_vaccines">Vaccine Types</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <?php if ($user_role === 'Administrator'): ?>
        <hr class="sidebar-divider d-none d-md-block my-1">

        <div class="sidebar-heading">System Control</div>

        <li class="nav-item <?= in_array($current_page, $admin_pages) ? 'active' : '' ?>">
            <a class="nav-link <?= in_array($current_page, $admin_pages) ? '' : 'collapsed' ?>" href="#"
                data-toggle="collapse" data-target="#admin"
                aria-expanded="<?= in_array($current_page, $admin_pages) ? 'true' : 'false' ?>" aria-controls="admin">
                <i class="fas fa-fw fa-users-cog"></i>
                <span>Administration</span>
            </a>
            <div id="admin" class="collapse <?= in_array($current_page, $admin_pages) ? 'show' : '' ?>"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Access Management:</h6>
                    <a class="collapse-item <?= $current_page == 'manage_accounts' ? 'active' : '' ?>"
                        href="manage_accounts">Manage User Accounts</a>
                    <div class="collapse-divider"></div>
                    <h6 class="collapse-header">System Logs:</h6>
                    <a class="collapse-item <?= $current_page == 'activity_log' ? 'active' : '' ?>"
                        href="activity_log">Activity Log</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <li class="nav-item">
        <a class="nav-link" href="#" id="signOutLink">
            <i class="fas fa-fw fa-sign-out-alt text-danger"></i>
            <span class="text-danger">Sign Out</span>
        </a>
    </li>

    <div class="text-center d-none d-md-inline mt-3">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('signOutLink')?.addEventListener('click', function (event) {
        event.preventDefault();
        Swal.fire({
            title: 'Sign Out?',
            text: "Are you sure you want to end your secure session?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1b4332', // Matched with the new Deep Green theme
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, sign out',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Ensure this points to your actual logout handler
                window.location.href = '../../logout.php';
            }
        });
    });
</script>