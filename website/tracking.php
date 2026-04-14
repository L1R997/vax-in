<?php
include './../config.php';
include './template/header.php';
?>

<style>
    .tracking-card {
        border: none;
        border-radius: 20px;
        background: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .tracking-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
        padding: 30px;
        color: white;
        text-align: center;
        position: relative;
    }

    .tracking-header::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 0;
        right: 0;
        height: 40px;
        background: white;
        border-radius: 50% 50% 0 0;
    }

    .status-pill {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-block;
        margin-top: 10px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px dashed #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #6c757d;
        font-weight: 500;
    }

    .info-value {
        color: #212529;
        font-weight: 700;
    }

    .balance-card {
        background: #fff3cd;
        color: #856404;
        border-radius: 15px;
        padding: 15px;
        margin-top: 20px;
        border: 1px solid #ffeeba;
    }

    .paid-card {
        background: #d1e7dd;
        color: #0f5132;
        border-radius: 15px;
        padding: 15px;
        margin-top: 20px;
        border: 1px solid #badbcc;
    }
</style>

<body>
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>

    <?php include './template/navbar.php'; ?>

    <div class="container-fluid page-header py-5 mb-5 bg-primary text-white text-center">
        <h1 class="display-3">Track Reservation</h1>
        <p class="mb-0">Enter your tracking number to view your booking details.</p>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <div class="input-group input-group-lg shadow-sm">
                    <input type="text" id="trackingNumberInput" class="form-control border-0 py-3 ps-4"
                        placeholder="Enter Tracking No. (e.g. RES123456)">
                    <button class="btn btn-primary px-4" id="trackBtn"><i class="fas fa-search me-2"></i> Track</button>
                </div>
            </div>
        </div>

        <div id="reservationDetails" style="display: none;"></div>
    </div>

    <?php include './template/script.php'; ?>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Hide Spinner
        window.addEventListener('load', () => {
            const spinner = document.getElementById('spinner');
            if (spinner) spinner.classList.remove('show');
        });

        document.getElementById('trackBtn').addEventListener('click', function () {
            const trackingNumber = document.getElementById('trackingNumberInput').value.trim();
            const container = document.getElementById('reservationDetails');

            if (trackingNumber === '') {
                Swal.fire({ icon: 'warning', title: 'Oops...', text: 'Please enter a tracking number!' });
                return;
            }

            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Searching...</p></div>';
            container.style.display = 'block';

            fetch('track_reservation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tracking_number: trackingNumber })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const r = data.reservation;

                        let servicesList = r.services.length ? r.services.map(s => `<div class="d-flex justify-content-between small mb-1"><span>${s.name}</span><span>₱${s.price}</span></div>`).join('') : '<small class="text-muted">None</small>';
                        let rentalsList = r.rentals.length ? r.rentals.map(x => `<div class="d-flex justify-content-between small mb-1"><span>${x.name}</span><span>₱${x.price}</span></div>`).join('') : '<small class="text-muted">None</small>';
                        let boatsList = r.boats.length ? r.boats.map(b => `<div class="d-flex justify-content-between small mb-1"><span>${b.name}</span><span>₱${b.price}</span></div>`).join('') : '<small class="text-muted">None</small>';

                        let balanceVal = parseFloat(r.balance.toString().replace(/,/g, ''));
                        let balanceHtml = balanceVal > 0
                            ? `<div class="balance-card"><div class="d-flex justify-content-between align-items-center"><span><i class="fas fa-exclamation-circle me-2"></i>Balance Due</span><span class="h5 mb-0 fw-bold">₱${r.balance}</span></div></div>`
                            : `<div class="paid-card"><div class="d-flex justify-content-between align-items-center"><span><i class="fas fa-check-circle me-2"></i>Fully Paid</span><span class="h5 mb-0 fw-bold">₱0.00</span></div></div>`;

                        let actionButtons = (r.status == 1 || r.status == 2) ? `
                        <div class="d-grid gap-2 mt-4 border-top pt-3">
                            <button class="btn btn-warning btn-sm text-white py-2" id="reschedBtn"><i class="fas fa-calendar-alt me-2"></i>Request Reschedule</button>
                            <button class="btn btn-danger btn-sm py-2" id="cancelBtn"><i class="fas fa-ban me-2"></i>Request Cancellation</button>
                        </div>` : '';

                        container.innerHTML = `
                        <div class="row g-4 justify-content-center">
                            <div class="col-lg-5">
                                <div class="tracking-card h-100">
                                    <div class="tracking-header">
                                        <h4 class="mb-0 fw-bold">${r.guest_name}</h4>
                                        <div class="status-pill">${r.status_text}</div>
                                    </div>
                                    <div class="p-4 pt-5">
                                        <div class="text-center mb-4">
                                            <img src="./../user/admin/${r.room_image}" class="img-fluid rounded-3 shadow-sm" style="max-height:180px; width:100%; object-fit:cover;">
                                            <h5 class="mt-3 fw-bold text-primary">${r.room_name}</h5>
                                            <small class="text-muted text-uppercase">${r.room_type}</small>
                                        </div>
                                        <div class="info-row"><span class="info-label">Check-In</span><span class="info-value">${r.check_in}</span></div>
                                        <div class="info-row"><span class="info-label">Check-Out</span><span class="info-value">${r.check_out}</span></div>
                                        <div class="info-row"><span class="info-label">Guests</span><span class="info-value">${r.guests} Person(s)</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="tracking-card h-100">
                                    <div class="p-4">
                                        <h5 class="fw-bold text-secondary mb-4"><i class="fas fa-receipt me-2"></i>Booking Breakdown</h5>
                                        <h6 class="text-primary border-bottom pb-2 mb-3">Inclusions</h6>
                                        <div class="mb-3"><p class="fw-bold small mb-1">Extra Services:</p>${servicesList}</div>
                                        <div class="mb-3"><p class="fw-bold small mb-1">Rentals:</p>${rentalsList}</div>
                                        <div class="mb-4"><p class="fw-bold small mb-1">Boat Activities:</p>${boatsList}</div>

                                        <h6 class="text-primary border-bottom pb-2 mb-3">Payment Summary</h6>
                                        <div class="info-row"><span class="info-label">Total Cost</span><span class="info-value fs-5">₱${r.total_price}</span></div>
                                        <div class="info-row"><span class="info-label">Option Selected</span><span class="info-value"><span class="badge bg-info text-dark">${r.payment_option}</span></span></div>
                                        <div class="info-row"><span class="info-label">Amount Paid</span><span class="info-value text-success">₱${r.amount_paid}</span></div>
                                        ${balanceHtml}
                                        
                                        <div class="text-center mt-4">
                                            <small class="text-muted d-block mb-2">Ref No: <strong>${r.reference_number || 'N/A'}</strong></small>
                                            
                                            <a href="print_receipt.php?tracking=${r.tracking_number}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                                                <i class="fas fa-print me-2"></i>Print Details
                                            </a>
                                        </div>
                                        ${actionButtons}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                        // Attach Event Listeners for Resched & Cancel
                        if (document.getElementById("reschedBtn")) {
                            document.getElementById("reschedBtn").addEventListener("click", () => {
                                Swal.fire({
                                    title: "Request Reschedule",
                                    html: `
                                    <div class="text-start">
                                        <label class="form-label small fw-bold">New Check-In</label>
                                        <input type="date" id="newCheckIn" class="form-control mb-2" min="${new Date().toISOString().split('T')[0]}">
                                        <label class="form-label small fw-bold">New Check-Out</label>
                                        <input type="date" id="newCheckOut" class="form-control" min="${new Date().toISOString().split('T')[0]}">
                                    </div>`,
                                    showCancelButton: true,
                                    confirmButtonText: "Submit Request",
                                    preConfirm: () => {
                                        const inDate = document.getElementById("newCheckIn").value;
                                        const outDate = document.getElementById("newCheckOut").value;
                                        if (!inDate || !outDate) Swal.showValidationMessage("Please select both dates");
                                        return { newCheckIn: inDate, newCheckOut: outDate };
                                    }
                                }).then(result => {
                                    if (result.isConfirmed) updateStatus(trackingNumber, 8, result.value);
                                });
                            });
                        }

                        if (document.getElementById("cancelBtn")) {
                            document.getElementById("cancelBtn").addEventListener("click", () => {
                                Swal.fire({
                                    title: "Request Cancellation?",
                                    text: "This will be sent to admin for approval.",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#dc3545",
                                    confirmButtonText: "Yes, Cancel it"
                                }).then(result => {
                                    if (result.isConfirmed) updateStatus(trackingNumber, 7);
                                });
                            });
                        }
                    } else {
                        container.innerHTML = `<div class="text-center py-5"><h4 class="text-secondary">Reservation Not Found</h4><p class="text-muted">We couldn't find any booking with tracking number <strong>${trackingNumber}</strong>.</p></div>`;
                    }
                })
                .catch(err => {
                    container.innerHTML = `<div class="alert alert-danger text-center"><h4>Error</h4><p>Something went wrong. Please try again.</p></div>`;
                });
        });

        function updateStatus(tracking, status, dates = null) {
            Swal.fire({ title: 'Processing...', didOpen: () => Swal.showLoading() });

            fetch('update_reservation_request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tracking_number: tracking, status: status, dates: dates })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success', 'Request submitted successfully.', 'success').then(() => document.getElementById('trackBtn').click());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Request failed.', 'error'));
        }
    </script>
</body>