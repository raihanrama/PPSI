<?php
session_start();

if (!isset($_SESSION['npm']) || ($_SESSION['role'] != 'admin_msib' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../login.php");
    exit;
}
require '../config.php';

// Function to delete files associated with MSIB entry
function deleteMsibFiles($pdo, $npm)
{
    // First, get the file paths from the database
    $stmt = $pdo->prepare("SELECT rangkuman_nilai, cv FROM msib WHERE npm = ?");
    $stmt->execute([$npm]);
    $files = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($files) {
        // Delete rangkuman nilai file if it exists
        if (!empty($files['rangkuman_nilai'])) {
            $rangkumanPath = "../uploads/msib/" . $files['rangkuman_nilai'];
            if (file_exists($rangkumanPath)) {
                unlink($rangkumanPath);
            }
        }

        // Delete CV file if it exists
        if (!empty($files['cv'])) {
            $cvPath = "../uploads/msib/" . $files['cv'];
            if (file_exists($cvPath)) {
                unlink($cvPath);
            }
        }
    }
}

// Proses update status MSIB tanpa unggah file surat, hanya dengan link Google Drive
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $npm = $_POST['npm'];
    $status = $_POST['status'];
    $link_gdrive = $_POST['link_gdrive'];
    $pesan_informasi = $status == 'ditolak' ? $_POST['pesan_informasi'] : null;

    $stmt = $pdo->prepare("UPDATE msib SET status = ?, surat = ?, pesan_informasi = ? WHERE npm = ?");
    $stmt->execute([$status, $link_gdrive, $pesan_informasi, $npm]);

    $success = "Status, link surat rekomendasi, dan pesan informasi berhasil diperbarui.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $npm = $_POST['npm'];

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Delete the files first
        deleteMsibFiles($pdo, $npm);

        // Then delete the database record
        $stmt = $pdo->prepare("DELETE FROM msib WHERE npm = ?");
        $stmt->execute([$npm]);

        // Commit the transaction
        $pdo->commit();

        $success = "Data pengajuan MSIB dan dokumen terkait berhasil dihapus.";
    } catch (Exception $e) {
        // Rollback in case of error
        $pdo->rollBack();
        $error = "Terjadi kesalahan saat menghapus data: " . $e->getMessage();
    }
}

