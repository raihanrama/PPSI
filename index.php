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
    <link rel="icon" href="assets\images\logo Gunadarma.png" type="image/png">
    <link rel="stylesheet" href="style.css">
</head>
<style>
    /* Reset and Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        background-color: #f5f5f5;
        min-height: 100vh;
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

    /* Hamburger Menu Styles */
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
        transition: all 0.3s ease-in-out;
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

    /* Main Content Styles */
    .main-content {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .title {
        text-align: center;
        color: #333;
        font-size: 2rem;
        margin-bottom: 2rem;
    }

    .npm-form {
        background-color: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    .form-title {
        color: #333;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .npm-input {
        width: 100%;
        padding: 0.8rem;
        margin-bottom: 1rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .npm-input:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.2);
    }

    .submit-btn {
        width: 100%;
        padding: 0.8rem;
        background-color: #0066cc;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .submit-btn:hover {
        background-color: #0052a3;
    }

    /* News Section Styles */
    .news-section {
        max-width: 1200px;
        margin: 3rem auto;
        padding: 0 1rem;
    }

    .section-title {
        font-size: 2rem;
        font-weight: bold;
        color: white;
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eee;
    }

    .news-item {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .news-item:hover {
        transform: translateY(-3px);
    }

    .news-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .news-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .news-tag {
        background: #ff0000;
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .news-title {
        color: #333;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.1rem;
        flex: 1;
    }

    .news-category {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .news-date {
        color: #888;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .news-content {
        color: #444;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .read-more {
        background: #0066cc;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
    }

    .read-more:hover {
        background: #0052a3;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 1100;
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(5px);
    }

    .modal.show {
        opacity: 1;
    }

    .modal-content {
        background: white;
        width: 90%;
        max-width: 800px;
        margin: 2rem auto;
        border-radius: 12px;
        position: relative;
        transform: translateY(-20px) scale(0.95);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal.show .modal-content {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
        position: relative;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        color: #2d3748;
        font-weight: 600;
    }

    .close {
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #ddd;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .close:hover {
        background: #f3f4f6;
        transform: translateY(-50%) scale(1.1);
    }

    .close::before,
    .close::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 2px;
        background: #4a5568;
        border-radius: 1px;
    }

    .close::before {
        transform: rotate(45deg);
    }

    .close::after {
        transform: rotate(-45deg);
    }

    .modal-meta {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .modal-category,
    .modal-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #666;
        font-size: 0.9rem;
    }

    .modal-body {
        padding: 1.5rem;
        max-height: calc(80vh - 180px);
        overflow-y: auto;
        color: #4a5568;
        line-height: 1.6;
    }

    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #eee;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .share-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .share-btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        color: white;
    }

    .share-btn.facebook {
        background: #1877f2;
    }

    .share-btn.twitter {
        background: #1da1f2;
    }

    .share-btn.whatsapp {
        background: #25d366;
    }

    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    /* Loading animation */
    .modal-loading {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        justify-content: center;
        align-items: center;
        z-index: 1;
    }

    .modal-loading.show {
        display: flex;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 1rem auto;
        }

        .modal-header h2 {
            font-size: 1.2rem;
        }

        .modal-meta {
            flex-direction: column;
            gap: 0.5rem;
        }

        .share-buttons {
            flex-wrap: wrap;
        }

        .share-btn {
            flex: 1;
            min-width: 100px;
            justify-content: center;
        }
    }

    /* Responsive Breakpoints */
    @media (max-width: 768px) {
        .navbar {
            flex-direction: column;
            padding: 1rem;
        }

        .hamburger {
            display: flex;
        }


        .logo-container {
            margin-bottom: 1rem;
        }

        .logo-image {
            height: 30px;
        }

        .brand-text {
            font-size: 1rem;
        }

        .nav-links {
            width: 100%;
            justify-content: center;
            padding: 0.5rem 0;
        }

        .title {
            font-size: 1.5rem;
            margin: 1rem 0;
        }

        .news-header {
            flex-wrap: wrap;
        }

        .news-title {
            width: 100%;
            margin-top: 0.5rem;
        }

        .modal-content {
            width: 95%;
            margin: 1rem auto;
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

    @media (max-width: 480px) {
        .nav-links {
            flex-direction: column;
            gap: 0.5rem;
        }

        .login-btn {
            width: 100%;
            text-align: center;
        }

        .news-avatar {
            width: 30px;
            height: 30px;
        }

        .news-tag {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }

        .section-title {
            font-size: 1.5rem;
        }

        .hamburger {
            scale: 0.9;
        }

    }

    .fade-in {
        animation: fadeIn 0.3s ease-in forwards;
    }

    .fade-out {
        animation: fadeOut 0.3s ease-out forwards;
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

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }

    /* Print Styles */
    @media print {

        .navbar,
        .npm-form,
        .modal,
        .read-more {
            display: none;
        }

        .news-section {
            margin: 0;
            padding: 0;
        }

        .news-item {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
</style>

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

    <main class="main-content">
        <h1 class="title">Sistem Administrasi Program Studi</h1>
        <div class="npm-form">
            <h2 class="form-title">Masukkan NPM</h2>
            <form method="POST">
                <input type="text" name="npm" class="npm-input" placeholder="NPM Anda" required>
                <button type="submit" class="submit-btn">Cek Status PI</button>
            </form>
            <?php if (!empty($error)): ?>
                <div class="error-message" style="color: red; margin-top: 10px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- News Section -->
    <div class="news-section">
        <h2 class="section-title">Portal Berita</h2>

        <?php
        // Fetch news items for "Portal Berita"
        try {
            $stmt = $pdo->query("SELECT id, judul, konten, tanggal, kategori FROM informasi ORDER BY tanggal DESC");
            $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error fetching news: " . $e->getMessage();
        }
        ?>

        <?php if (!empty($newsItems)): ?>
            <?php foreach ($newsItems as $news): ?>
                <div class="news-item">
                    <div class="news-header">
                        <img src="assets/images/notification.png" alt="Notification Avatar" class="news-avatar">
                        <span class="news-tag">TERBARU</span>
                        <a href="#" class="news-title"><?php echo htmlspecialchars($news['judul']); ?></a>
                    </div>
                    <div class="news-category">Kategori: <?php echo htmlspecialchars($news['kategori']); ?></div>
                    <div class="news-date">Posted on <?php echo date('d F Y', strtotime($news['tanggal'])); ?></div>
                    <p class="news-content"><?php echo htmlspecialchars(substr($news['konten'], 0, 100)); ?>...</p>
                    <button class="read-more" data-title="<?php echo htmlspecialchars($news['judul'], ENT_QUOTES); ?>"
                        data-category="<?php echo htmlspecialchars($news['kategori'], ENT_QUOTES); ?>"
                        data-date="<?php echo htmlspecialchars(date('d F Y', strtotime($news['tanggal'])), ENT_QUOTES); ?>"
                        data-content="<?php echo htmlspecialchars($news['konten'], ENT_QUOTES); ?>" onclick="openModal(this)">
                        Selengkapnya
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Tidak ada berita tersedia saat ini.</p>
        <?php endif; ?>
    </div>

    <!-- Modal Structure -->
    <div id="newsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"></h2>
                <button class="close" aria-label="Close modal">&times;</button>
                <div class="modal-meta">
                    <div class="modal-category" id="modalCategory">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 4h12M2 8h12M2 12h12" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                        <span></span>
                    </div>
                    <div class="modal-date" id="modalDate">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="2" y="3" width="12" height="11" rx="2" stroke="currentColor" stroke-width="2" />
                            <path d="M2 7h12" stroke="currentColor" stroke-width="2" />
                            <path d="M6 2v2M10 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        <span></span>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <p id="modalContent"></p>
            </div>
            <div class="modal-footer">
                <div class="share-buttons">
                    <button onclick="shareNews('facebook')" class="share-btn facebook">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z" />
                        </svg>
                        Share
                    </button>
                    <button onclick="shareNews('twitter')" class="share-btn twitter">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z" />
                        </svg>
                        Tweet
                    </button>
                    <button onclick="shareNews('whatsapp')" class="share-btn whatsapp">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        Share
                    </button>
                </div>
            </div>
            <div class="modal-loading">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize modal functionality
            initModal();

            // Initialize mobile menu
            initMobileMenu();
        });

        function initModal() {
            // Add loading div to modal if not exists
            const modal = document.getElementById('newsModal');
            if (!modal.querySelector('.modal-loading')) {
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'modal-loading';
                loadingDiv.innerHTML = '<div class="spinner"></div>';
                modal.querySelector('.modal-content').appendChild(loadingDiv);
            }

            // Close modal handlers
            setupCloseHandlers();
        }

        function setupCloseHandlers() {
            // Close on escape key
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            // Close on outside click
            window.onclick = function (event) {
                const modal = document.getElementById('newsModal');
                if (event.target === modal) {
                    closeModal();
                }
            };

            // Close on button click
            const closeButton = document.querySelector('.close');
            if (closeButton) {
                closeButton.addEventListener('click', closeModal);
            }
        }

        function openModal(button) {
            const modal = document.getElementById('newsModal');
            if (!modal) {
                console.error('Modal element not found');
                return;
            }

            // Show loading state
            const loadingDiv = modal.querySelector('.modal-loading');
            if (loadingDiv) {
                loadingDiv.classList.add('show');
            }

            // Get data from button attributes
            const title = button.getAttribute('data-title');
            const category = button.getAttribute('data-category');
            const date = button.getAttribute('data-date');
            const content = button.getAttribute('data-content');

            // Simulate loading delay for better UX
            setTimeout(() => {
                // Set modal content
                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalCategory').querySelector('span').textContent = category;
                document.getElementById('modalDate').querySelector('span').textContent = date;
                document.getElementById('modalContent').textContent = content;

                // Hide loading state
                if (loadingDiv) {
                    loadingDiv.classList.remove('show');
                }

                // Show modal with animation
                modal.style.display = 'block';
                requestAnimationFrame(() => {
                    modal.classList.add('show');
                });

                // Disable body scroll
                document.body.style.overflow = 'hidden';
            }, 500); // 500ms delay for loading animation
        }

        function closeModal() {
            const modal = document.getElementById('newsModal');
            if (!modal) {
                console.error('Modal element not found');
                return;
            }

            // Hide modal with animation
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                // Reset loading state
                const loadingDiv = modal.querySelector('.modal-loading');
                if (loadingDiv) {
                    loadingDiv.classList.remove('show');
                }
                // Re-enable body scroll
                document.body.style.overflow = 'auto';
            }, 300);
        }

        function shareNews(platform) {
            const title = document.getElementById('modalTitle').textContent;
            const url = window.location.href;

            // Show loading state
            const loadingDiv = document.querySelector('.modal-loading');
            if (loadingDiv) {
                loadingDiv.classList.add('show');
            }

            // Simulate share process
            setTimeout(() => {
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
                    default:
                        console.error('Invalid sharing platform');
                        if (loadingDiv) loadingDiv.classList.remove('show');
                        return;
                }

                // Hide loading state
                if (loadingDiv) {
                    loadingDiv.classList.remove('show');
                }

                // Open share window
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }, 300);
        }

        function initMobileMenu() {
            const hamburger = document.querySelector('.hamburger');
            const navLinks = document.querySelector('.nav-links');
            let isMenuOpen = false;

            if (hamburger && navLinks) {
                hamburger.addEventListener('click', toggleMenu);

                // Close menu when clicking outside
                document.addEventListener('click', (e) => {
                    if (isMenuOpen && !navLinks.contains(e.target) && !hamburger.contains(e.target)) {
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
            }

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
        }
    </script>