<?php
session_start(); // Start session
require 'config.php'; // Database connection

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npm = trim($_POST['npm']); // Remove spaces at the beginning and end

    // Validate NPM input format (optional, e.g., only digits and specific length)
    if (!preg_match('/^\d{8}$/', $npm)) {
        $error = "Format NPM tidak valid. Harus terdiri dari 8 angka.";
    } else {
        // Check if NPM exists in the database
        $stmt = $pdo->prepare("SELECT * FROM penelitian WHERE npm = ?");
        $stmt->execute([$npm]);
        $result = $stmt->fetch();

        if ($result) {
            // Redirect to detail_pi.php with npm as parameter if data is found
            header("Location: detail_pi.php?npm=" . urlencode($npm));
            exit;
        } else {
            $error = "NPM tidak ditemukan atau belum ada data Penelitian Ilmiah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Administrasi Program Studi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Navbar Modern */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.2rem 5%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-image {
            height: 45px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo-image:hover {
            transform: scale(1.05);
        }

        .brand-text {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #592693, #7439b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 1rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #592693, #7439b8);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .login-btn {
            background: linear-gradient(135deg, #592693, #7439b8);
            color: white !important;
            padding: 0.7rem 2rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(89, 38, 147, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(89, 38, 147, 0.4);
        }

        .login-btn::after {
            display: none;
        }

        /* Hero Section */
        .hero-section {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 5%;
        }

        .hero-content {
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #592693, #7439b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            animation: fadeInDown 0.8s ease;
        }

        /* NPM Check Form */
        .npm-section {
            max-width: 700px;
            margin: 0 auto 4rem;
            padding: 0 1rem;
        }

        .npm-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .npm-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(135deg, #592693, #7439b8);
        }

        .npm-card:hover {
            box-shadow: 0 20px 60px rgba(89, 38, 147, 0.15);
            transform: translateY(-5px);
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .npm-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .npm-input:focus {
            outline: none;
            border-color: #592693;
            background: white;
            box-shadow: 0 0 0 4px rgba(89, 38, 147, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #592693, #7439b8);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(89, 38, 147, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(89, 38, 147, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid #c33;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* News Section */
        .news-section {
            max-width: 1400px;
            margin: 0 auto 4rem;
            padding: 0 5%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #592693, #7439b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .news-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .news-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, #592693, #7439b8);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .news-card:hover::before {
            transform: scaleX(1);
        }

        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(89, 38, 147, 0.15);
        }

        .news-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .news-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
            background: linear-gradient(135deg, #592693, #7439b8);
            padding: 8px;
        }

        .news-badge {
            background: linear-gradient(135deg, #ff4757, #ff6b81);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(255, 71, 87, 0.3);
        }

        .news-title-link {
            flex: 1;
            color: #333;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.15rem;
            line-height: 1.4;
            transition: color 0.3s ease;
        }

        .news-title-link:hover {
            color: #592693;
        }

        .news-body {
            padding: 1.5rem;
        }

        .news-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .news-excerpt {
            color: #555;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .read-more-btn {
            background: linear-gradient(135deg, #592693, #7439b8);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(89, 38, 147, 0.2);
        }

        .read-more-btn:hover {
            transform: translateX(5px);
            box-shadow: 0 6px 20px rgba(89, 38, 147, 0.3);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            opacity: 1;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 900px;
            max-height: 85vh;
            margin: 2rem auto;
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            transform: translateY(-20px) scale(0.95);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal.show .modal-content {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .modal-header {
            padding: 2rem;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }

        .modal-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
            padding-right: 3rem;
        }

        .close-btn {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e9ecef;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: #f8f9fa;
            border-color: #592693;
            transform: rotate(90deg);
        }

        .close-btn::before,
        .close-btn::after {
            content: '';
            position: absolute;
            width: 18px;
            height: 2px;
            background: #333;
            border-radius: 2px;
        }

        .close-btn::before {
            transform: rotate(45deg);
        }

        .close-btn::after {
            transform: rotate(-45deg);
        }

        .modal-meta-info {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        .meta-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.95rem;
        }

        .modal-body {
            padding: 2rem;
            max-height: calc(85vh - 200px);
            overflow-y: auto;
            line-height: 1.8;
            color: #444;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #592693, #7439b8);
            border-radius: 4px;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #f0f0f0;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .share-buttons {
            display: flex;
            gap: 0.8rem;
        }

        .share-btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            color: white;
            font-size: 0.9rem;
        }

        .share-btn.facebook {
            background: linear-gradient(135deg, #1877f2, #0d65d9);
        }

        .share-btn.twitter {
            background: linear-gradient(135deg, #1da1f2, #0c85d0);
        }

        .share-btn.whatsapp {
            background: linear-gradient(135deg, #25d366, #1fb855);
        }

        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .hamburger span {
            width: 28px;
            height: 3px;
            background: #592693;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .news-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 2rem;
                gap: 1.5rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                display: none;
            }

            .nav-links.active {
                display: flex;
            }

            .hero-title {
                font-size: 2rem;
            }

            .npm-card {
                padding: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .news-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 1rem auto;
            }

            .modal-title {
                font-size: 1.4rem;
            }

            .share-buttons {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 1rem 4%;
            }

            .hero-title {
                font-size: 1.6rem;
            }

            .form-title {
                font-size: 1.4rem;
            }

            .npm-card {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo Universitas" class="logo-image">
                <span class="brand-text">SISTEM ADMINISTRASI</span>
            </div>
            <button class="hamburger" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="login.php" class="login-btn">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Sistem Administrasi Program Studi</h1>
        </div>

        <!-- NPM Check Form -->
        <div class="npm-section">
            <div class="npm-card">
                <h2 class="form-title">Cek Status Penelitian Ilmiah</h2>
                <form method="POST">
                    <div class="form-group">
                        <input 
                            type="text" 
                            name="npm" 
                            class="npm-input" 
                            placeholder="Masukkan NPM Anda (8 digit)" 
                            maxlength="8"
                            pattern="\d{8}"
                            required
                        >
                    </div>
                    <button type="submit" class="submit-btn">Cek Status PI</button>
                </form>
                <!-- Error message placeholder -->
                <div id="errorMessage" class="error-message" style="display: none;">
                    NPM tidak ditemukan atau belum ada data Penelitian Ilmiah.
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <div class="section-header">
            <h2 class="section-title">Portal Berita</h2>
            <p class="section-subtitle">Informasi terkini seputar kegiatan akademik</p>
        </div>

        <div class="news-grid" id="newsGrid">
            <!-- News cards will be dynamically inserted here -->
            <!-- Sample news card structure -->
            <div class="news-card">
                <div class="news-header">
                    <img src="assets/images/notification.png" alt="News" class="news-avatar">
                    <span class="news-badge">TERBARU</span>
                    <a href="#" class="news-title-link">Pengumuman Jadwal Sidang Akhir Semester</a>
                </div>
                <div class="news-body">
                    <div class="news-meta">
                        <div class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M2 4h12M2 8h12M2 12h12" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span>Akademik</span>
                        </div>
                        <div class="meta-item">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M2 7h12M6 2v2M10 2v2" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span>06 Oktober 2025</span>
                        </div>
                    </div>
                    <p class="news-excerpt">
                        Informasi penting mengenai jadwal pelaksanaan sidang akhir semester untuk mahasiswa tingkat akhir. Harap perhatikan persyaratan dan tenggat waktu...
                    </p>
                    <button class="read-more-btn" onclick="openModal(this)" 
                        data-title="Pengumuman Jadwal Sidang Akhir Semester"
                        data-category="Akademik"
                        data-date="06 Oktober 2025"
                        data-content="Informasi lengkap mengenai jadwal pelaksanaan sidang akhir semester untuk mahasiswa tingkat akhir. Sidang akan dilaksanakan mulai tanggal 15 Oktober 2025. Mahasiswa diminta untuk mempersiapkan dokumen yang diperlukan dan mengikuti prosedur pendaftaran yang telah ditentukan.">
                        Selengkapnya
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div id="newsModal" class="modal" onclick="closeModalOnOutside(event)">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle"></h2>
                <button class="close-btn" onclick="closeModal()"></button>
                <div class="modal-meta-info">
                    <div class="meta-badge" id="modalCategory">
                        <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <path d="M2 4h12M2 8h12M2 12h12" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span></span>
                    </div>
                    <div class="meta-badge" id="modalDate">
                        <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                            <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M2 7h12M6 2v2M10 2v2" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span></span>
                    </div>
                </div>
            </div>
            <div class="modal-body" id="modalContent"></div>
            <div class="modal-footer">
                <div class="share-buttons">
                    <button onclick="shareNews('facebook')" class="share-btn facebook">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z"/>
                        </svg>
                        Facebook
                    </button>
                    <button onclick="shareNews('twitter')" class="share-btn twitter">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                        </svg>
                        Twitter
                    </button>
                    <button onclick="shareNews('whatsapp')" class="share-btn whatsapp">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const hamburger = document.querySelector('.hamburger');
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('active');
        }

        function openModal(button) {
            const modal = document.getElementById('newsModal');
            const title = button.getAttribute('data-title');
            const category = button.getAttribute('data-category');
            const date = button.getAttribute('data-date');
            const content = button.getAttribute('data-content');

            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalCategory').querySelector('span').textContent = category;
            document.getElementById('modalDate').querySelector('span').textContent = date;
            document.getElementById('modalContent').textContent = content;

            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('newsModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }

        function closeModalOnOutside(event) {
            if (event.target.id === 'newsModal') {
                closeModal();
            }
        }

        function shareNews(platform) {
            const title = document.getElementById('modalTitle').textContent;
            const url = window.location.href;
            let shareUrl;

            switch (platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' ' + url)}`;
                    break;
            }

            window.open(shareUrl, '_blank', 'width=600,height=400');
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById('navLinks');
            const hamburger = document.querySelector('.hamburger');
            
            if (navLinks.classList.contains('active') && 
                !navLinks.contains(event.target) && 
                !hamburger.contains(event.target)) {
                toggleMenu();
            }
        });
    </script>
</body>
    </html>
