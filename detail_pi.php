<?php
// Memulai session
session_start();
require 'config.php';

// Cek apakah parameter NPM ada
if (!isset($_GET['npm'])) {
    header("Location: index.php");
    exit;
}

$npm = $_GET['npm'];

// Mengambil data penelitian mahasiswa berdasarkan NPM
$stmt = $pdo->prepare("SELECT * FROM penelitian WHERE npm = ?");
$stmt->execute([$npm]);
$mahasiswa = $stmt->fetch();

if (!$mahasiswa) {
    echo "Data Penelitian Ilmiah tidak ditemukan.";
    exit;
}

// Ambil data dari tabel penelitian (sesuai struktur tabel)
$status = $mahasiswa['status'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <title>Detail Penelitian Ilmiah</title>
    <link rel="icon" href="assets\images\logo Gunadarma.png" type="image/png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-family: Arial, sans-serif;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #ffffff;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-image {
            height: 40px;
            width: auto;
        }

        .brand-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }

        /* Navigation Links */
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 1rem;
        }

        .nav-links a:hover {
            color: #0066cc;
        }

        .login-btn {
            color: black;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #0052a3;
            color: white;
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 25px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
        }

        .hamburger span {
            width: 30px;
            height: 3px;
            background: #333;
            border-radius: 10px;
            transition: all 0.3s linear;
            position: relative;
            transform-origin: 1px;
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }

            .hamburger {
                display: flex;
                position: absolute;
                top: 1rem;
                right: 1rem;
            }

            .logo-container {
                margin-bottom: 1rem;
            }

            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                padding: 1rem 0;
            }

            .nav-links.active {
                display: flex;
            }

            .steps-container {
                gap: 15px;
            }

            .step::before {
                width: 20px;
            }

            .titlestep,
            .titlestep1,
            .titlesteph1 {
                font-size: 1.4rem;
            }

            .subtitle,
            .subtitlestep {
                font-size: 1rem;
            }

            /* Hamburger Animation */
            .hamburger.active span:nth-child(1) {
                transform: rotate(45deg) translate(0px, 11px);
            }

            .hamburger.active span:nth-child(2) {
                opacity: 0;
                transform: translateX(-20px);
            }

            .hamburger.active span:nth-child(3) {
                transform: rotate(-45deg) translate(0px, -11px);
            }
        }

        .card {
            border-radius: 5px;
            box-shadow: rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card-header {
            background: #4b0082;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }

        .card-header h4 {
            font-weight: bold;
            border-radius: 15px;
            margin: 0;
            color: white;
        }

        .btn-secondary {
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: background 0.3s;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        /* Animasi teks */
        .text-animate {
            animation: slideIn 1s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About - Sistem Administrasi</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <nav class="navbar">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo Universitas" class="logo-image">
                <span class="brand-text">SISTEM ADMINISTRASI</span>
                <link rel="stylesheet" href="style.css">
            </div>
            <button class="hamburger" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="login.php" class="login-btn">Login</a>
            </div>
        </nav>

        <div class="container mt-5 text-animate">
            <h1 class="text-center">Detail Penelitian Ilmiah Mahasiswa</h1>
            <div class="card mt-4">
                <div class="card-header">
                    <h4>Informasi Penelitian Ilmiah</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered text-white">
                        <tr>
                            <th>NPM</th>
                            <td><?php echo htmlspecialchars($mahasiswa['npm']); ?></td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td><?php echo htmlspecialchars($mahasiswa['nama']); ?></td>
                        </tr>
                        <tr>
                            <th>Status PI Anda</th>
                            <td><?php echo htmlspecialchars($status); ?></td>
                        </tr>
                        <!-- Tambahkan baris informasi jika status PI selesai -->
                        <?php if ($status === 'Selesai'): ?>
                            <tr>
                                <th>Informasi Pengambilan</th>
                                <td>
                                    Pengambilan PI dapat dilakukan di <strong>Ruang D422</strong>. <br>
                                    <span class="text-muted">Jam Operasional: Senin - Jumat, 10:00 - 15:00</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div class="enhanced-progress-container mt-4">
                <!-- Timeline Progress -->
                <div class="timeline-progress">
                    <div class="progress-steps">
                        <!-- Step 1 -->
                        <div class="progress-step <?php echo $status === 'Diproses' ? 'active' : ($status === 'Menuju Penandatanganan' || $status === 'Selesai' ? 'completed' : ''); ?>"
                            data-tooltip="Dokumen sedang dalam proses review">
                            <div class="step-icon-wrapper">
                                <div class="step-icon">
                                    <i
                                        class="fas fa-file-alt <?php echo $status === 'Diproses' ? 'fa-bounce' : ''; ?>"></i>
                                </div>
                                <div class="progress-line"></div>
                            </div>
                            <div class="step-content">
                                <h4>Diproses</h4>
                                <p>Dokumen dalam tahap review</p>
                                <?php if ($status === 'Diproses'): ?>
                                    <div class="step-details">
                                        <div class="processing-animation">
                                            <div class="dot-pulse"></div>
                                        </div>
                                        <span class="status-badge">Sedang Diproses</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="progress-step <?php echo $status === 'Menuju Penandatanganan' ? 'active' : ($status === 'Selesai' ? 'completed' : ''); ?>"
                            data-tooltip="Dokumen siap untuk ditandatangani">
                            <div class="step-icon-wrapper">
                                <div class="step-icon">
                                    <i
                                        class="fas fa-file-signature <?php echo $status === 'Menuju Penandatanganan' ? 'fa-shake' : ''; ?>"></i>
                                </div>
                                <div class="progress-line"></div>
                            </div>
                            <div class="step-content">
                                <h4>Penandatanganan</h4>
                                <p>Menunggu tanda tangan</p>
                                <?php if ($status === 'Menuju Penandatanganan'): ?>
                                    <div class="step-details">
                                        <div class="signing-animation">
                                            <i class="fas fa-pen-fancy fa-bounce"></i>
                                        </div>
                                        <span class="status-badge warning">Menunggu Tanda Tangan</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="progress-step <?php echo $status === 'Selesai' ? 'active completed' : ''; ?>"
                            data-tooltip="Proses telah selesai">
                            <div class="step-icon-wrapper">
                                <div class="step-icon">
                                    <i
                                        class="fas fa-check-circle <?php echo $status === 'Selesai' ? 'fa-beat' : ''; ?>"></i>
                                </div>
                            </div>
                            <div class="step-content">
                                <h4>Selesai</h4>
                                <p>Proses telah selesai</p>
                                <?php if ($status === 'Selesai'): ?>
                                    <div class="step-details">
                                        <div class="completion-animation">
                                            <i class="fas fa-medal fa-shake"></i>
                                        </div>
                                        <span class="status-badge success">Selesai</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Summary Card -->
                <div class="status-summary">
                    <div class="status-card">
                        <div class="status-icon">
                            <?php
                            switch ($status) {
                                case 'Diproses':
                                    echo '<i class="fas fa-sync-alt fa-spin"></i>';
                                    break;
                                case 'Menuju Penandatanganan':
                                    echo '<i class="fas fa-file-signature fa-bounce"></i>';
                                    break;
                                case 'Selesai':
                                    echo '<i class="fas fa-check-circle fa-beat"></i>';
                                    break;
                            }
                            ?>
                        </div>
                        <div class="status-info">
                            <h3>Status Saat Ini</h3>
                            <p><?php echo htmlspecialchars($status); ?></p>
                        </div>
                        <div class="status-details">
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span>Update Terakhir: <?php echo date('d M Y'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Add hover effect to steps
                    const steps = document.querySelectorAll('.progress-step');
                    steps.forEach(step => {
                        step.addEventListener('mouseenter', function () {
                            if (!this.classList.contains('active') && !this.classList.contains('completed')) {
                                this.querySelector('.step-icon').style.transform = 'scale(1.1)';
                            }
                        });

                        step.addEventListener('mouseleave', function () {
                            if (!this.classList.contains('active') && !this.classList.contains('completed')) {
                                this.querySelector('.step-icon').style.transform = 'scale(1)';
                            }
                        });
                    });
                });

                document.addEventListener('DOMContentLoaded', () => {
                    const hamburger = document.querySelector('.hamburger');
                    const navLinks = document.querySelector('.nav-links');
                    let isMenuOpen = false;

                    function toggleMenu() {
                        isMenuOpen = !isMenuOpen;
                        hamburger.classList.toggle('active');
                        navLinks.classList.toggle('active');

                        if (isMenuOpen) {
                            navLinks.classList.add('fade-in');
                            navLinks.classList.remove('fade-out');
                        } else {
                            navLinks.classList.add('fade-out');
                            navLinks.classList.remove('fade-in');
                        }
                    }

                    // Toggle menu on hamburger click
                    hamburger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        toggleMenu();
                    });

                    // Close menu when clicking outside
                    document.addEventListener('click', (e) => {
                        if (isMenuOpen && !navLinks.contains(e.target)) {
                            toggleMenu();
                        }
                    });

                    // Close menu when clicking a link
                    navLinks.querySelectorAll('a').forEach(link => {
                        link.addEventListener('click', () => {
                            if (isMenuOpen) {
                                toggleMenu();
                            }
                        });
                    });

                    // Handle window resize
                    let resizeTimer;
                    window.addEventListener('resize', () => {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(() => {
                            if (window.innerWidth > 768 && isMenuOpen) {
                                toggleMenu();
                            }
                        }, 250);
                    });

                    // Prevent menu close when clicking inside nav-links
                    navLinks.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                });
            </script>

            <div class="mt-5 text-center">
                <a href="index.php" class="btn btn-secondary">Kembali ke Halaman Utama</a>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>