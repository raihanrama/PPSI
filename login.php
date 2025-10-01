<?php
session_start();
if (isset($_SESSION['login_message'])) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            ' . $_SESSION['login_message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['login_message']);
}
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npm = $_POST['npm'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE npm = ?");
    $stmt->execute([$npm]);
    $user = $stmt->fetch();

    if ($user) {
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
        if (!ctype_digit($npm)) {
            $error = "NPM mahasiswa hanya boleh berupa angka.";
        } else {
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            overflow-x: hidden;
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
            transition: opacity 2s ease-in-out;
            background-size: cover;
            background-position: center;
            animation: zoom 25s infinite;
        }

        .slide.active {
            opacity: 1;
        }

        .slide:nth-child(1) {
            background-image: linear-gradient(135deg, rgba(0, 85, 255, 0.4), rgba(5, 10, 40, 0.6)),
                url('assets/images/Gunadarma.png');
        }

        .slide:nth-child(2) {
            background-image: linear-gradient(135deg, rgba(0, 85, 255, 0.4), rgba(5, 10, 40, 0.6)),
                url('assets/images/Gedung 4 Gunadarma-thumbnail.png');
        }

        .slide:nth-child(3) {
            background-image: linear-gradient(135deg, rgba(0, 85, 255, 0.4), rgba(5, 10, 40, 0.6)),
                url('assets/images/3267131452.png');
        }

        .slide:nth-child(4) {
            background-image: linear-gradient(135deg, rgba(0, 85, 255, 0.4), rgba(5, 10, 40, 0.6)),
                url('assets/images/K1008_DEX_view-02.png');
        }

        @keyframes zoom {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 1200px;
            gap: 60px;
        }

        .login-info {
            flex: 1;
            color: white;
            text-align: left;
            display: none;
        }

        .login-info h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }

        .login-info p {
            font-size: 1.2rem;
            opacity: 0.95;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
            line-height: 1.6;
        }

        @media (min-width: 992px) {
            .login-info {
                display: block;
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 50px 45px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #0055FF, #00BFFF, #0055FF);
            background-size: 200% 100%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h2 {
            color: #1a1a1a;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #0055FF;
            background: white;
            box-shadow: 0 0 0 4px rgba(0, 85, 255, 0.1);
        }

        .form-control:focus ~ .input-icon {
            color: #0055FF;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
            z-index: 10;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            color: #0055FF;
        }

        .login-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0055FF, #0044CC);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0, 85, 255, 0.3);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 85, 255, 0.4);
            background: linear-gradient(135deg, #0044CC, #003399);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .alert {
            margin-bottom: 25px;
            padding: 14px 16px;
            font-size: 0.9rem;
            border-radius: 10px;
            border: none;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }

        .divider span {
            padding: 0 15px;
            color: #999;
            font-size: 0.85rem;
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: #0055FF;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .forgot-password a:hover {
            color: #0044CC;
            text-decoration: underline;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }

        .register-link p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
        }

        .register-link a {
            color: #0055FF;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .register-link a:hover {
            color: #0044CC;
            text-decoration: underline;
        }

        #notification {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 1000;
            padding: 20px 50px 20px 20px;
            border-radius: 12px;
            color: white;
            display: none;
            max-width: 450px;
            width: auto;
            min-width: 300px;
            text-align: left;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideInRight 0.5s ease;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        #notification .notification-content {
            display: flex;
            align-items: flex-start;
        }

        #notification .notification-icon {
            margin-right: 15px;
            font-size: 1.8rem;
            flex-shrink: 0;
        }

        #notification .notification-text {
            flex-grow: 1;
        }

        #notification .notification-text strong {
            display: block;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        #notification .close-notification {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            color: white;
            opacity: 0.8;
            transition: opacity 0.3s;
            font-size: 1.2rem;
        }

        #notification .close-notification:hover {
            opacity: 1;
        }

        #notification.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        #notification.error {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 35px 30px;
            }

            .login-header h2 {
                font-size: 1.75rem;
            }

            #notification {
                top: 20px;
                right: 20px;
                left: 20px;
                max-width: none;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 25px;
                border-radius: 20px;
            }

            .login-header h2 {
                font-size: 1.5rem;
            }

            .form-control {
                padding: 12px 14px 12px 44px;
            }

            .login-button {
                padding: 13px;
            }
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
        <div class="login-wrapper">
            <div class="login-info">
                <h1>Selamat Datang di<br>Sistem Administrasi<br>Universitas Gunadarma</h1>
                <p>Masuk ke akun Anda untuk mengakses layanan akademik dan administrasi</p>
            </div>
            
            <div class="login-card">
                <div class="login-header">
                    <h2>Masuk</h2>
                    <p>Silakan login dengan akun Anda</p>
                </div>
                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php } ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="npm">NPM</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" class="form-control" id="npm" name="npm" placeholder="Masukkan NPM Anda" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <div class="password-container">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Masukkan Password Anda" required>
                                <span class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="togglePassword"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="login-button">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </button>

                    <div class="forgot-password">
                        <a href="forgot_password.php"><i class="fas fa-key"></i> Lupa Password?</a>
                    </div>

                    <div class="register-link">
                        <p>Belum punya akun? <a href="register.php">Daftar di sini <i class="fas fa-arrow-right"></i></a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script>
        let slides = document.querySelectorAll('.slide');
        let currentSlide = 0;

        function initializeSlideshow() {
            setInterval(nextSlide, 6000);
        }

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

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

            setTimeout(() => {
                closeNotification();
            }, 5000);
        }

        function closeNotification() {
            notificationElement.style.opacity = '0';
            setTimeout(() => {
                notificationElement.style.display = 'none';
                notificationElement.style.opacity = '1';
            }, 300);
        }

        document.addEventListener('DOMContentLoaded', initializeSlideshow);
    </script>
</body>

</html>

