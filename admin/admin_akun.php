<?php
session_start();
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}
require '../config.php';

try {
    // Menambahkan Admin
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
        $npm = $_POST['npm'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $pdo->prepare("INSERT INTO users (npm, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$npm, $password, $role]);

        $success_admin = "Admin berhasil ditambahkan!";
    }

    // Menambahkan Mahasiswa
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_mahasiswa'])) {
        $npm = $_POST['npm_mhs'];
        $email = $_POST['email_mhs'];
        $namalengkap = $_POST['namalengkap_mhs'];
        $password = password_hash($_POST['password_mhs'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO mahasiswa (npm, email, password, nama_lengkap) VALUES (?, ?, ?, ?)");
        $stmt->execute([$npm, $email, $password, $namalengkap]);

        $success_mahasiswa = "Mahasiswa berhasil ditambahkan!";
    }

    // Proses verifikasi atau penolakan mahasiswa
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi'], $_POST['npm'])) {
        $aksi = ($_POST['aksi'] == 'verifikasi') ? 'Diverifikasi' : 'Ditolak';
        $pesan = $_POST['pesan_informasi'] ?? null;

        if ($aksi == 'Ditolak' && empty($pesan)) {
            echo "Alasan penolakan harus diisi.";
            exit;
        }

        $stmt = $pdo->prepare("UPDATE mahasiswa SET status_verifikasi = ?, pesan_informasi = ? WHERE npm = ?");
        $success = $stmt->execute([$aksi, $pesan, $_POST['npm']]);

        if ($success) {
            $success_verifikasi = "Status verifikasi berhasil diupdate!";
        }
    }

    // Menghapus Admin atau Mahasiswa
    if (isset($_GET['delete']) && isset($_GET['type'])) {
        $npm = $_GET['delete'];
        $type = $_GET['type'];

        if ($type == 'admin') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE npm = ?");
            $stmt->execute([$npm]);
            $success_delete = "Admin berhasil dihapus.";
        } elseif ($type == 'mahasiswa') {
            // Ambil informasi file KRS sebelum menghapus data mahasiswa
            $stmt = $pdo->prepare("SELECT krs FROM mahasiswa WHERE npm = ?");
            $stmt->execute([$npm]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && !empty($result['krs'])) {
                $krsFile = '../uploads/krs/' . $result['krs'];
                if (file_exists($krsFile)) {
                    // Verifikasi bahwa file adalah PDF sebelum menghapus
                    $fileInfo = pathinfo($krsFile);
                    if (strtolower($fileInfo['extension']) === 'pdf') {
                        if (unlink($krsFile)) {
                            // File berhasil dihapus, lanjutkan dengan menghapus data mahasiswa
                            $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE npm = ?");
                            $stmt->execute([$npm]);
                            $success_delete = "Mahasiswa dan file KRS berhasil dihapus.";
                        } else {
                            // Gagal menghapus file
                            throw new Exception("Gagal menghapus file KRS.");
                        }
                    } else {
                        // File bukan PDF, hanya hapus data mahasiswa
                        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE npm = ?");
                        $stmt->execute([$npm]);
                        $success_delete = "Mahasiswa berhasil dihapus. File KRS bukan PDF.";
                    }
                } else {
                    // File tidak ditemukan, hanya hapus data mahasiswa
                    $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE npm = ?");
                    $stmt->execute([$npm]);
                    $success_delete = "Mahasiswa berhasil dihapus. File KRS tidak ditemukan.";
                }
            } else {
                // Tidak ada file KRS, hanya hapus data mahasiswa
                $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE npm = ?");
                $stmt->execute([$npm]);
                $success_delete = "Mahasiswa berhasil dihapus.";
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_super_admin'])) {
        try {
            $current_npm = $_SESSION['npm'];
            $new_npm = $_POST['new_npm'];
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE npm = ? AND role = 'super_admin'");
            $stmt->execute([$current_npm]);
            $user = $stmt->fetch();

            if ($user && password_verify($_POST['current_password'], $user['password'])) {
                // Update credentials
                $stmt = $pdo->prepare("UPDATE users SET npm = ?, password = ? WHERE npm = ? AND role = 'super_admin'");
                $stmt->execute([$new_npm, $new_password, $current_npm]);

                // Update session
                $_SESSION['npm'] = $new_npm;
                $success_update = "Kredensial Super Admin berhasil diperbarui!";

                // Redirect to re-login if credentials were changed
                header("Location: ../logout.php");
                exit;
            } else {
                $error_update = "Password saat ini tidak valid!";
            }
        } catch (PDOException $e) {
            $error_update = "Gagal memperbarui kredensial: " . $e->getMessage();
        }
    }

    // Pagination configuration
    $items_per_page = 25;
    $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Get total number of students
    $stmt = $pdo->query("SELECT COUNT(*) FROM mahasiswa");
    $total_students = $stmt->fetchColumn();
    $total_pages = ceil($total_students / $items_per_page);

    // Mendapatkan daftar admin
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role != 'super_admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mendapatkan mahasiswa yang belum diverifikasi
    $stmt = $pdo->query("SELECT * FROM mahasiswa WHERE status_verifikasi = 'Belum diverifikasi'");
    $mahasiswaBelumVerifikasi = $stmt->fetchAll();

    // Mendapatkan daftar mahasiswa dengan pagination
    $stmtAll = $pdo->prepare("SELECT * FROM mahasiswa LIMIT ? OFFSET ?");
    $stmtAll->bindValue(1, $items_per_page, PDO::PARAM_INT);
    $stmtAll->bindValue(2, $offset, PDO::PARAM_INT);
    $stmtAll->execute();
    $mahasiswaAll = $stmtAll->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<style>
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

    /* Modal styling */
    .modal-content {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), #224abe);
        color: white;
        border-radius: 0.5rem 0.5rem 0 0;
        padding: 1rem 1.5rem;
    }

    .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-close {
        background-color: white;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        font-weight: 500;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 2px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 0.75rem 1rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .password-field {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--secondary-color);
        cursor: pointer;
        padding: 0;
        transition: color 0.2s;
    }

    .toggle-password:hover {
        color: var(--dark-color);
    }

    .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 0.75rem 1rem;
        background-position: right 1rem center;
        transition: all 0.2s;
    }

    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        background-color: #f8f9fc;
        border-top: 1px solid #e3e6f0;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        background-color: #2e59d9;
        transform: translateY(-1px);
    }

    .error-message {
        color: var(--danger-color);
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }

    .input-error {
        border-color: var(--danger-color) !important;
    }

    /* Animation */
    @keyframes modalFade {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal.show .modal-dialog {
        animation: modalFade 0.3s ease-out;
    }

    .pagination {
        margin-bottom: 0;
    }

    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        color: var(--primary-color);
        background-color: #fff;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
    }

    .pagination .page-link:hover {
        color: #fff;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .pagination .page-item.disabled .page-link {
        color: var(--secondary-color);
        background-color: #fff;
        border-color: #dee2e6;
    }

    .pagination .fas {
        font-size: 0.875rem;
    }
