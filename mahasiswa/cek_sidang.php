<?php
session_start();
require_once 'session_handler.php';
checkSessionTimeout();

// Check if the user is logged in and has the 'mahasiswa' role
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

require '../config.php';

$allowed_files = [
    'surat_krs',
    'sertifikat_pi',
    'sertifikat_workshop',
    'sertifikat_kursus',
    'sertifikat_apptitude',
    'acc_pembimbing_1',
    'acc_pembimbing_2',
    'buku_bimbingan',
    'manual_book',
    'draft_jurnal',
    'poster'
];
$documents = [
    'surat_krs' => 'Surat KRS',
    'sertifikat_pi' => 'Sertifikat PI',
    'sertifikat_workshop' => 'Sertifikat Workshop',
    'sertifikat_kursus' => 'Sertifikat Kursus',
    'sertifikat_apptitude' => 'Sertifikat Apptitude',
    'acc_pembimbing_1' => 'Acc Pembimbing 1',
    'acc_pembimbing_2' => 'Acc Pembimbing 2',
    'buku_bimbingan' => 'Buku Bimbingan',
    'manual_book' => 'Manual Book',
    'draft_jurnal' => 'Draft Jurnal',
    'poster' => 'Poster'
];

$npm = $_SESSION['npm'];

// Get data from 'sidang' table for Sidang Skripsi
$stmtSidang = $pdo->prepare("
    SELECT 'sidang' AS type, m.npm, m.nama_lengkap, s.status, s.surat_krs, s.sertifikat_pi, 
           s.sertifikat_workshop, s.sertifikat_kursus, s.sertifikat_apptitude, s.acc_pembimbing_1, 
           s.acc_pembimbing_2, s.buku_bimbingan, s.manual_book, s.draft_jurnal, s.poster, s.pesan_informasi
    FROM mahasiswa m
    LEFT JOIN sidang s ON m.npm = s.npm
    WHERE m.npm = ? AND s.npm IS NOT NULL
");
$stmtSidang->execute([$npm]);
$sidang = $stmtSidang->fetch();

// Get data from 'kompre' table for Sidang Komprehensif
$stmtKompre = $pdo->prepare("
    SELECT 'kompre' AS type, m.npm, m.nama_lengkap, k.status, k.surat_krs, k.sertifikat_pi, 
           k.sertifikat_workshop, k.sertifikat_kursus, k.sertifikat_apptitude, k.pesan_informasi
    FROM mahasiswa m
    LEFT JOIN kompre k ON m.npm = k.npm
    WHERE m.npm = ? AND k.npm IS NOT NULL
");
$stmtKompre->execute([$npm]);
$kompre = $stmtKompre->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_single_file']) && !empty($_POST['update_file'])) {
        $sidang_type = $_POST['sidang_type'];
        $update_file = $_POST['update_file'];
        $target_dir = ($sidang_type === 'sidang') ? "../uploads/sidang/" : "../uploads/kompre/";
        $table = ($sidang_type === 'sidang') ? 'sidang' : 'kompre';

        if (!empty($_FILES['files']['name'][$update_file])) {
            $file_name = basename($_FILES['files']['name'][$update_file]);
            $file_tmp = $_FILES['files']['tmp_name'][$update_file];
            $target_file = $target_dir . $file_name;

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $target_file)) {
                $sql = "UPDATE $table SET $update_file = ?, status = 'belum diverifikasi' WHERE npm = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$file_name, $npm]);

                $_SESSION['message'] = "File '$documents[$update_file]' berhasil diperbarui dan status diubah menjadi 'belum diverifikasi'.";
            }
        }
    }

    // Redirect to refresh the page and show updated files
    header("Location: cek_sidang.php"); // Ganti cek_status.php sesuai nama file Anda
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <title>Cek Status Sidang Skripsi dan Komprehensif</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
    <style>
        :root {
            --primary-color: #2575fc;
            --secondary-color: #6a11cb;
            --text-dark: #2c3e50;
            --bg-light: #f5f7fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Inter', Arial, sans-serif;
            color: #333;
        }

        .main-content {
            padding: 2rem 0;
        }


        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            border-radius: 15px;
            padding: 2.5rem 2rem;
            margin-bottom: 3rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(37, 117, 252, 0.2);
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(45deg,
                    transparent,
                    transparent 10px,
                    rgba(255, 255, 255, 0.05) 10px,
                    rgba(255, 255, 255, 0.05) 20px);
            animation: move-bg 20s linear infinite;
        }

        @keyframes move-bg {
            0% {
                transform: translateX(0) translateY(0);
            }

            100% {
                transform: translateX(-50%) translateY(-50%);
            }
        }

        .welcome-banner h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
        }

        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 40px 0 rgba(0, 0, 0, .15);
            overflow: hidden;
            margin: 2rem 0;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            font-weight: 600;
            padding: 1.2rem 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            border: none;
        }

        .table td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .table tr:hover td {
            background-color: rgba(37, 117, 252, 0.05);
        }

        /* File Links */
        .icon-link {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: var(--bg-light);
            border-radius: 8px;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            gap: 0.5rem;
        }

        .icon-link:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-diverifikasi {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }

        .status-ditolak {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        /* File Upload Area */
        .file-upload {
            position: relative;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .file-input {
            flex: 1;
        }

        .upload-btn {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.2);
        }

        /* Card Styles */
        .status-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 0 40px 0 rgba(0, 0, 0, .1);
            overflow: hidden;
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }

        .status-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 1.5rem;
            border-bottom: none;
        }

        .card-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Alert Styles */
        .alert {
            border-radius: 15px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .05);
        }

        .alert-info {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
            border-left: 4px solid var(--info-color);
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .file-upload {
                flex-direction: column;
            }

            .upload-btn {
                width: 100%;
            }

            .welcome-banner {
                padding: 2rem 1rem;
            }

            .welcome-banner h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['upload_success'])): ?>
        <div id="notification-root"></div>
        <script type="text/babel">
            const SuccessNotification = () => {
                const [isVisible, setIsVisible] = React.useState(true);

                React.useEffect(() => {
                    const confetti = () => {
                        const colors = ['#2575fc', '#6a11cb', '#f5f7fa'];
                        const canvas = document.createElement('canvas');
                        canvas.style.position = 'fixed';
                        canvas.style.top = '0';
                        canvas.style.left = '0';
                        canvas.style.width = '100%';
                        canvas.style.height = '100%';
                        canvas.style.pointerEvents = 'none';
                        canvas.style.zIndex = '9999';
                        document.body.appendChild(canvas);

                        const ctx = canvas.getContext('2d');
                        canvas.width = window.innerWidth;
                        canvas.height = window.innerHeight;

                        const particles = [];

                        for (let i = 0; i < 100; i++) {
                            particles.push({
                                x: Math.random() * canvas.width,
                                y: canvas.height + Math.random() * 100,
                                vx: (Math.random() - 0.5) * 10,
                                vy: -Math.random() * 10 - 10,
                                size: Math.random() * 5 + 5,
                                color: colors[Math.floor(Math.random() * colors.length)]
                            });
                        }

                        const animate = () => {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            particles.forEach((p, i) => {
                                p.x += p.vx;
                                p.y += p.vy;
                                p.vy += 0.2;
                                p.size *= 0.99;
                                ctx.fillStyle = p.color;
                                ctx.beginPath();
                                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                                ctx.fill();
                                if (p.size < 0.1) particles.splice(i, 1);
                            });
                            if (particles.length > 0) {
                                requestAnimationFrame(animate);
                            } else {
                                document.body.removeChild(canvas);
                            }
                        };
                        animate();
                    };
                    confetti();
                }, []);

                if (!isVisible) return null;

                return (
                    <div style={{
                        position: 'fixed',
                        top: '80px',
                        right: '20px',
                        width: '384px',
                        zIndex: 9999,
                        animation: 'slideIn 0.5s ease-out'
                    }}>
                        <div style={{
                            background: 'linear-gradient(to right, #7928CA, #FF0080)',
                            padding: '16px',
                            borderRadius: '8px',
                            color: 'white',
                            boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                            position: 'relative'
                        }}>
                            <button
                                onClick={() => setIsVisible(false)}
                                style={{
                                    position: 'absolute',
                                    top: '8px',
                                    right: '8px',
                                    background: 'none',
                                    border: 'none',
                                    color: 'white',
                                    cursor: 'pointer',
                                    padding: '4px'
                                }}
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div>
                                    <h4 style={{ fontSize: '1.125rem', fontWeight: '600', marginBottom: '8px' }}>
                                        Dokumen Berhasil Diunggah!
                                    </h4>
                                    <div style={{ color: 'rgba(255, 255, 255, 0.9)' }}>
                                        <div>
                                            <p>Semua dokumen persyaratan sidang telah berhasil disimpan.</p>
                                            <div style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '8px',
                                                marginTop: '8px',
                                                background: 'rgba(255, 255, 255, 0.2)',
                                                padding: '8px',
                                                borderRadius: '6px'
                                            }}>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                <span>Menunggu verifikasi dari admin/staff</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            };

            ReactDOM.createRoot(document.getElementById('notification-root')).render(<SuccessNotification />);
        </script>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>
        <?php unset($_SESSION['upload_success']); ?>
    <?php endif; ?>
    <?php include '../komponen/navbarus.php'; ?>

    <div class="container main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>Cek Status Sidang Skripsi dan Komprehensif</h2>
            <p>Administrasi Akademik Universitas Gunadarma Fakultas Ilmu Komputer & Teknologi Informasi</p>
        </div>

        <div class="container my-5">
            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['message'];
                    unset($_SESSION['message']); ?>
                </div>
            <?php } ?>

            <?php if (!$sidang && !$kompre) { ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Anda belum pernah mengajukan sidang sarjana atau komprehensif.
                </div>
                <div class="text-center mt-4">
                    <a href="upload_sidang.php" class="btn btn-primary me-2">
                        <i class="fas fa-file-upload me-2"></i>
                        Ajukan Sidang Sarjana
                    </a>
                    <a href="upload_kompre.php" class="btn btn-primary">
                        <i class="fas fa-file-upload me-2"></i>
                        Ajukan Sidang Komprehensif
                    </a>
                </div>
            <?php } ?>

            <!-- Bagian Sidang Skripsi -->
            <?php if ($sidang) { ?>
                <div class="status-card">
                    <div class="card-header">
                        <h2 class="card-title text-center mb-0">Status Sidang Skripsi</h2>
                    </div>
                    <div class="card-body">
                        <div class="card-body">
                            <?php if ($sidang['pesan_informasi']) { ?>
                                <div class="alert 
        <?php
        switch ($sidang['status']) {
            case 'diverifikasi':
                echo 'alert-success';
                break;
            case 'belum diverifikasi':
                echo 'alert-warning';
                break;
            case 'ditolak':
                echo 'alert-danger';
                break;
            default:
                echo 'alert-info';
        }
        ?>">
                                    <i class="fas 
            <?php
            switch ($sidang['status']) {
                case 'diverifikasi':
                    echo 'fa-check-circle';
                    break;
                case 'belum diverifikasi':
                    echo 'fa-clock';
                    break;
                case 'ditolak':
                    echo 'fa-times-circle';
                    break;
                default:
                    echo 'fa-info-circle';
            }
            ?> me-2"></i>
                                    <strong>Informasi:</strong> <?php echo htmlspecialchars($sidang['pesan_informasi']); ?>
                                </div>
                            <?php } ?>
                            <div class="table-container">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th width="30%">NPM</th>
                                            <td><?php echo htmlspecialchars($sidang['npm']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nama</th>
                                            <td><?php echo htmlspecialchars($sidang['nama_lengkap']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status Sidang</th>
                                            <td>
                                                <span
                                                    class="status-badge status-<?php echo strtolower($sidang['status']); ?>">
                                                    <i class="fas fa-circle me-2"></i>
                                                    <?php echo htmlspecialchars($sidang['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php foreach ($allowed_files as $key) { ?>
                                            <tr>
                                                <th><?php echo $documents[$key]; ?></th>
                                                <td class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <?php if ($sidang[$key]) { ?>
                                                            <a href='../uploads/sidang/<?php echo htmlspecialchars($sidang[$key]); ?>'
                                                                target='_blank' class='icon-link'>
                                                                <i class='fas fa-file-alt'></i>
                                                                Lihat Dokumen
                                                            </a>
                                                        <?php } else { ?>
                                                            <span class="text-muted">
                                                                <i class="fas fa-times-circle me-2"></i>
                                                                Tidak Ada
                                                            </span>
                                                        <?php } ?>
                                                    </div>
                                                    <?php if ($sidang['status'] === 'ditolak' || $sidang['status'] === 'belum diverifikasi') { ?>
                                                        <div class="file-upload">
                                                            <form method="POST" enctype="multipart/form-data"
                                                                class="d-flex gap-2 align-items-center">
                                                                <input type="hidden" name="sidang_type" value="sidang">
                                                                <input type="hidden" name="update_file" value="<?php echo $key; ?>">
                                                                <div class="file-input">
                                                                    <input type="file" name="files[<?php echo $key; ?>]"
                                                                        class="form-control">
                                                                </div>
                                                                <button type="submit" name="update_single_file" class="upload-btn">
                                                                    <i class="fas fa-upload me-2"></i>
                                                                    Update
                                                                </button>
                                                            </form>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!-- Bagian Komprehensif -->
                <?php if ($kompre) { ?>
                    <div class="status-card">
                        <div class="card-header">
                            <h2 class="card-title text-center mb-0">Status Sidang Komprehensif</h2>
                        </div>
                        <div class="card-body">
                            <div class="card-body">
                                <?php if ($kompre['pesan_informasi']) { ?>
                                    <div class="alert 
        <?php
        switch ($kompre['status']) {
            case 'diverifikasi':
                echo 'alert-success';
                break;
            case 'belum diverifikasi':
                echo 'alert-warning';
                break;
            case 'ditolak':
                echo 'alert-danger';
                break;
            default:
                echo 'alert-info';
        }
        ?>">
                                        <i class="fas 
            <?php
            switch ($kompre['status']) {
                case 'diverifikasi':
                    echo 'fa-check-circle';
                    break;
                case 'belum diverifikasi':
                    echo 'fa-clock';
                    break;
                case 'ditolak':
                    echo 'fa-times-circle';
                    break;
                default:
                    echo 'fa-info-circle';
            }
            ?> me-2"></i>
                                        <strong>Informasi:</strong> <?php echo htmlspecialchars($kompre['pesan_informasi']); ?>
                                    </div>
                                <?php } ?>
                                <div class="table-container">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <th width="30%">NPM</th>
                                                <td><?php echo htmlspecialchars($kompre['npm']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nama</th>
                                                <td><?php echo htmlspecialchars($kompre['nama_lengkap']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Status Kompre</th>
                                                <td>
                                                    <span
                                                        class="status-badge status-<?php echo strtolower($kompre['status']); ?>">
                                                        <i class="fas fa-circle me-2"></i>
                                                        <?php echo htmlspecialchars($kompre['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php $i = 0; ?>
                                            <?php foreach ($allowed_files as $key) { ?>
                                                <?php if ($i < 5) { ?>
                                                    <tr>
                                                        <th><?php echo $documents[$key]; ?></th>
                                                        <td class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <?php if ($kompre[$key]) { ?>
                                                                    <a href='../uploads/kompre/<?php echo htmlspecialchars($kompre[$key]); ?>'
                                                                        target='_blank' class='icon-link'>
                                                                        <i class='fas fa-file-alt'></i>
                                                                        Lihat Dokumen
                                                                    </a>
                                                                <?php } else { ?>
                                                                    <span class="text-muted">
                                                                        <i class="fas fa-times-circle me-2"></i>
                                                                        Tidak Ada
                                                                    </span>
                                                                <?php } ?>
                                                            </div>
                                                            <?php if ($kompre['status'] === 'ditolak' || $kompre['status'] === 'belum diverifikasi') { ?>
                                                                <div class="file-upload">
                                                                    <form method="POST" enctype="multipart/form-data"
                                                                        class="d-flex gap-2 align-items-center">
                                                                        <input type="hidden" name="sidang_type" value="kompre">
                                                                        <input type="hidden" name="update_file"
                                                                            value="<?php echo $key; ?>">
                                                                        <div class="file-input">
                                                                            <input type="file" name="files[<?php echo $key; ?>]"
                                                                                class="form-control">
                                                                        </div>
                                                                        <button type="submit" name="update_single_file"
                                                                            class="upload-btn">
                                                                            <i class="fas fa-upload me-2"></i>
                                                                            Update
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                <?php $i++; ?>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (isset($_SESSION['upload_success'])): ?>
                        <div id="notification-root"></div>
                        <script type="module">
                            import { createRoot } from 'react-dom/client';
                            import SuccessNotification from './SuccessNotification';

                            const root = createRoot(document.getElementById('notification-root'));
                            root.render(<SuccessNotification />);

                            <?php unset($_SESSION['upload_success']); ?>
                        </script>
                    <?php endif; ?>


                    <script
                        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>