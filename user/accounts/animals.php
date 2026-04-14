<?php
include '../../config.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include './../template/header.php' ?>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            color: #495057;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .modal-xl {
            max-width: 1000px;
        }

        /* Custom Checkbox */
        .custom-control-label::before {
            border: 2px solid #2d6a4f;
        }

        .custom-checkbox .custom-control-input:checked~.custom-control-label::before {
            background-color: #2d6a4f;
            border-color: #2d6a4f;
        }

        #computed_age {
            font-weight: bold;
            color: #b02a30;
            background-color: #f8f9fa;
        }

        /* SPRINT 4: Registration Type Buttons */
        .reg-type-box {
            background: #e9f5e9;
            border: 2px solid #2d6a4f;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* SIGNATURE PAD CSS */
        .signature-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-color: #fff;
            border: 2px dashed #adb5bd;
            border-radius: 8px;
        }

        .signature-pad {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            cursor: crosshair;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include './../template/sidebar.php' ?>
        <div id="content-wrapper" class="d-flex flex-column bg-light">
            <div id="content">
                <?php include './../template/navbar.php'; ?>
                <?php

                $current_userid = $_SESSION['userid'];
                $current_fullname = $_SESSION['fullname'];

                // --- PHP PROCESSING LOGIC ---
                
                // 1. ADD NEW ANIMAL (With Error Handling & Logging)
                if (isset($_POST['add_animal'])) {

                    // SPRINT 4 LOGIC: Owned vs Stray Priority
                    $registration_type = $_POST['registration_type']; // Values: 'Owned' or 'Stray'
                    $is_stray = ($registration_type === 'Stray') ? 1 : 0;

                    // Barangay is always required (para alam kung saan nahuli/nakatira)
                    $barangay_id = $_POST['barangay_id'];

                    if ($is_stray == 1) {
                        // Kung Ligaw, i-default ang mga Owner Fields sa N/A
                        $owner_name = "None (Stray Animal)";
                        $contact_no = "N/A";
                        $owner_gender = "";
                        $owner_birthday = null;
                        $is_pwd = 0;
                        $is_senior = 0;
                        $owner_signature = ""; // Walang pirma dahil walang may-ari
                    } else {
                        // Kung Owned, kunin lahat ng tinype sa form
                        $owner_name = trim(ucwords($_POST['owner_name']));
                        $contact_no = trim($_POST['contact_no']);
                        $owner_gender = $_POST['owner_gender'];
                        $owner_birthday = !empty($_POST['owner_birthday']) ? $_POST['owner_birthday'] : null;
                        $is_pwd = isset($_POST['is_pwd']) ? 1 : 0;
                        $is_senior = isset($_POST['is_senior']) ? 1 : 0;
                        $owner_signature = $_POST['owner_signature']; // Base64 Signature Image
                    }

                    // Animal Info
                    $animal_name = trim(ucwords($_POST['animal_name']));
                    $species_id = $_POST['species_id']; // This is now labeled as "Animal Type"
                    $breed = trim(ucwords($_POST['breed']));
                    $sex = $_POST['sex'];
                    $color = trim(ucwords($_POST['color']));
                    $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;

                    $is_fixed = $_POST['is_fixed'];
                    $remarks = trim($_POST['remarks']);

                    // GENERATE ANIMAL ID
                    $brgy_query = mysqli_query($conn, "SELECT barangay_code FROM barangay_tbl WHERE barangay_id = '$barangay_id'");
                    $brgy_code = "UNK";
                    if ($brgy_row = mysqli_fetch_assoc($brgy_query)) {
                        $brgy_code = $brgy_row['barangay_code'];
                    }

                    $year = date("Y");
                    $rand_num = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    $animal_id_tag = $brgy_code . "-" . $year . "-" . $rand_num;

                    // INSERT QUERY
                    $sql = "INSERT INTO animal_tbl (
                animal_id_tag, owner_name, contact_no, barangay_id, owner_gender, owner_birthday, is_pwd, is_senior, owner_signature,
                animal_name, species_id, breed, sex, color, birth_date, is_stray, is_fixed, remarks
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);

                    if ($stmt) {
                        $stmt->bind_param(
                            "sssisssisssssssiis",
                            $animal_id_tag,
                            $owner_name,
                            $contact_no,
                            $barangay_id,
                            $owner_gender,
                            $owner_birthday,
                            $is_pwd,
                            $is_senior,
                            $owner_signature,
                            $animal_name,
                            $species_id,
                            $breed,
                            $sex,
                            $color,
                            $birth_date,
                            $is_stray,
                            $is_fixed,
                            $remarks
                        );

                        if ($stmt->execute()) {
                            // SPRINT 1: SUCCESS LOG
                            logSystemActivity($conn, $current_userid, $current_fullname, "Registered new animal ($registration_type): $animal_name (ID: $animal_id_tag)");

                            echo '<script>
                document.addEventListener("DOMContentLoaded", function () {
                    Swal.fire({ icon: "success", title: "Animal Registered", text: "Generated ID: ' . $animal_id_tag . '", confirmButtonColor: "#1b4332" });
                });
            </script>';
                        } else {
                            // SPRINT 1: ERROR LOG
                            $error_msg = $stmt->error;
                            logSystemActivity($conn, $current_userid, $current_fullname, "Failed to register animal ID $animal_id_tag. Error: $error_msg", "Error");

                            echo '<script>
                document.addEventListener("DOMContentLoaded", function () {
                    Swal.fire({ icon: "error", title: "Database Error", text: "Could not save the record. Please contact support.", confirmButtonColor: "#e63946" });
                });
            </script>';
                        }
                        $stmt->close();
                    } else {
                        logSystemActivity($conn, $current_userid, $current_fullname, "System Architecture Error in animal registration query.", "Error");
                    }
                }
                // ==========================================