</style>

<body>
    <?php include '../komponen/navbar.php'; ?>
    <div class="page-header">
        <div class="container">
            <h1 class="text-center mb-0"><i class="fas fa-users-cog me-2"></i>Dashboard Super Admin</h1>
            <p class="text-center mt-2 mb-0">Kelola akun admin dan mahasiswa</p>
        </div>
    </div>

    <div class="container fade-in">
        <!-- Notifikasi -->
        <?php if (isset($success_admin) || isset($success_mahasiswa) || isset($success_delete)) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php
                echo isset($success_admin) ? $success_admin : (isset($success_mahasiswa) ? $success_mahasiswa : $success_delete);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="fas fa-user-plus me-2"></i>Tambah Admin
                </button>
                <button class="btn btn-info text-white ms-2" data-bs-toggle="modal"
                    data-bs-target="#updateSuperAdminModal">
                    <i class="fas fa-user-shield me-2"></i>Update Kredensial Super Admin
                </button>
            </div>
        </div>


        <!-- Card Verifikasi Mahasiswa -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Verifikasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>NPM</th>
                                <th>Nama Lengkap</th>
                                <th>KRS</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mahasiswaBelumVerifikasi as $mahasiswa): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mahasiswa['npm']); ?></td>
                                    <td><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></td>
                                    <td><a href="../uploads/krs/<?php echo htmlspecialchars($mahasiswa['krs']); ?>"
                                            target="_blank">Lihat KRS</a></td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="npm"
                                                value="<?php echo htmlspecialchars($mahasiswa['npm']); ?>">
                                            <button type="submit" name="aksi" value="verifikasi"
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-check me-1"></i>Verifikasi
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modalTolak-<?php echo htmlspecialchars($mahasiswa['npm']); ?>">
                                                <i class="fas fa-times me-1"></i>Tolak
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daftar Admin Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Daftar Admin</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>NPM</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin) { ?>
                                <tr>
                                    <td><?php echo $admin['npm']; ?></td>
                                    <td><?php echo $admin['role']; ?></td>
                                    <td>
                                        <a href="admin_edit.php?edit=<?php echo $admin['npm']; ?>&type=admin"
                                            class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="?delete=<?php echo $admin['npm']; ?>&type=admin"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus akun ini?');">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daftar Mahasiswa Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Daftar Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>NPM</th>
                                <th>Email</th>
                                <th>Nama Lengkap</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mahasiswaAll)): ?>
                                <?php foreach ($mahasiswaAll as $mhs): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mhs['npm']); ?></td>
                                        <td><?php echo htmlspecialchars($mhs['email']); ?></td>
                                        <td><?php echo htmlspecialchars($mhs['nama_lengkap']); ?></td>
                                        <td>
                                            <a href="admin_edit.php?edit=<?php echo htmlspecialchars($mhs['npm']); ?>&type=mahasiswa"
                                                class="btn btn-warning btn-sm">Edit</a>
                                            <a href="?delete=<?php echo htmlspecialchars($mhs['npm']); ?>&type=mahasiswa"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin menghapus akun ini?');">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data mahasiswa.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Navigation -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- First Page -->
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=1" <?php echo ($current_page <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>

                            <!-- Previous Page -->
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" <?php echo ($current_page <= 1) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            if ($end_page - $start_page < 4) {
                                $start_page = max(1, $end_page - 4);
                            }

                            for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" <?php echo ($current_page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>

                            <!-- Last Page -->
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>" <?php echo ($current_page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <!-- Page Information -->
                    <div class="text-center text-muted mt-2">
                        Showing page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                        (<?php echo $total_students; ?> total records)
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Admin yang diperbarui -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i>
                        Tambah Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="adminForm" onsubmit="return validateForm()">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">ID Admin</label>
                            <input type="text" class="form-control" name="npm" id="adminId" required>
                            <span class="error-message" id="adminIdError">ID Admin harus diisi</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" name="password" id="password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span class="error-message" id="passwordError">Password minimal 6
                                karakter</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="role">
                                <option value="admin_pi">Admin PI</option>
                                <option value="admin_sidang">Admin Sidang</option>
                                <option value="admin_msib">Admin MSIB</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_admin" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Update Super Admin -->
    <div class="modal fade" id="updateSuperAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-shield me-2"></i>Update Kredensial Super Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" onsubmit="return validateSuperAdminForm()">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">Username Baru</label>
                            <input type="text" class="form-control" name="new_npm" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <div class="password-field">
                                <input type="password" class="form-control" name="current_password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Password Baru</label>
                            <div class="password-field">
                                <input type="password" class="form-control" name="new_password" id="newPassword"
                                    required>
                                <button type="button" class="toggle-password" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_super_admin" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tolak untuk setiap mahasiswa -->
    <?php foreach ($mahasiswaBelumVerifikasi as $mahasiswa): ?>
        <div class="modal fade" id="modalTolak-<?php echo htmlspecialchars($mahasiswa['npm']); ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-exclamation-circle me-2"></i>Alasan Penolakan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" name="npm" value="<?php echo htmlspecialchars($mahasiswa['npm']); ?>">
                            <div class="form-group">
                                <label class="form-label">Alasan Penolakan:</label>
                                <textarea name="pesan_informasi" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="aksi" value="tolak" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i>Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
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

        // Show error for an input field
        function showError(input, errorId) {
            input.classList.add('input-error');
            document.getElementById(errorId).style.display = 'block';
        }

        // Reset all error states
        function resetErrors() {
            const inputs = document.querySelectorAll('.form-control');
            const errorMessages = document.querySelectorAll('.error-message');

            inputs.forEach(input => input.classList.remove('input-error'));
            errorMessages.forEach(error => error.style.display = 'none');
        }

        // Reset form when modal is closed
        document.getElementById('addAdminModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('adminForm').reset();
            resetErrors();
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });

        function validateSuperAdminForm() {
            const newPassword = document.getElementById('newPassword').value;
            if (newPassword.length < 6) {
                alert('Password baru harus minimal 6 karakter!');
                return false;
            }
            return confirm('Anda yakin ingin memperbarui kredensial? Anda akan diminta login ulang.');
        }

        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }, 5000);
            });
        });
    </script>
</body>

</html>