<?php
session_start();

if (!isset($_SESSION['npm']) || ($_SESSION['role'] != 'admin_pi' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../login.php");
    exit;
}

require '../config.php';

// Proses untuk menambah atau memperbarui status PI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_or_update'])) {
    $npm = $_POST['npm'];
    $nama = $_POST['nama'];
    $status_pi = $_POST['status'];

    // Cek apakah NPM mahasiswa ada di tabel mahasiswa
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE npm = ?");
    $stmt->execute([$npm]);
    $mahasiswa = $stmt->fetch();

    if ($mahasiswa) {
        // Jika mahasiswa ada, lanjutkan dengan proses update atau insert di tabel penelitian
        $stmt = $pdo->prepare("SELECT * FROM penelitian WHERE npm = ?");
        $stmt->execute([$npm]);
        $penelitian = $stmt->fetch();

        if ($penelitian) {
            // Update status PI jika NPM sudah ada
            $stmt = $pdo->prepare("UPDATE penelitian SET status = ? WHERE npm = ?");
            $stmt->execute([$status_pi, $npm]);
            $success = "Status PI berhasil diperbarui!";
        } else {
            // Tambahkan data PI baru
            $stmt = $pdo->prepare("INSERT INTO penelitian (npm, nama, status) VALUES (?, ?, ?)");
            $stmt->execute([$npm, $nama, $status_pi]);
            $success = "Data mahasiswa berhasil ditambahkan!";
        }
    } else {
        // Jika mahasiswa tidak ditemukan, tampilkan pesan error
        $error = "NPM mahasiswa tidak ditemukan di database. Pastikan data mahasiswa sudah terdaftar.";
    }
}

// Proses untuk menghapus data PI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $npm = $_POST['npm'];

    $stmt = $pdo->prepare("DELETE FROM penelitian WHERE npm = ?");
    $stmt->execute([$npm]);

    $success = "Data mahasiswa berhasil dihapus!";
}

// Proses Pencarian
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $pdo->prepare("SELECT * FROM penelitian WHERE npm LIKE ? OR nama LIKE ?");
    $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
} else {
    // Mendapatkan semua data mahasiswa untuk ditampilkan di tabel jika tidak ada pencarian
    $stmt = $pdo->prepare("SELECT * FROM penelitian");
    $stmt->execute();
}