// UPDATE ANIMAL
// ==========================================
if (isset($_POST['update_animal'])) {

    $id = $_POST['record_id'];
    $animal_name = trim($_POST['animal_name']);
    $breed = trim($_POST['breed']);
    $color = trim($_POST['color']);
    $owner_name = trim($_POST['owner_name']);

    $stmt = $conn->prepare("UPDATE animal_tbl 
        SET animal_name=?, breed=?, color=?, owner_name=? 
        WHERE record_id=?");

    $stmt->bind_param("ssssi", $animal_name, $breed, $color, $owner_name, $id);

    if ($stmt->execute()) {
        logSystemActivity($conn, $current_userid, $current_fullname, "Updated animal ID: $id");

        echo "<script>
            Swal.fire('Updated!', 'Animal record updated.', 'success')
            .then(()=> window.location.href='animals.php');
        </script>";
    }
}

// ==========================================
// DELETE ANIMAL
// ==========================================
if (isset($_POST['delete_animal'])) {

    $id = $_POST['record_id'];

    $stmt = $conn->prepare("DELETE FROM animal_tbl WHERE record_id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logSystemActivity($conn, $current_userid, $current_fullname, "Deleted animal ID: $id");

        echo "<script>
            Swal.fire('Deleted!', 'Animal record removed.', 'success')
            .then(()=> window.location.href='animals.php');
        </script>";
    }
}
                ?>
                <div class="container-fluid pt-4">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 fw-bold" style="color: #1b4332 !important;"><i
                                class="fas fa-paw mr-2"></i>Animals Masterlist</h1>
                        <button class="btn text-white shadow-sm px-4"
                            style="background-color: #2d6a4f; border-radius: 8px;" data-toggle="modal"
                            data-target="#addAnimalModal">
                            <i class="fas fa-plus-circle me-2"></i> Register New Animal
                        </button>
                    </div>

                    <div class="card shadow mb-4" style="border-top: 4px solid #2d6a4f;">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered align-middle" id="animalTable"
                                    width="100%" cellspacing="0">
                                    <thead style="background-color: #f8f9fa; color: #1b4332;">
                                        <tr>
                                            <th>Animal ID</th>
                                            <th>Pet / Tag Name</th>
                                            <th>Animal Type & Details</th>
                                            <th>Owner Name</th>
                                            <th>Barangay</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // SPRINT 4: MASTERLIST UPGRADE (Added vax_sessions counter subquery)
                                        $sql = "SELECT a.*, b.barangay_name, s.species_name,
                                                (SELECT COUNT(log_id) FROM vaccination_tbl WHERE animal_id = a.record_id) as vax_sessions
                                                FROM animal_tbl a 
                                                LEFT JOIN barangay_tbl b ON a.barangay_id = b.barangay_id 
                                                LEFT JOIN species_tbl s ON a.species_id = s.species_id 
                                                ORDER BY a.created_at DESC";
                                        $result = mysqli_query($conn, $sql);

                                        if ($result && mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $icon = "fa-paw";
                                                if (stripos($row['species_name'], 'dog') !== false || stripos($row['species_name'], 'canine') !== false)
                                                    $icon = "fa-dog text-primary";
                                                if (stripos($row['species_name'], 'cat') !== false || stripos($row['species_name'], 'feline') !== false)
                                                    $icon = "fa-cat text-warning";

                                                // Counter Badge Logic
                                                $vax_count = (int) $row['vax_sessions'];
                                                if ($vax_count > 0) {
                                                    $session_badge = "<span class='badge badge-success mt-1'><i class='fas fa-shield-alt mr-1'></i> $vax_count Vax Session(s)</span>";
                                                } else {
                                                    $session_badge = "<span class='badge badge-danger mt-1'><i class='fas fa-exclamation-circle mr-1'></i> No Vax Record</span>";
                                                }
                                                ?>
                                                <tr>
                                                    <td class="font-weight-bold align-middle" style="color: #2d6a4f;">
                                                        <i class="fas fa-qrcode mr-1 text-muted"></i>
                                                        <?= htmlspecialchars($row['animal_id_tag']) ?>
                                                    </td>
                                                    <td class="font-weight-bold text-dark align-middle">
                                                        <i
                                                            class="fas <?= $icon ?> mr-2"></i><?= htmlspecialchars($row['animal_name']) ?: '<span class="text-muted italic">No Name (Farm Tag)</span>' ?><br>
                                                        <?= $session_badge ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span
                                                            class="badge badge-info p-1 mb-1"><?= htmlspecialchars($row['species_name']) ?></span>
                                                        <?= $row['is_fixed'] ? '<span class="badge badge-success p-1 mb-1"><i class="fas fa-check"></i> Kapon</span>' : '' ?>
                                                        <?= $row['is_stray'] ? '<span class="badge badge-warning text-dark p-1 mb-1">Stray</span>' : '' ?>
                                                        <br>
                                                        <small class="text-muted"><?= htmlspecialchars($row['breed']) ?>
                                                            (<?= $row['sex'] ?>) -
                                                            <?= htmlspecialchars($row['color']) ?></small>
                                                    </td>
                                                    <td class="align-middle">
                                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                                        <?= htmlspecialchars($row['owner_name']) ?><br>
                                                        <small class="text-muted"><i class="fas fa-phone-alt"></i>
                                                            <?= htmlspecialchars($row['contact_no']) ?></small>
                                                    </td>
                                                    <td class="align-middle"><?= htmlspecialchars($row['barangay_name']) ?></td>

                                                    <td class="text-center align-middle">

    <!-- VAX -->
    <a href="vaccination_logs.php?animal_id=<?= $row['record_id'] ?>"
        class="btn btn-success btn-sm mb-1"
        style="background-color: #52b788; border: none;">
        <i class="fas fa-syringe"></i>
    </a>

    <!-- EDIT -->
    <button class="btn btn-warning btn-sm mb-1"
        data-toggle="modal"
        data-target="#editModal<?= $row['record_id'] ?>">
        <i class="fas fa-edit"></i>
    </button>

    <!-- DELETE -->
    <button class="btn btn-danger btn-sm mb-1"
        data-toggle="modal"
        data-target="#deleteModal<?= $row['record_id'] ?>">
        <i class="fas fa-trash"></i>
    </button>

</td>
                                                </tr>
                                                <div class="modal fade" id="editModal<?= $row['record_id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Animal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="record_id" value="<?= $row['record_id'] ?>">

                    <div class="form-group">
                        <label>Animal Name</label>
                        <input type="text" name="animal_name" class="form-control"
                            value="<?= htmlspecialchars($row['animal_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Breed</label>
                        <input type="text" name="breed" class="form-control"
                            value="<?= htmlspecialchars($row['breed']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" class="form-control"
                            value="<?= htmlspecialchars($row['color']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Owner Name</label>
                        <input type="text" name="owner_name" class="form-control"
                            value="<?= htmlspecialchars($row['owner_name']) ?>">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" name="update_animal" class="btn btn-success">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteModal<?= $row['record_id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Animal</h5>
                </div>

                <div class="modal-body">
                    <p>Are you sure you want to delete this animal?</p>
                    <strong><?= htmlspecialchars($row['animal_name']) ?></strong>

                    <input type="hidden" name="record_id" value="<?= $row['record_id'] ?>">
                </div>

                <div class="modal-footer">
                    <button type="submit" name="delete_animal" class="btn btn-danger">
                        Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
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

    <div class="modal fade" id="addAnimalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <form method="POST" id="registrationForm">
                    <div class="modal-header text-white"
                        style="background-color: #1b4332; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-file-signature mr-2"></i>Official
                            Registration Form</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-4 bg-light">

                        <div class="reg-type-box shadow-sm">
                            <label class="font-weight-bold d-block text-dark mb-2"><i
                                    class="fas fa-list-ul mr-2"></i>Registration Type (Select First) *</label>
                            <div class="custom-control custom-radio custom-control-inline mr-4">
                                <input type="radio" id="typeOwned" name="registration_type" value="Owned"
                                    class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-bold text-success"
                                    style="font-size:1.1rem;" for="typeOwned">Owned Pet</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="typeStray" name="registration_type" value="Stray"
                                    class="custom-control-input">
                                <label class="custom-control-label font-weight-bold text-danger"
                                    style="font-size:1.1rem;" for="typeStray">Stray Animal (Ligaw)</label>
                            </div>
                        </div>

                        <div class="form-group mb-4 bg-white p-3 border rounded shadow-sm">
                            <label class="small font-weight-bold text-dark">Barangay Location (Where animal is
                                located/caught) *</label>
                            <select name="barangay_id" class="form-control select2-single" style="width: 100%;"
                                required>
                                <option value="">Select Barangay...</option>
                                <?php
                                $b_query = mysqli_query($conn, "SELECT * FROM barangay_tbl ORDER BY barangay_name ASC");
                                while ($b = mysqli_fetch_assoc($b_query)) {
                                    echo "<option value='{$b['barangay_id']}'>{$b['barangay_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row">

                            <div class="col-md-5 border-right" id="ownerInfoSection">
                                <h6 class="font-weight-bold mb-3 pb-2"
                                    style="color: #2d6a4f; border-bottom: 2px solid #ccc;">
                                    <i class="fas fa-user mr-2"></i>1. Owner's Information
                                </h6>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Owner's Full Name *</label>
                                    <input type="text" name="owner_name" class="form-control owner-field"
                                        placeholder="e.g., Juan Dela Cruz" required>
                                </div>

                                <div class="row">
                                    <div class="form-group mb-3 col-md-6">
                                        <label class="small font-weight-bold">Gender *</label>
                                        <select name="owner_gender" class="form-control owner-field" required>
                                            <option value="">Select...</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3 col-md-6">
                                        <label class="small font-weight-bold">Birthday</label>
                                        <input type="date" name="owner_birthday" id="owner_birthday"
                                            class="form-control owner-field">
                                    </div>
                                </div>

                                <div class="d-flex mb-3">
                                    <div class="custom-control custom-checkbox mr-4">
                                        <input type="checkbox" class="custom-control-input owner-field" id="checkPWD"
                                            name="is_pwd" value="1">
                                        <label class="custom-control-label font-weight-bold small pt-1"
                                            for="checkPWD">PWD</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input owner-field" id="checkSenior"
                                            name="is_senior" value="1">
                                        <label class="custom-control-label font-weight-bold small pt-1"
                                            for="checkSenior">Senior Citizen</label>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Contact Number *</label>
                                    <input type="text" name="contact_no" class="form-control owner-field"
                                        placeholder="09XXXXXXXXX" maxlength="11" minlength="11" pattern="[0-9]{11}"
                                        required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>

                                <div class="form-group mt-4">
                                    <label class="small font-weight-bold text-dark"><i class="fas fa-pen-nib mr-1"></i>
                                        Owner's Digital Signature *</label>
                                    <div class="signature-wrapper">
                                        <canvas id="signature-pad" class="signature-pad"></canvas>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted italic">Please sign inside the box.</small>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                                            id="clear-signature">Clear</button>
                                    </div>
                                    <input type="hidden" name="owner_signature" id="hidden_signature_data">
                                </div>
                            </div>

                            <div class="col-md-7 pl-4" id="petInfoSection">
                                <h6 class="font-weight-bold mb-3 pb-2"
                                    style="color: #2d6a4f; border-bottom: 2px solid #ccc;">
                                    <i class="fas fa-paw mr-2"></i>2. Animal's Information
                                </h6>

                                <div class="row">
                                    <div class="form-group mb-3 col-md-7">
                                        <label class="small font-weight-bold">Pet / Tag Name *</label>
                                        <input type="text" name="animal_name" class="form-control"
                                            placeholder="e.g., Bantay (Or 'Tag-01' if stray)" required>
                                    </div>
                                    <div class="form-group mb-3 col-md-5">
                                        <label class="small font-weight-bold">Animal Type *</label>
                                        <select name="species_id" class="form-control select2-single"
                                            style="width: 100%;" required>
                                            <option value="">Select Type...</option>
                                            <?php
                                            $s_query = mysqli_query($conn, "SELECT * FROM species_tbl ORDER BY species_name ASC");
                                            while ($s = mysqli_fetch_assoc($s_query)) {
                                                echo "<option value='{$s['species_id']}'>{$s['species_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group mb-3 col-md-5">
                                        <label class="small font-weight-bold">Birth Date / Est. Date *</label>
                                        <input type="date" name="birth_date" id="pet_birth_date" class="form-control"
                                            required>
                                    </div>
                                    <div class="form-group mb-3 col-md-4">
                                        <label class="small font-weight-bold">Calculated Age</label>
                                        <input type="text" id="computed_age" class="form-control"
                                            placeholder="0 yrs, 0 mos" readonly>
                                    </div>
                                    <div class="form-group mb-3 col-md-3">
                                        <label class="small font-weight-bold">Sex *</label>
                                        <select name="sex" class="form-control" required>
                                            <option value="M">M</option>
                                            <option value="F">F</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group mb-3 col-md-6">
                                        <label class="small font-weight-bold">Breed *</label>
                                        <input type="text" name="breed" class="form-control"
                                            placeholder="e.g., Aspin, Puspin" required>
                                    </div>
                                    <div class="form-group mb-3 col-md-6">
                                        <label class="small font-weight-bold">Color / Markings *</label>
                                        <input type="text" name="color" class="form-control"
                                            placeholder="e.g., Brown, Spotted" required>
                                    </div>
                                </div>

                                <div class="row p-3 bg-white border rounded mx-0 mb-3 shadow-sm">
                                    <div class="form-group mb-0 col-md-12">
                                        <label class="small font-weight-bold d-block">Fixed Status</label>
                                        <div class="custom-control custom-radio custom-control-inline mt-1">
                                            <input type="radio" id="kapon" name="is_fixed" value="1"
                                                class="custom-control-input">
                                            <label class="custom-control-label small" for="kapon">Kapon / Ligate</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline mt-1">
                                            <input type="radio" id="hindiKapon" name="is_fixed" value="0"
                                                class="custom-control-input" checked>
                                            <label class="custom-control-label small" for="hindiKapon">Hindi
                                                Kapon</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold">Remarks</label>
                                    <input type="text" name="remarks" class="form-control"
                                        placeholder="Any additional notes...">
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0"
                        style="background-color: #e9ecef; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_animal" class="btn text-white px-5"
                            style="background-color: #1b4332; font-weight: bold;">
                            <i class="fas fa-save mr-2"></i> Register Animal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <?php include './../template/script.php'; ?>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#animalTable').DataTable({
                "pageLength": 10,
                "language": { "search": "Search Record:" },
                "order": [[0, "desc"]] // Sort by newest registered
            });

            $('.select2-single').select2({ dropdownParent: $('#addAnimalModal') });

            // ==========================================
            // SPRINT 4: OWNED VS STRAY LOGIC
            // ==========================================
            $('input[name="registration_type"]').on('change', function () {
                var isStray = $(this).val() === 'Stray';

                if (isStray) {
                    // Hide Owner Section and remove required tags
                    $('#ownerInfoSection').slideUp();
                    $('#petInfoSection').removeClass('col-md-7 pl-4').addClass('col-md-12'); // Expand pet info
                    $('.owner-field').prop('required', false);
                } else {
                    // Show Owner Section and restore required tags
                    $('#petInfoSection').removeClass('col-md-12').addClass('col-md-7 pl-4'); // Shrink pet info
                    $('#ownerInfoSection').slideDown();

                    // Only these are mandatory for owner
                    $('input[name="owner_name"], select[name="owner_gender"], input[name="contact_no"]').prop('required', true);
                }
            });


            // ==========================================
            // JAVASCRIPT: SIGNATURE PAD LOGIC
            // ==========================================
            var canvas = document.getElementById('signature-pad');

            function resizeCanvas() {
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
            }

            $('#addAnimalModal').on('shown.bs.modal', function () {
                resizeCanvas();
                window.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 1)',
                    penColor: 'rgb(0, 0, 0)'
                });
            });

            document.getElementById('clear-signature').addEventListener('click', function () {
                if (window.signaturePad) window.signaturePad.clear();
            });

            // Before Form Submits, validate and get signature image
            $('#registrationForm').on('submit', function (e) {
                var type = $('input[name="registration_type"]:checked').val();

                // Require signature ONLY if it's an Owned Pet
                if (type === 'Owned') {
                    if (window.signaturePad.isEmpty()) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Signature Required',
                            text: 'Please ask the owner to sign the digital pad before saving.',
                            confirmButtonColor: '#1b4332'
                        });
                        return false;
                    }
                    var dataUrl = window.signaturePad.toDataURL('image/png');
                    $('#hidden_signature_data').val(dataUrl);
                } else {
                    $('#hidden_signature_data').val(''); // Empty for stray
                }
            });

            // ==========================================
            // JAVASCRIPT: AUTO COMPUTE PET AGE
            // ==========================================
            $('#pet_birth_date').on('change', function () {
                var birthDate = new Date($(this).val());
                var today = new Date();
                if (birthDate > today) {
                    $('#computed_age').val("Invalid Date");
                    return;
                }
                var years = today.getFullYear() - birthDate.getFullYear();
                var months = today.getMonth() - birthDate.getMonth();
                if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) {
                    years--; months += 12;
                }
                var ageString = "";
                if (years > 0) ageString += years + " yr(s) ";
                if (months > 0 || years === 0) ageString += months + " mo(s)";
                $('#computed_age').val(ageString);
            });

            // ==========================================
            // JAVASCRIPT: AUTO-CHECK SENIOR CITIZEN
            // ==========================================
            $('#owner_birthday').on('change', function () {
                var bdate = new Date($(this).val());
                var today = new Date();
                var age = today.getFullYear() - bdate.getFullYear();
                var m = today.getMonth() - bdate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < bdate.getDate())) { age--; }
                if (age >= 60) { $('#checkSenior').prop('checked', true); }
                else { $('#checkSenior').prop('checked', false); }
            });
        });
    </script>
</body>

</html>