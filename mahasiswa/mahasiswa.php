<?php
session_start();
require_once 'session_handler.php';
checkSessionTimeout();


// Cek apakah user adalah mahasiswa
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

// Koneksi database
require '../config.php';

$npm = $_SESSION['npm'];

// Ambil data mahasiswa
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE npm = ?");
$stmt->execute([$npm]);
$mahasiswa = $stmt->fetch();

// Ambil informasi terbaru
$infoStmt = $pdo->query("SELECT * FROM informasi ORDER BY tanggal DESC");
$informasi = $infoStmt->fetchAll();

// Tentukan threshold (misalnya 7 hari) untuk informasi baru
$today = new DateTime();
$newThreshold = $today->modify('-7 days')->format('Y-m-d');

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../assets/icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Beranda Mahasiswa</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">

    <style>
        :root {
            --primary-color: #2575fc;
            --secondary-color: #6a11cb;
            --text-dark: #2c3e50;
            --bg-light: #f5f7fa;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Main Content Styles */
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

        /* Feature Cards */
        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card i {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        .feature-card h4 {
            color: var(--text-dark);
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
        }

        .feature-card p {
            color: #666;
            font-size: 1rem;
        }

        /* Information Section */
        .info-section {
            padding: 2rem 0;
        }

        .info-section h3 {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.8rem;
        }

        .info-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .info-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .info-header i {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 1rem;
        }

        .info-header h5 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--text-dark);
        }

        .badge-new {
            background: linear-gradient(45deg, #ff6b6b, #ee0979);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            margin-left: 1rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .info-content {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .info-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-date {
            color: #888;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .read-more {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-decoration: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .read-more:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateX(5px);
            color: white;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .welcome-banner {
                padding: 2rem 1rem;
                margin: 1rem;
            }

            .welcome-banner h2 {
                font-size: 1.8rem;
            }

            .features-container {
                grid-template-columns: 1fr;
                padding: 0 1rem;
            }

            .info-card {
                margin: 1rem;
            }

            .info-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .read-more {
                width: 100%;
                justify-content: center;
            }

            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
            }

            .modal.show {
                opacity: 1;
            }

            .modal-content {
                background-color: #fff;
                margin: 5% auto;
                width: 90%;
                max-width: 700px;
                border-radius: 12px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                transform: translateY(-50px);
                opacity: 0;
                transition: all 0.3s ease-in-out;
                overflow: hidden;
            }

            .modal.show .modal-content {
                transform: translateY(0);
                opacity: 1;
            }

            /* Modal Header */
            .modal-header {
                padding: 20px 25px;
                background-color: #f8f9fa;
                border-bottom: 1px solid #eee;
            }

            .modal-header h2 {
                margin: 0;
                font-size: 1.5rem;
                color: #333;
                font-weight: 600;
            }

            .close {
                position: absolute;
                right: 20px;
                top: 20px;
                font-size: 24px;
                color: #666;
                cursor: pointer;
                transition: all 0.2s;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
            }

            .close:hover {
                background-color: #f0f0f0;
                transform: rotate(90deg);
            }

            /* Modal Meta Information */
            .modal-meta {
                display: flex;
                gap: 15px;
                margin-top: 10px;
                font-size: 0.9rem;
                color: #666;
            }

            .modal-category,
            .modal-date {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .modal-category i,
            .modal-date i {
                font-size: 0.8rem;
            }

            /* Modal Body */
            .modal-body {
                padding: 25px;
                max-height: 60vh;
                overflow-y: auto;
                line-height: 1.6;
            }

            .modal-body::-webkit-scrollbar {
                width: 8px;
            }

            .modal-body::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .modal-body::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }

            /* Modal Footer */
            .modal-footer {
                padding: 15px 25px;
                border-top: 1px solid #eee;
                display: flex;
                justify-content: flex-end;
            }

            .share-buttons {
                display: flex;
                gap: 10px;
            }

            .share-btn {
                width: 35px;
                height: 35px;
                border: none;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
                color: white;
            }

            .share-btn:hover {
                transform: translateY(-2px);
            }

            .facebook {
                background-color: #1877f2;
            }

            .twitter {
                background-color: #1da1f2;
            }

            .whatsapp {
                background-color: #25d366;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .modal-content {
                    margin: 10% auto;
                    width: 95%;
                }

                .modal-header h2 {
                    font-size: 1.2rem;
                }

                .modal-meta {
                    flex-direction: column;
                    gap: 5px;
                }
            }

            /* Animation Keyframes */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @keyframes slideUp {
                from {
                    transform: translateY(100px);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
    </style>
    </style>
</head>

<body>
    <?php include '../komponen/navbarus.php'; ?>

    <div class="container main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>Selamat Datang di Administrasi Prodi Sistem Informasi</h2>
            <p>Administrasi Akademik Universitas Gunadarma Fakultas Ilmu Komputer & Teknologi Informasi</p>
        </div>

        <!-- Feature Cards -->
        <div class="features-container">
            <div class="feature-card">
                <i class="bi bi-journal-check"></i>
                <h4>Status PI</h4>
                <p>Cek dan monitoring status penelitian ilmiah anda</p>
            </div>
            <div class="feature-card">
                <i class="bi bi-file-earmark-text"></i>
                <h4>Sidang Sarjana</h4>
                <p>Pendaftaran dan informasi sidang sarjana</p>
            </div>
            <div class="feature-card">
                <i class="bi bi-envelope-paper"></i>
                <h4>MSIB</h4>
                <p>Pengurusan Surat Rekomendasi Program MSIB</p>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="infoModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="kategori-tanggal">
                            <div class="kategori-info">
                                <i class="bi bi-folder"></i>
                                <span id="infoKategori"></span>
                            </div>
                            <div class="tanggal-info">
                                <i class="bi bi-calendar3"></i>
                                <span id="infoDate"></span>
                            </div>
                        </div>
                        <div id="infoContent"></div>
                        <div class="social-share">
                            <button class="share-button facebook-share" onclick="shareToFacebook()">
                                <i class="bi bi-facebook"></i>
                            </button>
                            <button class="share-button twitter-share" onclick="shareToTwitter()">
                                <i class="bi bi-twitter"></i>
                            </button>
                            <button class="share-button whatsapp-share" onclick="shareToWhatsApp()">
                                <i class="bi bi-whatsapp"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Konten utama tetap sama -->
        <div class="container main-content">
            <!-- Welcome Banner dan Feature Cards tetap sama -->

            <!-- Information Section -->
            <div class="info-section">
                <h3>Portal Berita</h3>
                <?php foreach ($informasi as $info) {
                    $isNew = (new DateTime($info['tanggal']) > new DateTime($newThreshold));
                    ?>
                    <div class="info-card">
                        <div class="info-header">
                            <i class="bi bi-person-circle"></i>
                            <h5>
                                <?= htmlspecialchars($info['judul']); ?>
                                <?php if ($isNew) { ?>
                                    <span class="badge-new">New</span>
                                <?php } ?>
                            </h5>
                        </div>
                        <div class="info-content">
                            <p><?= htmlspecialchars(substr($info['konten'], 0, 100)); ?>...</p>
                        </div>
                        <div class="info-footer">
                            <span class="info-date">
                                <i class="bi bi-calendar3"></i>
                                <?= date('d M Y', strtotime($info['tanggal'])); ?>
                            </span>
                            <button class="read-more" data-bs-toggle="modal" data-bs-target="#infoModal"
                                data-info-id="<?= $info['id']; ?>"
                                data-info-title="<?= htmlspecialchars($info['judul']); ?>"
                                data-info-content="<?= htmlspecialchars($info['konten']); ?>"
                                data-info-date="<?= date('d M Y', strtotime($info['tanggal'])); ?>"
                                data-info-kategori="<?= htmlspecialchars($info['kategori']); ?>">
                                Selengkapnya <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <script src="../assets/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const infoModal = document.getElementById('infoModal');

                infoModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const title = button.getAttribute('data-info-title');
                    const content = button.getAttribute('data-info-content');
                    const date = button.getAttribute('data-info-date');
                    const kategori = button.getAttribute('data-info-kategori');

                    const modalTitle = infoModal.querySelector('.modal-title');
                    const modalContent = infoModal.querySelector('#infoContent');
                    const modalDate = infoModal.querySelector('#infoDate');
                    const modalKategori = infoModal.querySelector('#infoKategori');

                    modalTitle.textContent = title;
                    modalContent.textContent = content;
                    modalDate.textContent = date;
                    modalKategori.textContent = kategori;
                });
            });

            // Fungsi share
            function shareToFacebook() {
                const title = document.querySelector('.modal-title').textContent;
                const url = window.location.href;
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(title)}`, '_blank');
            }

            function shareToTwitter() {
                const title = document.querySelector('.modal-title').textContent;
                const url = window.location.href;
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, '_blank');
            }

            function shareToWhatsApp() {
                const title = document.querySelector('.modal-title').textContent;
                const url = window.location.href;
                window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
            }
        </script>
</body>

</html>