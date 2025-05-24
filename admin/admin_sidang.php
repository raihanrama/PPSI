<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki role yang sesuai
if (!isset($_SESSION['npm']) || ($_SESSION['role'] != 'admin_sidang' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../login.php");
    exit;
}

require '../config.php';

// Proses verifikasi dan update status sidang atau kompre
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $npm = $_POST['npm'];
    $status = $_POST['status'];
    $type = $_POST['type'];
    $pesan_informasi = isset($_POST['pesan_informasi']) ? $_POST['pesan_informasi'] : '';

    // Update status dan pesan informasi berdasarkan type
    $table = ($type == 'sidang') ? 'sidang' : 'kompre';
    $stmt = $pdo->prepare("UPDATE $table SET status = ?, pesan_informasi = ? WHERE npm = ?");

    try {
        $stmt->execute([$status, $pesan_informasi, $npm]);
        if ($stmt->rowCount() > 0) {
            $success = "Status dan pesan informasi $type berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui status $type.";
        }
    } catch (PDOException $e) {
        $error = "Error database: " . $e->getMessage();
    }
}

// Proses hapus data sidang atau kompre
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $npm = $_POST['npm'];
    $type = $_POST['type']; // Type bisa 'sidang' atau 'kompre'

    try {
        // Get all file paths before deleting database record
        $table = ($type == 'sidang') ? 'sidang' : 'kompre';
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE npm = ?");
        $stmt->execute([$npm]);
        $data = $stmt->fetch();

        if ($data) {
            $uploadDir = "../uploads/$type/";

            // List of file columns
            $fileColumns = [
                'sertifikat_pi',
                'sertifikat_workshop',
                'sertifikat_kursus',
                'sertifikat_apptitude',
                'surat_krs'
            ];

            // Add additional file columns for sidang
            if ($type == 'sidang') {
                $fileColumns = array_merge($fileColumns, [
                    'acc_pembimbing_1',
                    'acc_pembimbing_2',
                    'buku_bimbingan',
                    'manual_book',
                    'draft_jurnal',
                    'poster',
                ]);
            }

            // Delete each file if it exists
            foreach ($fileColumns as $column) {
                if (!empty($data[$column]) && file_exists($uploadDir . $data[$column])) {
                    unlink($uploadDir . $data[$column]);
                }
            }

            // Delete database record
            $deleteStmt = $pdo->prepare("DELETE FROM $table WHERE npm = ?");
            if ($deleteStmt->execute([$npm])) {
                $success = "Data dan file $type berhasil dihapus.";
            } else {
                $error = "Gagal menghapus data $type.";
            }
        } else {
            $error = "Data $type tidak ditemukan.";
        }
    } catch (PDOException $e) {
        $error = "Error database: " . $e->getMessage();
    }
}

