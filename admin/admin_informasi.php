<?php
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

// Memastikan koneksi ke database
require '../config.php';

// Proses form input informasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $konten = $_POST['konten'];
    $kategori = $_POST['kategori'];

    $stmt = $pdo->prepare("INSERT INTO informasi (judul, konten, tanggal, kategori) VALUES (?, ?, NOW(), ?)");
    $stmt->execute([$judul, $konten, $kategori]);

    if ($stmt->rowCount() > 0) {
        $success = "Informasi berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan informasi.";
    }
}

// Proses hapus informasi
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM informasi WHERE id = ?");
    $stmt->execute([$deleteId]);

    if ($stmt->rowCount() > 0) {
        $success = "Informasi berhasil dihapus!";
    } else {
        $error = "Gagal menghapus informasi.";
    }
}

// Proses Pencarian
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $infoStmt = $pdo->prepare("SELECT * FROM informasi WHERE judul LIKE ? OR konten LIKE ? OR kategori LIKE ? ORDER BY tanggal DESC");
    $infoStmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
} else {
    $infoStmt = $pdo->query("SELECT * FROM informasi ORDER BY tanggal DESC");
}

$informasi = $infoStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Informasi</title>
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

        .badge-kategori {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .kategori-pi {
            background-color: var(--primary-color);
            color: white;
        }

        .kategori-sidang {
            background-color: var(--success-color);
            color: white;
        }

        .kategori-msib {
            background-color: var(--warning-color);
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

        .modal-enhanced {
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-header-enhanced {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 0.5rem 0.5rem 0 0;
            padding: 1.5rem;
        }

        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-floating input,
        .form-floating textarea,
        .form-floating select {
            height: auto;
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
        }

        .form-floating label {
            position: absolute;
            top: 0;
            left: 0;
            padding: 1rem 0.75rem;
            pointer-events: none;
            transform-origin: 0 0;
            transition: all 0.2s ease-in-out;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .form-floating input:focus,
        .form-floating textarea:focus,
        .form-floating select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .form-floating input:focus~label,
        .form-floating textarea:focus~label,
        .form-floating select:focus~label,
        .form-floating input:not(:placeholder-shown)~label,
        .form-floating textarea:not(:placeholder-shown)~label,
        .form-floating select:not(:placeholder-shown)~label {
            transform: scale(0.85) translateY(-0.5rem);
            color: #4e73df;
        }

        .char-counter {
            position: absolute;
            right: 1rem;
            bottom: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .preview-section {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            display: none;
        }

        .preview-section.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-pi {
            background-color: #4e73df;
            color: white;
        }

        .badge-sidang {
            background-color: #1cc88a;
            color: white;
        }

        .badge-msib {
            background-color: #f6c23e;
            color: white;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s ease-in-out;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .success-alert {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background-color: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
            animation: slideIn 0.3s ease-out;
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

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <?php include '../komponen/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="text-center mb-0"><i class="fas fa-info-circle me-2"></i>Kelola Informasi</h1>
            <p class="text-center mt-2 mb-0">Kelola dan publikasikan informasi untuk mahasiswa</p>
        </div>
    </div>

    <div class="container fade-in">
        <!-- Notifications -->
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

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInfoModal">
                <i class="fas fa-plus me-2"></i>Tambah Informasi
            </button>

            <!-- Search Box -->
            <div class="search-box" style="width: 300px;">
                <i class="fas fa-search"></i>
                <form method="GET" action="">
                    <input type="text" name="search" class="form-control" placeholder="Cari informasi..."
                        value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </form>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Informasi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Konten</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($informasi as $info) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($info['judul']); ?></td>
                                    <td>
                                        <a href="#" class="text-decoration-none" data-bs-toggle="modal"
                                            data-bs-target="#kontenModal<?php echo $info['id']; ?>">
                                            <?php echo strlen($info['konten']) > 50 ? htmlspecialchars(substr($info['konten'], 0, 50)) . '...' : htmlspecialchars($info['konten']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-kategori <?php
                                        echo $info['kategori'] == 'PI' ? 'kategori-pi' :
                                            ($info['kategori'] == 'Sidang' ? 'kategori-sidang' : 'kategori-msib');
                                        ?>">
                                            <?php echo $info['kategori']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($info['tanggal'])); ?></td>
                                    <td>
                                        <a href="admin_edit_informasi.php?id=<?php echo $info['id']; ?>"
                                            class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="?delete_id=<?php echo $info['id']; ?>" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus informasi ini?')">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal untuk melihat konten informasi -->
                                <div class="modal fade" id="kontenModal<?php echo $info['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?php echo htmlspecialchars($info['judul']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php echo nl2br(htmlspecialchars($info['konten'])); ?>
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

    <!-- Modifikasi modal yang sudah ada -->
    <div class="modal fade" id="addInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-enhanced">
                <!-- Enhanced Header -->
                <div class="modal-header modal-header-enhanced">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Informasi Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Enhanced Body -->
                <div class="modal-body p-4">
                    <div class="success-alert" id="successAlert">
                        <i class="fas fa-check-circle me-2"></i>
                        Informasi berhasil ditambahkan!
                    </div>

                    <form method="POST" action="" id="infoForm">
                        <!-- Enhanced Judul Input -->
                        <div class="form-floating">
                            <input type="text" class="form-control" id="judul" name="judul" placeholder=" " required>
                            <label for="judul">
                                <i class="fas fa-heading me-2"></i>Judul Informasi
                            </label>
                        </div>

                        <!-- Enhanced Konten Input -->
                        <div class="form-floating">
                            <textarea class="form-control" id="konten" name="konten" placeholder=" " rows="5"
                                required></textarea>
                            <label for="konten">
                                <i class="fas fa-paragraph me-2"></i>Konten Informasi
                            </label>
                            <div class="char-counter">
                                <span id="charCount">0</span> karakter
                            </div>
                        </div>

                        <!-- Enhanced Kategori Select -->
                        <div class="form-floating">
                            <select class="form-control" id="kategori" name="kategori" required>
                                <option value="PI">PI</option>
                                <option value="Sidang">Sidang</option>
                                <option value="MSIB">MSIB</option>
                            </select>
                            <label for="kategori">
                                <i class="fas fa-tag me-2"></i>Kategori
                            </label>
                        </div>

                        <!-- Preview Toggle -->
                        <div class="mt-3">
                            <button type="button" class="btn btn-link p-0" id="previewToggle">
                                <i class="fas fa-eye me-2"></i>Tampilkan Preview
                            </button>
                        </div>

                        <!-- Preview Section -->
                        <div class="preview-section" id="previewSection">
                            <h6 class="fw-bold" id="previewJudul">Judul Informasi</h6>
                            <p class="text-muted mb-2" id="previewKonten">Konten Informasi</p>
                            <span class="badge" id="previewKategori">Kategori</span>
                        </div>

                        <!-- Enhanced Submit Button -->
                        <button type="submit" class="btn-submit mt-4">
                            <i class="fas fa-save me-2"></i>Simpan Informasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('infoForm');
            const kontenInput = document.getElementById('konten');
            const charCount = document.getElementById('charCount');
            const previewToggle = document.getElementById('previewToggle');
            const previewSection = document.getElementById('previewSection');
            const previewJudul = document.getElementById('previewJudul');
            const previewKonten = document.getElementById('previewKonten');
            const previewKategori = document.getElementById('previewKategori');
            const successAlert = document.getElementById('successAlert');
            let previewVisible = false;

            // Character counter
            kontenInput.addEventListener('input', function () {
                charCount.textContent = this.value.length;
            });

            // Preview toggle
            previewToggle.addEventListener('click', function () {
                previewVisible = !previewVisible;
                previewSection.classList.toggle('active', previewVisible);
                this.innerHTML = previewVisible ?
                    '<i class="fas fa-eye-slash me-2"></i>Sembunyikan Preview' :
                    '<i class="fas fa-eye me-2"></i>Tampilkan Preview';
            });

            // Live preview updates
            form.addEventListener('input', function (e) {
                if (e.target.id === 'judul') {
                    previewJudul.textContent = e.target.value || 'Judul Informasi';
                }
                if (e.target.id === 'konten') {
                    previewKonten.textContent = e.target.value || 'Konten Informasi';
                }
                if (e.target.id === 'kategori') {
                    previewKategori.textContent = e.target.value;
                    previewKategori.className = `badge badge-${e.target.value.toLowerCase()}`;
                }
            });

            // Form submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Show success message
                successAlert.style.display = 'block';
                setTimeout(() => {
                    successAlert.style.display = 'none';
                    // Submit the form after showing the message
                    this.submit();
                }, 2000);
            });

            // Initialize floating labels
            document.querySelectorAll('.form-floating input, .form-floating textarea, .form-floating select').forEach(element => {
                if (element.value) {
                    element.parentElement.classList.add('is-filled');
                }
                element.addEventListener('focus', () => {
                    element.parentElement.classList.add('is-focused');
                });
                element.addEventListener('blur', () => {
                    element.parentElement.classList.remove('is-focused');
                });
            });
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

        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi carousel dengan interval 5 detik
            var myCarousel = document.getElementById('heroCarousel');
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 5000,    // Interval dalam milisecond (5000ms = 5 detik)
                wrap: true,        // Mengulang ke slide pertama setelah slide terakhir
                keyboard: false,   // Menonaktifkan kontrol keyboard
                touch: false,      // Menonaktifkan kontrol sentuh
                pause: false       // Tidak pause saat hover
            });
        });
    </script>
</body>

</html>