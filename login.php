<?php
session_start();
if (isset($_SESSION['login_message'])) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            ' . $_SESSION['login_message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['login_message']); // Hapus pesan setelah ditampilkan
}
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npm = $_POST['npm'];
    $password = $_POST['password'];

    // First, check if this is an admin account
    $stmt = $pdo->prepare("SELECT * FROM users WHERE npm = ?");
    $stmt->execute([$npm]);
    $user = $stmt->fetch();

    if ($user) {
        // This is an admin account - no NPM format validation needed
        if (password_verify($password, $user['password'])) {
            $_SESSION['npm'] = $user['npm'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'super_admin':
                    header("Location: admin/super_admin.php");
                    break;
                case 'admin_pi':
                    header("Location: admin/admin_pi.php");
                    break;
                case 'admin_sidang':
                    header("Location: admin/admin_sidang.php");
                    break;
                case 'admin_msib':
                    header("Location: admin/admin_msib.php");
                    break;
                default:
                    $error = "Role tidak valid!";
                    break;
            }
            exit;
        } else {
            $error = "Password Anda salah!";
        }
    } else {
        // This might be a student account - validate NPM format
        if (!ctype_digit($npm)) {
            $error = "NPM mahasiswa hanya boleh berupa angka.";
        } else {
            // Check student table
            $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE npm = ?");
            $stmt->execute([$npm]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    if ($user['status_verifikasi'] == 'Diverifikasi') {
                        $_SESSION['npm'] = $user['npm'];
                        $_SESSION['role'] = 'mahasiswa';
                        header("Location: mahasiswa/mahasiswa.php");
                        exit;
                    } elseif ($user['status_verifikasi'] == 'Ditolak') {
                        $_SESSION['npm'] = $user['npm'];
                        $_SESSION['role'] = 'mahasiswa';
                        header("Location: perbaiki_akun.php?npm={$user['npm']}");
                        exit;
                    } else {
                        $error = "Akun Anda belum diverifikasi oleh admin. Silakan tunggu persetujuan.";
                    }
                } else {
                    $error = "Password Anda salah!";
                }
            } else {
                $error = "Login gagal! Coba periksa NPM dan password Anda.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Administrasi</title>
    <link rel="icon" href="assets\images\logo Gunadarma.png" type=" ">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .slideshow {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            background-size: cover;
            background-position: center;
            animation: zoom 20s infinite;
        }

        .slide.active {
            opacity: 1;
        }

        .slide:nth-child(1) {
            background-image: linear-gradient(rgba(5, 10, 40, 0.3), rgba(5, 10, 40, 0.3)),
                url('assets/images/Gunadarma.png');
        }

        .slide:nth-child(2) {
            background-image: linear-gradient(rgba(5, 10, 40, 0.3), rgba(5, 10, 40, 0.3)),
                url('assets/images/Gedung 4 Gunadarma-thumbnail.png');
        }

        .slide:nth-child(3) {
            background-image: linear-gradient(rgba(5, 10, 40, 0.3), rgba(5, 10, 40, 0.3)),
                url('assets/images/3267131452.png');
        }

        .slide:nth-child(4) {
            background-image: linear-gradient(rgba(5, 10, 40, 0.3), rgba(5, 10, 40, 0.3)),
                url('assets/images/K1008_DEX_view-02.png');
        }

        @keyframes zoom {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 1rem;
            color: #0055FF;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 0.8rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.2rem;
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
            height: 35px;
        }

        .form-control:focus {
            outline: none;
            border-color: #0055FF;
            box-shadow: 0 0 0 2px rgba(0, 85, 255, 0.1);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #0055FF;
        }

        .login-button {
            width: 100%;
            padding: 0.5rem;
            background-color: #0055FF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            height: 35px;
            margin-top: 0.5rem;
        }

        .login-button:hover {
            background-color: #0044CC;
        }

        .alert {
            margin-top: 5px;
            margin-bottom: 5px;
            padding: 5px;
            font-size: 0.8rem;
        }

        .text-center {
            text-align: center;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        a {
            color: #0055FF;
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline !important;
        }

        .forgot-password {
            font-size: 0.85rem;
            margin-top: 0.8rem;
            text-align: center;
        }

        .register-link {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            text-align: center;
            color: #333;
        }

        #notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 15px 30px 15px 15px;
            border-radius: 8px;
            color: white;
            display: none;
            max-width: 500px;
            width: 90%;
            text-align: left;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        #notification .notification-content {
            display: flex;
            align-items: center;
        }

        #notification .notification-icon {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        #notification .notification-text {
            flex-grow: 1;
        }

        #notification .close-notification {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            color: white;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        #notification .close-notification:hover {
            opacity: 1;
        }

        #notification.success {
            background-color: #28a745;
        }

        #notification.error {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="background-container">
        <div class="slideshow">
            <div class="slide active"></div>
            <div class="slide"></div>
            <div class="slide"></div>
            <div class="slide"></div>
        </div>
    </div>
    <div id="notification"></div>

    <div class="container">
        <div class="login-card">
            <div class="login-header">Login</div>
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="npm">NPM</label>
                    <input type="text" class="form-control" id="npm" name="npm" placeholder="Masukkan NPM" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan Password" required>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" class="login-button">Login</button>

                <div class="forgot-password">
                    <a href="forgot_password.php">Lupa Password?</a>
                </div>

                <div class="register-link">
                    <p class="mb-0">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Slideshow functionality
        let slides = document.querySelectorAll('.slide');
        let currentSlide = 0;

        function initializeSlideshow() {
            setInterval(nextSlide, 5000); // Change slide every 5 seconds
        }

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Notification functionality
        const flashMessage = <?php echo json_encode(isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : ''); ?>;
        const flashType = <?php echo json_encode(isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : ''); ?>;
        const notificationElement = document.getElementById('notification');

        if (flashMessage) {
            const notificationContent = `
                <div class="notification-content">
                    <i class="notification-icon fas ${flashType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <div class="notification-text">
                        <strong>Registrasi Berhasil</strong><br>
                        ${flashMessage}
                    </div>
                    <span class="close-notification" onclick="closeNotification()">
                        <i class="fas fa-times"></i>
                    </span>
                </div>
            `;

            notificationElement.innerHTML = notificationContent;
            notificationElement.classList.add(flashType);
            notificationElement.style.display = 'block';

            // Auto hide notification after 5 seconds
            setTimeout(() => {
                closeNotification();
            }, 5000);
        }

        function closeNotification() {
            notificationElement.style.display = 'none';
        }

        // Initialize slideshow when page loads
        document.addEventListener('DOMContentLoaded', initializeSlideshow);
    </script>
</body>

</html>