<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npm = $_POST['npm'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = $_POST['nama_lengkap'];
    $krsFile = $_FILES['krs']['name'];
    $krsTmp = $_FILES['krs']['tmp_name'];
    $uploadDir = 'uploads/krs/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Validate NPM format and length
    if (strlen($npm) != 8 || !is_numeric($npm)) {
        $error = "NPM harus berupa 8 digit angka.";
    } else {
        // Check if NPM already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE npm = ?");
        $stmt->execute([$npm]);
        $npmExists = $stmt->fetchColumn() > 0;

        if ($npmExists) {
            $error = "NPM sudah terdaftar.";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE email = ?");
            $stmt->execute([$email]);
            $emailExists = $stmt->fetchColumn() > 0;

            if ($emailExists) {
                $error = "Email sudah terdaftar.";
            } else {
                $fileExtension = pathinfo($krsFile, PATHINFO_EXTENSION);
                $allowedExtensions = ['pdf'];

                if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
                    $error = "Hanya file PDF yang diizinkan.";
                } else {
                    $newFileName = "krs_{$npm}." . $fileExtension;
                    $krsPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($krsTmp, $krsPath)) {
                        $stmt = $pdo->prepare("INSERT INTO mahasiswa (npm, email, password, nama_lengkap, krs) VALUES (?, ?, ?, ?, ?)");
                        if ($stmt->execute([$npm, $email, $password, $nama_lengkap, $newFileName])) {
                            $_SESSION['success_message'] = "Registrasi berhasil, silakan tunggu verifikasi oleh admin.";
                            header("Location: register.php");
                            exit;
                        } else {
                            $error = "Registrasi gagal, coba lagi!";
                        }
                    } else {
                        $error = "Gagal mengunggah KRS.";
                    }
                }
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
    <title>Register - Sistem Administrasi</title>
    <link rel="icon" href="assets\images\logo Gunadarma.png" type="image/png">
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

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            width: 100%;
            max-width: 400px;
            margin: 20px 0;
        }

        .register-header {
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

        .register-button {
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

        .register-button:hover {
            background-color: #0044CC;
        }

        .alert {
            margin-top: 5px;
            margin-bottom: 5px;
            padding: 5px;
            font-size: 0.8rem;
        }

        input[type="file"] {
            padding: 0.3rem;
            background-color: #fff;
            font-size: 0.8rem;
            height: 35px;
        }

        input[type="file"]::-webkit-file-upload-button {
            padding: 0.3rem 0.8rem;
            background-color: #0055FF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 0.5rem;
            font-size: 0.8rem;
            transition: background-color 0.3s ease;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background-color: #0044CC;
        }

        .login-link {
            font-size: 0.85rem;
            margin-top: 1rem;
            text-align: center;
        }

        .login-link a {
            color: #0055FF;
            text-decoration: none;
            font-weight: 500;
        }

        .modal-content {
            border-radius: 10px;
        }

        .modal-header {
            background-color: #0055FF;
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .modal-footer {
            border-top: none;
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

    <div class="container">
        <div class="register-card">
            <div class="register-header">Register</div>
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>
            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="npm">NPM</label>
                    <input type="text" class="form-control" id="npm" name="npm" placeholder="Masukkan NPM Anda"
                        required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda"
                        required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan password Anda" required>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                        placeholder="Masukkan nama lengkap Anda" required>
                </div>
                <div class="form-group">
                    <label for="krs">KRS (PDF)</label>
                    <input type="file" class="form-control" id="krs" name="krs" accept=".pdf" required>
                </div>
                <button type="submit" class="register-button">Register</button>
                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pop-Up -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Registrasi Berhasil</h5>
                </div>
                <div class="modal-body">
                    Akun Anda akan diperiksa dalam waktu maksimal 3 hari oleh staff yang bertugas.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="closeModal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Form validation
        function validateForm() {
            const npm = document.getElementById('npm').value;
            const password = document.getElementById('password').value;
            const file = document.getElementById('krs').files[0];

            // Validate NPM (numbers only)
            if (!/^\d+$/.test(npm)) {
                alert('NPM hanya boleh berisi angka.');
                return false;
            }

            // Validate password length
            if (password.length < 6) {
                alert('Password harus minimal 6 karakter.');
                return false;
            }

            // Validate file type
            if (file) {
                const fileType = file.type;
                if (fileType !== 'application/pdf') {
                    alert('File KRS harus berformat PDF.');
                    return false;
                }
            }

            return true;
        }

        // Show success modal if registration successful
        <?php if (isset($_SESSION['success_message'])) { ?>
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();

            <?php unset($_SESSION['success_message']); ?>

            setTimeout(function () {
                window.location.href = 'login.php';
            }, 30000);
        <?php } ?>

        // Handle modal close button
        document.getElementById('closeModal').addEventListener('click', function () {
            window.location.href = 'login.php';
        });

        // Initialize slideshow when page loads
        document.addEventListener('DOMContentLoaded', initializeSlideshow);
    </script>
</body>

</html>