// Function to get unprocessed count
function getUnprocessedCount($pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM msib WHERE status = 'Belum Diverifikasi'");
    $stmt->execute();
    return $stmt->fetchColumn();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// Base query
$query = "SELECT msib.npm, mahasiswa.nama_lengkap, msib.status, msib.rangkuman_nilai, msib.cv, msib.jenis_kegiatan, msib.nama_mitra, msib.posisi, msib.surat, msib.pesan_informasi 
          FROM msib 
          JOIN mahasiswa ON msib.npm = mahasiswa.npm 
          WHERE 1=1";

$params = [];

// Add search condition if search term exists
if ($search) {
    $query .= " AND (msib.npm LIKE ? OR mahasiswa.nama_lengkap LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

// Add filter status condition if filter exists
if ($filter_status) {
    $query .= " AND msib.status = ?";
    $params[] = $filter_status;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$msib_list = $stmt->fetchAll();

// Get unprocessed count for notifications
$unprocessedCount = getUnprocessedCount($pdo);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin MSIB</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .status-diverifikasi {
            background-color: var(--success-color);
            color: white;
        }

        .status-belum-diverifikasi {
            background-color: var(--warning-color);
            color: white;
        }

        .status-ditolak {
            background-color: var(--danger-color);
            color: white;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

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

        .modal-content {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #224abe);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-header .btn-close {
            color: white;
            opacity: 1;
            text-shadow: none;
            transition: transform 0.2s;
        }

        .modal-header .btn-close:hover {
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 2rem;
            background-color: #f8f9fc;
        }

        .modal-footer {
            border-top: none;
            padding: 1.5rem;
            background-color: #f8f9fc;
            gap: 1rem;
        }

        /* Status Buttons */
        .status-buttons {
            margin-top: 0.5rem;
        }

        .status-btn {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .status-btn.active {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        }

        /* Template Container */
        .template-container {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            border: 1px solid #e3e6f0;
        }

        .template-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .template-btn {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e3e6f0;
            background-color: white;
            color: var(--primary-color);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .template-btn:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        }

        .template-btn.active {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.15);
        }

        #modalPesanInformasi {
            border: 1px solid #e3e6f0;
            border-radius: 0.75rem;
            padding: 1rem;
            min-height: 120px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        #modalPesanInformasi:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
            outline: none;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal.fade .modal-dialog {
            animation: modalFadeIn 0.3s ease-out;
        }
    </style>
</head>

<body>
    <?php include '../komponen/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="text-center mb-0"><i class="fas fa-briefcase me-2"></i>Admin MSIB</h1>
            <p class="text-center mt-2 mb-0">Kelola pengajuan dan status MSIB mahasiswa</p>
        </div>
    </div>

    <div class="container fade-in">
        <?php if (isset($success)) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <!-- Search and Filter Container -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="filterStatusDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Filter Status
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterStatusDropdown">
                    <li><a class="dropdown-item" href="?">Semua Status</a></li>
                    <li><a class="dropdown-item" href="?filter_status=Diverifikasi">Diverifikasi</a></li>
                    <li><a class="dropdown-item" href="?filter_status=Belum diverifikasi">Belum Diverifikasi</a></li>
                    <li><a class="dropdown-item" href="?filter_status=ditolak">Ditolak</a></li>
                </ul>
            </div>

            <div class="d-flex align-items-center">
                <div class="search-box" style="width: 300px;">
                    <i class="fas fa-search"></i>
                    <form method="GET" action="">
                        <input type="text" name="search" class="form-control" placeholder="Cari NPM atau Nama..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
                <div class="notification-icon ms-3" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <i class="fas fa-bell"></i>
                    <?php if ($unprocessedCount > 0): ?>
                        <span class="notification-badge"><?php echo $unprocessedCount; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <?php

        // Ambil nilai filter status dari URL
        $filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

        // Filter Pencarian dan Status
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        $query = "SELECT msib.npm, mahasiswa.nama_lengkap, msib.status, msib.rangkuman_nilai, msib.cv, msib.jenis_kegiatan, msib.nama_mitra, msib.posisi, msib.surat, msib.pesan_informasi 
          FROM msib 
          JOIN mahasiswa ON msib.npm = mahasiswa.npm 
          WHERE (msib.npm LIKE ? OR mahasiswa.nama_lengkap LIKE ?)";

        // Tambahkan kondisi filter status jika dipilih
        if (!empty($filter_status)) {
            $query .= " AND msib.status = ?";
            $params = ['%' . $search . '%', '%' . $search . '%', $filter_status];
        } else {
            $params = ['%' . $search . '%', '%' . $search . '%'];
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $msib_list = $stmt->fetchAll();
        ?>



        <!-- Main Content Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Daftar Pengajuan MSIB</h5>
                <!-- Filter Status -->
                <div class="d-flex justify-content-between mb-3">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="filterStatusDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Filter Status
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterStatusDropdown">
                            <li><a class="dropdown-item" href="?filter_status=">Semua Status</a></li>
                            <li><a class="dropdown-item" href="?filter_status=Diverifikasi">Diverifikasi</a></li>
                            <li><a class="dropdown-item" href="?filter_status=Belum diverifikasi">Belum Diverifikasi</a>
                            </li>
                            <li><a class="dropdown-item" href="?filter_status=ditolak">Ditolak</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>NPM</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kegiatan</th>
                                <th>Nama Mitra</th>
                                <th>Posisi</th>
                                <th>Status</th>
                                <th>Dokumen</th>
                                <th>Update Status & Link</th>
                                <th>Surat Rekomendasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($msib_list as $msib) { ?>
                                <tr>
                                    <td><?php echo $msib['npm']; ?></td>
                                    <td><?php echo $msib['nama_lengkap']; ?></td>
                                    <td><?php echo $msib['jenis_kegiatan']; ?></td>
                                    <td><?php echo $msib['nama_mitra']; ?></td>
                                    <td><?php echo $msib['posisi']; ?></td>

                                    <td>
                                        <span class="status-badge <?php
                                        echo $msib['status'] == 'Diverifikasi' ? 'status-diverifikasi' : ($msib['status'] == 'ditolak' ? 'status-ditolak' : 'status-belum-diverifikasi');
                                        ?>">
                                            <?php echo $msib['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="../uploads/msib/<?php echo $msib['rangkuman_nilai']; ?>"
                                                target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-alt me-1"></i>Nilai
                                            </a>
                                            <a href="../uploads/msib/<?php echo $msib['cv']; ?>" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-user me-1"></i>CV
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#updateModal" data-npm="<?php echo $msib['npm']; ?>"
                                            data-nama="<?php echo $msib['nama_lengkap']; ?>"
                                            data-status="<?php echo $msib['status']; ?>"
                                            data-link="<?php echo $msib['surat']; ?>"
                                            data-pesan="<?php echo $msib['pesan_informasi']; ?>">
                                            <i class="fas fa-edit me-1"></i>Update Status
                                        </button>
                                    </td>
                                    <td>
                                        <?php if ($msib['surat']) { ?>
                                            <a href="<?php echo $msib['surat']; ?>" target="_blank"
                                                class="btn btn-sm btn-success">
                                                <i class="fas fa-file-download me-1"></i>Download
                                            </a>
                                        <?php } else { ?>
                                            <span class="text-muted"><i class="fas fa-times me-1"></i>Belum ada</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="npm" value="<?php echo $msib['npm']; ?>">
                                            <button type="submit" name="delete" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Enhanced Modal Design -->
                                <div class="modal fade" id="updateModal" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-edit me-2"></i>Update Status MSIB
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="" id="updateMsibForm">
                                                    <input type="hidden" name="npm" id="modalNpm">

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Mahasiswa:</label>
                                                        <div class="form-control bg-light" id="modalMahasiswa"></div>
                                                    </div>

                                                    <div class="row mb-4">
                                                        <div class="col-md-12">
                                                            <label class="form-label fw-bold">Status Verifikasi</label>
                                                            <div class="d-flex gap-3 status-buttons">
                                                                <button type="button"
                                                                    class="btn btn-outline-success flex-grow-1 status-btn"
                                                                    data-status="Diverifikasi">
                                                                    <i class="fas fa-check-circle me-2"></i>Diverifikasi
                                                                </button>
                                                                <button type="button"
                                                                    class="btn btn-outline-warning flex-grow-1 status-btn"
                                                                    data-status="Belum diverifikasi">
                                                                    <i class="fas fa-clock me-2"></i>Belum Diverifikasi
                                                                </button>
                                                                <button type="button"
                                                                    class="btn btn-outline-danger flex-grow-1 status-btn"
                                                                    data-status="ditolak">
                                                                    <i class="fas fa-times-circle me-2"></i>Ditolak
                                                                </button>
                                                            </div>
                                                            <input type="hidden" name="status" id="selectedStatus">
                                                        </div>
                                                    </div>

                                                    <div class="template-container mb-4">
                                                        <label class="form-label fw-bold">Template Pesan</label>
                                                        <div class="template-buttons">
                                                            <button type="button" class="template-btn"
                                                                data-template="verifikasi">
                                                                <i class="fas fa-check"></i>Berkas Lengkap
                                                            </button>
                                                            <button type="button" class="template-btn"
                                                                data-template="revisi">
                                                                <i class="fas fa-edit"></i>Perlu Revisi
                                                            </button>
                                                            <button type="button" class="template-btn"
                                                                data-template="tolak">
                                                                <i class="fas fa-times"></i>Berkas Tidak Sesuai
                                                            </button>
                                                            <button type="button" class="template-btn"
                                                                data-template="kurang">
                                                                <i class="fas fa-exclamation-circle"></i>Berkas Kurang
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">
                                                            <i class="fas fa-pen me-2"></i>Link Google Drive Surat
                                                        </label>
                                                        <input type="text" name="link_gdrive" id="modalLinkGdrive"
                                                            class="form-control"
                                                            placeholder="Masukkan link Google Drive surat rekomendasi">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">
                                                            <i class="fas fa-comment me-2"></i>Pesan Informasi
                                                        </label>
                                                        <textarea class="form-control" id="modalPesanInformasi"
                                                            name="pesan_informasi" rows="4"
                                                            placeholder="Masukkan pesan informasi atau catatan tambahan..."></textarea>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-1"></i>Tutup
                                                        </button>
                                                        <button type="submit" name="update" class="btn btn-primary">
                                                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notification -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">
                        <i class="fas fa-bell me-2"></i>Notifikasi Status MSIB
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($unprocessedCount > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Terdapat <strong><?php echo $unprocessedCount; ?></strong> mahasiswa yang masih dalam status
                            "Belum Diverifikasi"
                        </div>
                        <div class="notification-list">
                            <?php
                            $stmt = $pdo->prepare("SELECT msib.npm, mahasiswa.nama_lengkap as nama 
                                             FROM msib 
                                             JOIN mahasiswa ON msib.npm = mahasiswa.npm 
                                             WHERE msib.status = 'Belum Diverifikasi'");
                            $stmt->execute();
                            $unprocessedStudents = $stmt->fetchAll();

                            foreach ($unprocessedStudents as $student):
                                ?>
                                <div class="notification-item p-2 border-bottom">
                                    <i class="fas fa-user-clock me-2"></i>
                                    <?php echo htmlspecialchars($student['nama']); ?>
                                    (<?php echo htmlspecialchars($student['npm']); ?>)
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Tidak ada mahasiswa yang sedang dalam status "Belum Diverifikasi"
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Template messages
            const templateMessages = {
                verifikasi: "Selamat! Berkas pengajuan MSIB Anda telah diverifikasi dan lengkap. Silakan menunggu informasi selanjutnya.",
                revisi: "Mohon untuk melakukan revisi pada berkas pengajuan MSIB Anda. Silakan periksa kembali kelengkapan dan kesesuaian dokumen yang dibutuhkan.",
                tolak: "Mohon maaf, berkas pengajuan MSIB Anda tidak dapat disetujui karena tidak memenuhi persyaratan yang ditentukan.",
                kurang: "Berkas pengajuan MSIB Anda masih kurang lengkap. Mohon lengkapi dokumen yang diperlukan sesuai dengan persyaratan."
            };

            // Initialize status buttons
            const statusBtns = document.querySelectorAll('.status-btn');
            statusBtns.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault(); // Prevent any default button behavior

                    // Remove active class from all buttons
                    statusBtns.forEach(b => b.classList.remove('active'));

                    // Add active class to clicked button
                    this.classList.add('active');

                    // Update hidden status input
                    const selectedStatus = this.getAttribute('data-status');
                    document.getElementById('selectedStatus').value = selectedStatus;
                });
            });

            // Initialize template buttons
            const templateBtns = document.querySelectorAll('.template-btn');
            templateBtns.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault(); // Prevent any default button behavior

                    // Get template message
                    const template = this.getAttribute('data-template');
                    const message = templateMessages[template];

                    // Update textarea with template message
                    document.getElementById('modalPesanInformasi').value = message;

                    // Update button states
                    templateBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Handle modal show event
            const updateModal = document.getElementById('updateModal');
            if (updateModal) {
                updateModal.addEventListener('show.bs.modal', function (event) {
                    // Get data from button that triggered the modal
                    const button = event.relatedTarget;
                    const npm = button.getAttribute('data-npm');
                    const nama = button.getAttribute('data-nama');
                    const status = button.getAttribute('data-status');
                    const link = button.getAttribute('data-link');
                    const pesan = button.getAttribute('data-pesan');

                    // Update modal form fields
                    document.getElementById('modalNpm').value = npm;
                    document.getElementById('modalMahasiswa').textContent = `${nama} (${npm})`;
                    document.getElementById('modalLinkGdrive').value = link || '';
                    document.getElementById('modalPesanInformasi').value = pesan || '';
                    document.getElementById('selectedStatus').value = status;

                    // Reset all status buttons
                    statusBtns.forEach(btn => {
                        const btnStatus = btn.getAttribute('data-status');
                        if (btnStatus === status) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                    });

                    // Reset all template buttons
                    templateBtns.forEach(btn => btn.classList.remove('active'));
                });
            }

            // Form validation before submit
            const updateForm = document.getElementById('updateMsibForm');
            if (updateForm) {
                updateForm.addEventListener('submit', function (e) {
                    const selectedStatus = document.getElementById('selectedStatus').value;

                    if (!selectedStatus) {
                        e.preventDefault();
                        alert('Silakan pilih status verifikasi terlebih dahulu.');
                        return false;
                    }

                    // If status is 'ditolak', ensure there's a message
                    if (selectedStatus === 'ditolak') {
                        const pesanInformasi = document.getElementById('modalPesanInformasi').value.trim();
                        if (!pesanInformasi) {
                            e.preventDefault();
                            alert('Mohon isi pesan informasi untuk status ditolak.');
                            return false;
                        }
                    }
                });
            }

            // Optional: Add success message fade out
            const alertSuccess = document.querySelector('.alert-success');
            if (alertSuccess) {
                setTimeout(() => {
                    alertSuccess.classList.add('fade');
                    setTimeout(() => {
                        alertSuccess.remove();
                    }, 300);
                }, 3000);
            }

            // Optional: Add animation when clicking buttons
            const allButtons = document.querySelectorAll('.btn');
            allButtons.forEach(button => {
                button.addEventListener('click', function () {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 100);
                });
            });
        });
    </script>
</body>

</html