// Function to get count of students with "Diproses" status
function getUnprocessedCount($pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM penelitian WHERE status = 'Diproses'");
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Get the count
$unprocessedCount = getUnprocessedCount($pdo);

$pi_list = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Penelitian Ilmiah</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Shared Admin Panel Styles */
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
        }

        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fc;
            color: var(--dark-color);
            font-weight: bold;
            border-top: none;
        }

        .table td {
            vertical-align: middle;
        }

        .btn {
            border-radius: 0.35rem;
            padding: 0.375rem 0.75rem;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .search-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-box .form-control {
            padding-left: 2.5rem;
            border-radius: 0.35rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-verified {
            background-color: var(--success-color);
            color: white;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }

        .status-rejected {
            background-color: var(--danger-color);
            color: white;
        }

        .modal-content {
            border: none;
            border-radius: 0.5rem;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .alert {
            border-radius: 0.35rem;
            border: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            margin-left: 10px;
            font-size: 1.2rem;
            color: #4e73df;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #e74a3b;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
        }

        .filter-notification-container {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-header .modal-title i {
            margin-right: 0.5rem;
        }

        #piForm .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        #piForm .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        #piForm .form-control,
        #piForm .form-select {
            border: 2px solid #e3e6f0;
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }

        #piForm .form-control:focus,
        #piForm .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        #piForm .input-error {
            border-color: var(--danger-color) !important;
        }

        #piForm .error-message {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        /* Animation for PI Modal */
        @keyframes modalPIFade {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #addPIModal.show .modal-dialog {
            animation: modalPIFade 0.3s ease-out;
        }
    </style>
</head>

<body>

    <?php include '../komponen/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="text-center mb-0"><i class="fas fa-graduation-cap me-2"></i>Admin Penelitian Ilmiah</h1>
            <p class="text-center mt-2 mb-0">Kelola status penelitian ilmiah mahasiswa</p>
        </div>
    </div>

    <div class="container fade-in">
        <!-- Notifications -->
        <?php if (isset($success)) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <!-- Add Button -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPIModal">
                    <i class="fas fa-plus me-2"></i>Tambah Data PI
                </button>

                <!-- Notification Icon -->
                <div class="notification-icon" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <i class="fas fa-bell fa-fw"></i>
                    <?php if ($unprocessedCount > 0): ?>
                        <span class="notification-badge"><?php echo $unprocessedCount; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Search Box -->
            <div class="search-box" style="width: 300px;">
                <i class="fas fa-search"></i>
                <form method="GET" action="">
                    <input type="text" name="search" class="form-control" placeholder="Cari NPM atau Nama..."
                        value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </form>
            </div>
        </div>


        <!-- Add this at the bottom of your body tag -->
        <div class="modal fade" id="notificationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-bell me-2"></i>Notifikasi Status PI
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($unprocessedCount > 0): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Terdapat <strong><?php echo $unprocessedCount; ?></strong> mahasiswa yang masih dalam status
                                "Diproses"
                            </div>
                            <div class="notification-list">
                                <?php
                                $stmt = $pdo->prepare("SELECT npm, nama FROM penelitian WHERE status = 'Diproses'");
                                $stmt->execute();
                                $unprocessedStudents = $stmt->fetchAll();

                                foreach ($unprocessedStudents as $student):
                                    ?>
                                    <div class="notification-item">
                                        <i class="fas fa-user-clock me-2"></i>
                                        <?php echo $student['nama']; ?> (<?php echo $student['npm']; ?>)
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Tidak ada mahasiswa yang sedang dalam status "Diproses"
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Menentukan filter berdasarkan status
        $filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

        // Filter data berdasarkan status
        $filtered_pi_list = array_filter($pi_list, function ($pi) use ($filter_status) {
            return $filter_status ? $pi['status'] === $filter_status : true;
        });
        ?>

        <!-- Main Content Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Daftar Mahasiswa dan Status PI</h5>

                <!-- Filter Status Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="filterStatusDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Filter Status PI
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filterStatusDropdown">
                        <li><a class="dropdown-item" href="?">Semua Status</a></li>
                        <li><a class="dropdown-item" href="?filter_status=Diproses">Diproses</a></li>
                        <li><a class="dropdown-item" href="?filter_status=Menuju Penandatanganan">Menuju
                                Penandatanganan</a></li>
                        <li><a class="dropdown-item" href="?filter_status=Selesai">Selesai</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>NPM</th>
                                <th>Nama</th>
                                <th>Status PI</th>
                                <th>Update Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filtered_pi_list)) { ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data dengan status yang dipilih.</td>
                                </tr>
                            <?php } else { ?>
                                <?php foreach ($filtered_pi_list as $pi) { ?>
                                    <tr>
                                        <td><?php echo $pi['npm']; ?></td>
                                        <td><?php echo $pi['nama']; ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                            echo $pi['status'] == 'Selesai' ? 'status-verified' : ($pi['status'] == 'Diproses' ? 'status-pending' : 'status-warning');
                                            ?>">
                                                <?php echo $pi['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-flex gap-2">
                                                <input type="hidden" name="npm" value="<?php echo $pi['npm']; ?>">
                                                <input type="hidden" name="nama" value="<?php echo $pi['nama']; ?>">
                                                <select name="status" class="form-select">
                                                    <option value="Diproses" <?php if ($pi['status'] == 'Diproses')
                                                        echo 'selected'; ?>>Diproses</option>
                                                    <option value="Menuju Penandatanganan" <?php if ($pi['status'] == 'Menuju Penandatanganan')
                                                        echo 'selected'; ?>>Menuju Penandatanganan</option>
                                                    <option value="Selesai" <?php if ($pi['status'] == 'Selesai')
                                                        echo 'selected'; ?>>Selesai</option>
                                                </select>
                                                <button type="submit" name="add_or_update" class="btn btn-success">
                                                    <i class="fas fa-check me-1"></i>Update
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="npm" value="<?php echo $pi['npm']; ?>">
                                                <button type="submit" name="delete" class="btn btn-danger"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash me-1"></i>Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- Add PI Modal -->
        <div class="modal fade" id="addPIModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle"></i>
                            Tambah Data PI
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" id="piForm" onsubmit="return validatePIForm()">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">NPM</label>
                                <input type="text" class="form-control" name="npm" id="npmPI" required>
                                <span class="error-message" id="npmPIError">NPM harus diisi</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" name="nama" id="namaPI" required>
                                <span class="error-message" id="namaPIError">Nama harus diisi</span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status Penelitian Ilmiah</label>
                                <select class="form-select" name="status" id="statusPI" required>
                                    <option value="Diproses">Diproses</option>
                                    <option value="Menuju Penandatanganan">Menuju Penandatanganan</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="add_or_update" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <script src="../assets/js/bootstrap.bundle.min.js"></script>
        <script>
            // Form validation for Admin Form
            function validateForm() {
                let isValid = true;
                const adminId = document.getElementById('adminId');
                const password = document.getElementById('password');

                // Reset error states
                resetErrors();

                // Validate Admin ID
                if (!adminId.value.trim()) {
                    showError(adminId, 'adminIdError');
                    isValid = false;
                }

                // Validate Password
                if (password.value.length < 6) {
                    showError(password, 'passwordError');
                    isValid = false;
                }

                return isValid;
            }

            // Form validation for PI Form
            function validatePIForm() {
                let isValid = true;
                const npmPI = document.getElementById('npmPI');
                const namaPI = document.getElementById('namaPI');

                // Reset error states
                resetPIErrors();

                // Validate NPM
                if (!npmPI.value.trim()) {
                    showPIError(npmPI, 'npmPIError');
                    isValid = false;
                }

                // Validate Nama
                if (!namaPI.value.trim()) {
                    showPIError(namaPI, 'namaPIError');
                    isValid = false;
                }

                return isValid;
            }

            // Toggle password visibility
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const toggleButton = document.querySelector('.toggle-password i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleButton.classList.remove('fa-eye');
                    toggleButton.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleButton.classList.remove('fa-eye-slash');
                    toggleButton.classList.add('fa-eye');
                }
            }

            // Show error for an admin input field
            function showError(input, errorId) {
                input.classList.add('input-error');
                document.getElementById(errorId).style.display = 'block';
            }

            // Show error for a PI input field
            function showPIError(input, errorId) {
                input.classList.add('input-error');
                document.getElementById(errorId).style.display = 'block';
            }

            // Reset all admin error states
            function resetErrors() {
                const inputs = document.querySelectorAll('#adminForm .form-control');
                const errorMessages = document.querySelectorAll('#adminForm .error-message');

                inputs.forEach(input => input.classList.remove('input-error'));
                errorMessages.forEach(error => error.style.display = 'none');
            }

            // Reset all PI error states
            function resetPIErrors() {
                const inputs = document.querySelectorAll('#piForm .form-control');
                const errorMessages = document.querySelectorAll('#piForm .error-message');

                inputs.forEach(input => input.classList.remove('input-error'));
                errorMessages.forEach(error => error.style.display = 'none');
            }

            // Event Listeners
            document.addEventListener('DOMContentLoaded', function () {
                // Reset admin form when modal is closed
                const addAdminModal = document.getElementById('addAdminModal');
                if (addAdminModal) {
                    addAdminModal.addEventListener('hidden.bs.modal', function () {
                        document.getElementById('adminForm').reset();
                        resetErrors();
                    });
                }

                // Reset PI form when modal is closed
                const addPIModal = document.getElementById('addPIModal');
                if (addPIModal) {
                    addPIModal.addEventListener('hidden.bs.modal', function () {
                        document.getElementById('piForm').reset();
                        resetPIErrors();
                    });
                }

                // Auto-hide alerts after 5 seconds
                setTimeout(function () {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(function (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });

            // Filter functionality for PI status
            function filterStatus(status) {
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const statusCell = row.querySelector('td:nth-child(3)');
                    if (status === 'all' || (statusCell && statusCell.textContent.trim() === status)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Search functionality
            function searchTable() {
                const searchInput = document.querySelector('input[name="search"]');
                const filter = searchInput.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const npmCell = row.querySelector('td:nth-child(1)');
                    const namaCell = row.querySelector('td:nth-child(2)');

                    if (npmCell && namaCell) {
                        const npm = npmCell.textContent.toLowerCase();
                        const nama = namaCell.textContent.toLowerCase();

                        if (npm.includes(filter) || nama.includes(filter)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            }

            // Confirmation dialog for delete actions
            function confirmDelete(type, npm) {
                const message = type === 'admin'
                    ? 'Apakah Anda yakin ingin menghapus admin ini?'
                    : 'Apakah Anda yakin ingin menghapus data PI ini?';

                return confirm(message);
            }

            // Format NPM input (optional feature)
            function formatNPM(input) {
                // Remove any non-digit characters
                let value = input.value.replace(/\D/g, '');

                // Limit to maximum length (adjust as needed)
                if (value.length > 8) {
                    value = value.substr(0, 8);
                }

                input.value = value;
            }

            // Validate numeric input
            function validateNumericInput(event) {
                // Allow: backspace, delete, tab, escape, enter
                if ([46, 8, 9, 27, 13].indexOf(event.keyCode) !== -1 ||
                    // Allow: Ctrl+A, Ctrl+C, Ctrl+V
                    (event.keyCode === 65 && event.ctrlKey === true) ||
                    (event.keyCode === 67 && event.ctrlKey === true) ||
                    (event.keyCode === 86 && event.ctrlKey === true) ||
                    // Allow: home, end, left, right
                    (event.keyCode >= 35 && event.keyCode <= 39)) {
                    return;
                }
                // Ensure that it is a number and stop the keypress if not
                if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) &&
                    (event.keyCode < 96 || event.keyCode > 105)) {
                    event.preventDefault();
                }
            }

            // Initialize tooltips and popovers if using Bootstrap
            function initializeBootstrapComponents() {
                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Initialize popovers
                const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                });
            }

            // Initialize all components when the document is ready
            document.addEventListener('DOMContentLoaded', function () {
                initializeBootstrapComponents();

                // Add event listeners for NPM input fields
                const npmInputs = document.querySelectorAll('input[name="npm"]');
                npmInputs.forEach(input => {
                    input.addEventListener('input', function () {
                        formatNPM(this);
                    });
                    input.addEventListener('keydown', validateNumericInput);
                });
            });
        </script>
</body>

</html>