// Mendapatkan data mahasiswa untuk sidang dan kompre
$stmtSidang = $pdo->prepare("
    SELECT m.npm, m.nama_lengkap, s.status, s.sertifikat_pi, s.sertifikat_workshop, s.sertifikat_kursus, s.sertifikat_apptitude, s.pesan_informasi
    FROM mahasiswa m
    JOIN sidang s ON m.npm = s.npm
");
$stmtSidang->execute();
$sidang_list = $stmtSidang->fetchAll();

$stmtKompre = $pdo->prepare("
    SELECT m.npm, m.nama_lengkap, k.status, k.sertifikat_pi, k.sertifikat_workshop, k.sertifikat_kursus, k.sertifikat_apptitude, k.pesan_informasi
    FROM mahasiswa m
    JOIN kompre k ON m.npm = k.npm
");
$stmtKompre->execute();
$kompre_list = $stmtKompre->fetchAll();

// Ambil nilai pencarian dan filter dari form (GET)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query untuk mencari data sidang dan kompre
$querySidang = "
    SELECT m.npm, m.nama_lengkap, s.status, s.surat_krs, s.sertifikat_pi, s.sertifikat_workshop, s.sertifikat_kursus, s.sertifikat_apptitude, s.acc_pembimbing_1, s.acc_pembimbing_2, s.buku_bimbingan, s.manual_book, s.draft_jurnal, s.poster, s.pesan_informasi
    FROM mahasiswa m
    JOIN sidang s ON m.npm = s.npm
    WHERE m.npm LIKE :search OR m.nama_lengkap LIKE :search
";

$queryKompre = "
    SELECT m.npm, m.nama_lengkap, k.status, k.sertifikat_pi, k.surat_krs, k.sertifikat_workshop, k.sertifikat_kursus, k.sertifikat_apptitude, k.pesan_informasi
    FROM mahasiswa m
    JOIN kompre k ON m.npm = k.npm
    WHERE m.npm LIKE :search OR m.nama_lengkap LIKE :search
";

// Eksekusi query dengan parameter pencarian
$stmtSidang = $pdo->prepare($querySidang);
$stmtKompre = $pdo->prepare($queryKompre);
$stmtSidang->execute(['search' => "%$search%"]);
$stmtKompre->execute(['search' => "%$search%"]);

// Ambil hasil pencarian
$sidang_list = $stmtSidang->fetchAll();
$kompre_list = $stmtKompre->fetchAll();

// Function to get count of unverified students
function getUnverifiedCount($pdo)
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM (
            SELECT npm FROM sidang WHERE status = 'belum diverifikasi'
            UNION
            SELECT npm FROM kompre WHERE status = 'belum diverifikasi'
        ) as combined");
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Get the count
$unverifiedCount = getUnverifiedCount($pdo);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sidang Skripsi dan Kompre</title>
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

        .file-link {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: #f8f9fc;
            border-radius: 0.25rem;
            color: var(--primary-color);
            text-decoration: none;
            margin: 0.125rem;
            transition: all 0.2s;
        }

        .file-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .section-title {
            color: var(--dark-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
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

        .modal-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        /* Form Elements */
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

        /* Template Buttons */
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
            cursor: pointer;
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

        .template-btn i {
            font-size: 1rem;
        }

        /* Animation */
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

        .truncate-message {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            color: #4e73df;
        }

        .truncate-message:hover {
            text-decoration: underline;
        }

        .message-modal .modal-body {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <?php include '../komponen/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="text-center mb-0"><i class="fas fa-graduation-cap me-2"></i>Admin Sidang Skripsi dan Kompre</h1>
            <p class="text-center mt-2 mb-0">Kelola pendaftaran dan verifikasi berkas sidang dan kompre mahasiswa</p>
        </div>
    </div>

    <div class="container fade-in">
        <?php if (isset($success)) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } elseif (isset($error)) { ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <!-- Search Box -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="search-box">
                <form method="GET" action="">
                    <input type="text" name="search" class="form-control" placeholder="Cari NPM atau Nama..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </form>
            </div>

            <?php
            // Ambil nilai filter status, kategori data, dan pencarian dari URL
            $filter_status = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';
            $filter_kategori = isset($_GET['filter_kategori']) ? trim($_GET['filter_kategori']) : 'semua';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            // Query untuk mengambil data berdasarkan kategori dan filter status
            $querySidang = "
    SELECT m.npm, m.nama_lengkap, s.status, s.surat_krs, s.sertifikat_pi, s.sertifikat_workshop, s.sertifikat_kursus, s.sertifikat_apptitude, 
    s.acc_pembimbing_1, s.acc_pembimbing_2, s.buku_bimbingan, s.manual_book, s.draft_jurnal, s.poster, s.pesan_informasi
    FROM mahasiswa m
    JOIN sidang s ON m.npm = s.npm
    WHERE (m.npm LIKE :search OR m.nama_lengkap LIKE :search)
";

            $queryKompre = "
    SELECT m.npm, m.nama_lengkap, k.status, k.surat_krs, k.sertifikat_pi, k.sertifikat_workshop, k.sertifikat_kursus, k.sertifikat_apptitude, 
    k.pesan_informasi
    FROM mahasiswa m
    JOIN kompre k ON m.npm = k.npm
    WHERE (m.npm LIKE :search OR m.nama_lengkap LIKE :search)
";

            // Tambahkan kondisi filter status jika dipilih
            if (!empty($filter_status)) {
                $querySidang .= " AND s.status = :filter_status";
                $queryKompre .= " AND k.status = :filter_status";
            }

            // Persiapkan dan eksekusi query berdasarkan kategori yang dipilih
            $params = ['search' => "%$search%"];
            if (!empty($filter_status)) {
                $params['filter_status'] = $filter_status;
            }

            $sidang_list = [];
            $kompre_list = [];

            if ($filter_kategori == 'sidang' || $filter_kategori == 'semua') {
                $stmtSidang = $pdo->prepare($querySidang);
                $stmtSidang->execute($params);
                $sidang_list = $stmtSidang->fetchAll();
            }

            if ($filter_kategori == 'kompre' || $filter_kategori == 'semua') {
                $stmtKompre = $pdo->prepare($queryKompre);
                $stmtKompre->execute($params);
                $kompre_list = $stmtKompre->fetchAll();
            }
            // Tentukan label untuk kategori dan status yang dipilih
            $label_kategori = match ($filter_kategori) {
                'sidang' => 'Sidang',
                'kompre' => 'Kompre',
                default => 'Semua Data'
            };

            $label_status = match ($filter_status) {
                'diverifikasi' => 'Diverifikasi',
                'belum diverifikasi' => 'Belum Diverifikasi',
                'ditolak' => 'Ditolak',
                default => 'Semua Status'
            };

            // Gabungkan label kategori dan status untuk ditampilkan pada tombol
            $label_tombol = "Lihat $label_kategori ($label_status)";
            ?>

            <div class="filter-notification-container">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="filterDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $label_tombol ?>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                        <!-- Kategori -->
                        <li class="dropdown-header">Kategori</li>
                        <li><a class="dropdown-item"
                                href="?filter_kategori=semua&filter_status=<?= $filter_status ?>&search=<?= $search ?>">Semua
                                Data</a></li>
                        <li><a class="dropdown-item"
                                href="?filter_kategori=sidang&filter_status=<?= $filter_status ?>&search=<?= $search ?>">Sidang</a>
                        </li>
                        <li><a class="dropdown-item"
                                href="?filter_kategori=kompre&filter_status=<?= $filter_status ?>&search=<?= $search ?>">Kompre</a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- Status -->
                        <li class="dropdown-header">Status</li>
                        <li><a class="dropdown-item"
                                href="?filter_kategori=<?= $filter_kategori ?>&filter_status=&search=<?= $search ?>">Semua
                                Status</a></li>
                        <li><a class="dropdown-item"
                                href="?filter_kategori=<?= $filter_kategori ?>&filter_status=diverifikasi&search=<?= $search ?>">Diverifikasi</a>
                        </li>
                        <li><a class="dropdown-item"
                                href="?filter_kategori=<?= $filter_kategori ?>&filter_status=belum diverifikasi&search=<?= $search ?>">Belum
                                Diverifikasi</a></li>
                        <li><a class="dropdown-item"
                                href="?filter_kategori=<?= $filter_kategori ?>&filter_status=ditolak&search=<?= $search ?>">Ditolak</a>
                        </li>
                    </ul>
                </div>
                <div class="notification-icon" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <i class="fas fa-bell fa-fw"></i>
                    <?php if ($unverifiedCount > 0): ?>
                        <span class="notification-badge"><?php echo $unverifiedCount; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>




        <!-- Sidang Section -->
        <?php if (!empty($sidang_list)) { ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Daftar Pengajuan Sidang Skripsi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>NPM</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Dokumen</th>
                                    <th>Pesan Informasi</th>
                                    <th></th>
                                    <th></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sidang_list as $sidang) { ?>
                                    <tr>
                                        <td><?php echo $sidang['npm']; ?></td>
                                        <td><?php echo $sidang['nama_lengkap']; ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                            echo $sidang['status'] == 'diverifikasi' ? 'status-diverifikasi' : ($sidang['status'] == 'ditolak' ? 'status-ditolak' : 'status-belum-diverifikasi');
                                            ?>">
                                                <?php echo $sidang['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($sidang['surat_krs']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['surat_krs']; ?>" target="_blank"
                                                        class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>KRS
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['sertifikat_workshop']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['sertifikat_workshop']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Workshop
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['sertifikat_kursus']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['sertifikat_kursus']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Kursus
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['sertifikat_apptitude']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['sertifikat_apptitude']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Apptitude
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['acc_pembimbing_1']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['acc_pembimbing_1']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>ACC Dospem 1
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['acc_pembimbing_2']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['acc_pembimbing_2']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>ACC Dospem 2
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['buku_bimbingan']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['buku_bimbingan']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Buku Bimbingan
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['manual_book']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['manual_book']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Manual Book
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['draft_jurnal']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['draft_jurnal']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Jurnal
                                                    </a>
                                                <?php } ?>
                                                <?php if ($sidang['poster']) { ?>
                                                    <a href="../uploads/sidang/<?php echo $sidang['poster']; ?>" target="_blank"
                                                        class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Poster
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="truncate-message"
                                                onclick="showMessageDetail('<?php echo htmlspecialchars($sidang['pesan_informasi']); ?>')">
                                                <?php echo $sidang['pesan_informasi']; ?>
                                            </div>
                                        </td>
                                        <td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm d-flex align-items-center gap-2"
                                                onclick="openMessageModal('<?php echo $sidang['npm']; ?>', 'sidang', '<?php echo $sidang['status']; ?>', '<?php echo htmlspecialchars($sidang['pesan_informasi']); ?>')">
                                                <i class="fas fa-edit"></i>
                                                <span>Update</span>
                                            </button>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="npm" value="<?php echo $sidang['npm']; ?>">
                                                <input type="hidden" name="type" value="sidang">
                                                <button type="submit" name="delete"
                                                    class="btn btn-danger btn-sm d-flex align-items-center gap-2"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>

        <!-- Kompre Section -->
        <?php if (!empty($kompre_list)) { ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Daftar Pengajuan Komprehensif</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>NPM</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Dokumen</th>
                                    <th>Pesan Informasi</th>
                                    <th></th>
                                    <th></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kompre_list as $kompre) { ?>
                                    <tr>
                                        <td><?php echo $kompre['npm']; ?></td>
                                        <td><?php echo $kompre['nama_lengkap']; ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                            echo $kompre['status'] == 'diverifikasi' ? 'status-diverifikasi' : ($kompre['status'] == 'ditolak' ? 'status-ditolak' : 'status-belum-diverifikasi');
                                            ?>">
                                                <?php echo $kompre['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($kompre['surat_krs']) { ?>
                                                    <a href="../uploads/kompre/<?php echo $kompre['surat_krs']; ?>" target="_blank"
                                                        class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>KRS
                                                    </a>
                                                <?php } ?>
                                                <?php if ($kompre['sertifikat_pi']) { ?>
                                                    <a href="../uploads/kompre/<?php echo $kompre['sertifikat_pi']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>PI
                                                    </a>
                                                <?php } ?>
                                                <?php if ($kompre['sertifikat_workshop']) { ?>
                                                    <a href="../uploads/kompre/<?php echo $kompre['sertifikat_workshop']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Workshop
                                                    </a>
                                                <?php } ?>
                                                <?php if ($kompre['sertifikat_kursus']) { ?>
                                                    <a href="../uploads/kompre/<?php echo $kompre['sertifikat_kursus']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Kursus
                                                    </a>
                                                <?php } ?>
                                                <?php if ($kompre['sertifikat_apptitude']) { ?>
                                                    <a href="../uploads/kompre/<?php echo $kompre['sertifikat_apptitude']; ?>"
                                                        target="_blank" class="file-link">
                                                        <i class="fas fa-file-alt me-1"></i>Apptitude
                                                    </a>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="truncate-message"
                                                onclick="showMessageDetail('<?php echo htmlspecialchars($kompre['pesan_informasi']); ?>')">
                                                <?php echo $kompre['pesan_informasi']; ?>
                                            </div>
                                        </td>
                                        <td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm d-flex align-items-center gap-2"
                                                onclick="openMessageModal('<?php echo $kompre['npm']; ?>', 'kompre', '<?php echo $kompre['status']; ?>', '<?php echo htmlspecialchars($kompre['pesan_informasi']); ?>')">
                                                <i class="fas fa-edit"></i>
                                                <span>Update</span>
                                            </button>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="npm" value="<?php echo $kompre['npm']; ?>">
                                                <input type="hidden" name="type" value="kompre">
                                                <button type="submit" name="delete"
                                                    class="btn btn-danger btn-sm d-flex align-items-center gap-2"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>


        <?php if (empty($sidang_list) && empty($kompre_list)) { ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Tidak ada data pengajuan sidang atau komprehensif yang
                ditemukan.
            </div>
        <?php } ?>
    </div>

    <!-- Enhanced Modal Design -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Update Status & Pesan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateMessageForm" method="POST">
                        <input type="hidden" name="npm" id="modalNpm">
                        <input type="hidden" name="type" id="modalType">
                        <input type="hidden" name="status" id="selectedStatus">

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Status Verifikasi</label>
                                <div class="d-flex gap-3 status-buttons">
                                    <button type="button" class="btn btn-outline-success flex-grow-1 status-btn"
                                        data-status="diverifikasi">
                                        <i class="fas fa-check-circle me-2"></i>Diverifikasi
                                    </button>
                                    <button type="button" class="btn btn-outline-warning flex-grow-1 status-btn"
                                        data-status="belum diverifikasi">
                                        <i class="fas fa-clock me-2"></i>Belum Diverifikasi
                                    </button>
                                    <button type="button" class="btn btn-outline-danger flex-grow-1 status-btn"
                                        data-status="ditolak">
                                        <i class="fas fa-times-circle me-2"></i>Ditolak
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Template Pesan</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-success template-btn"
                                    data-template="verifikasi">
                                    <i class="fas fa-check me-1"></i>Berkas Lengkap
                                </button>
                                <button type="button" class="btn btn-outline-warning template-btn"
                                    data-template="revisi">
                                    <i class="fas fa-edit me-1"></i>Perlu Revisi
                                </button>
                                <button type="button" class="btn btn-outline-danger template-btn" data-template="tolak">
                                    <i class="fas fa-times me-1"></i>Berkas Tidak Sesuai
                                </button>
                                <button type="button" class="btn btn-outline-info template-btn" data-template="kurang">
                                    <i class="fas fa-exclamation-circle me-1"></i>Berkas Kurang
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modalMessage" class="form-label fw-bold">
                                <i class="fas fa-pen me-2"></i>Pesan Informasi
                            </label>
                            <textarea class="form-control" id="modalMessage" name="pesan_informasi" rows="4"
                                placeholder="Masukkan pesan informasi atau catatan tambahan..."></textarea>
                        </div>

                        <div class="modal-footer border-top pt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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

    <!-- Message Detail Modal -->
    <div class="modal fade" id="messageDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope me-2"></i>Detail Pesan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body message-modal">
                    <p id="fullMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-bell me-2"></i>Notifikasi Status Verifikasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($unverifiedCount > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Terdapat <strong><?php echo $unverifiedCount; ?></strong> mahasiswa yang belum
                            diverifikasi
                        </div>
                        <div class="notification-list">
                            <?php
                            $stmt = $pdo->prepare("
                            SELECT m.npm, m.nama_lengkap, 'sidang' as type 
                            FROM mahasiswa m 
                            JOIN sidang s ON m.npm = s.npm 
                            WHERE s.status = 'belum diverifikasi'
                            UNION ALL
                            SELECT m.npm, m.nama_lengkap, 'kompre' as type 
                            FROM mahasiswa m 
                            JOIN kompre k ON m.npm = k.npm 
                            WHERE k.status = 'belum diverifikasi'
                            ORDER BY npm");
                            $stmt->execute();
                            $unverifiedStudents = $stmt->fetchAll();

                            foreach ($unverifiedStudents as $student):
                                ?>
                                <div class="notification-item">
                                    <i class="fas fa-user-clock me-2"></i>
                                    <?php echo $student['nama_lengkap']; ?> (<?php echo $student['npm']; ?>) -
                                    <?php echo ucfirst($student['type']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Tidak ada mahasiswa yang belum diverifikasi
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>

        <script src="../assets/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Auto-submit search form when input changes
                document.querySelector('input[name="search"]')?.addEventListener('input', function () {
                    this.form.submit();
                });

                // Auto-hide alerts after 5 seconds
                setTimeout(function () {
                    document.querySelectorAll('.alert').forEach(function (alert) {
                        alert.classList.remove('show');
                        setTimeout(function () {
                            alert.remove();
                        }, 300);
                    });
                }, 5000);

                // Status button handling
                const statusButtons = document.querySelectorAll('.status-btn');
                const selectedStatusInput = document.getElementById('selectedStatus');

                statusButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        // Remove active class from all buttons
                        statusButtons.forEach(btn => {
                            btn.classList.remove('active');
                            btn.classList.remove('btn-success', 'btn-warning', 'btn-danger');
                            btn.classList.add('btn-outline-' + getButtonStyle(btn.dataset.status));
                        });

                        // Add active class and solid style to clicked button
                        this.classList.add('active');
                        this.classList.remove('btn-outline-' + getButtonStyle(this.dataset.status));
                        this.classList.add('btn-' + getButtonStyle(this.dataset.status));

                        // Update hidden input
                        selectedStatusInput.value = this.dataset.status;
                    });
                });

                // Template message handling
                const templateButtons = document.querySelectorAll('.template-btn');
                const messageTextarea = document.getElementById('modalMessage');

                const templates = {
                    verifikasi: "Selamat! Berkas Anda telah diverifikasi dan memenuhi semua persyaratan.",
                    revisi: "Mohon melakukan revisi pada berkas yang telah diupload. Silakan periksa kembali kelengkapan dokumen Anda.",
                    tolak: "Maaf, berkas Anda tidak dapat diverifikasi karena tidak memenuhi persyaratan yang ditentukan.",
                    kurang: "Berkas Anda belum lengkap. Mohon lengkapi dokumen yang diperlukan."
                };

                templateButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const templateType = this.dataset.template;
                        messageTextarea.value = templates[templateType];

                        // Update visual feedback
                        templateButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            });

            function getButtonStyle(status) {
                switch (status) {
                    case 'diverifikasi': return 'success';
                    case 'belum diverifikasi': return 'warning';
                    case 'ditolak': return 'danger';
                    default: return 'secondary';
                }
            }

            function openMessageModal(npm, type, status, message) {
                // Set form values
                document.getElementById('modalNpm').value = npm;
                document.getElementById('modalType').value = type;
                document.getElementById('modalMessage').value = message;
                document.getElementById('selectedStatus').value = status;

                // Update status buttons
                const statusButtons = document.querySelectorAll('.status-btn');
                statusButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.classList.remove('btn-success', 'btn-warning', 'btn-danger');
                    btn.classList.add('btn-outline-' + getButtonStyle(btn.dataset.status));

                    if (btn.dataset.status === status) {
                        btn.classList.add('active');
                        btn.classList.remove('btn-outline-' + getButtonStyle(status));
                        btn.classList.add('btn-' + getButtonStyle(status));
                    }
                });

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                modal.show();
            }

            // Detail massage
            function showMessageDetail(message) {
                document.getElementById('fullMessage').textContent = message;
                const modal = new bootstrap.Modal(document.getElementById('messageDetailModal'));
                modal.show();
            }
        </script>
</body>

</html>