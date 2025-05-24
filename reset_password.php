<?php
require 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle OTP verification
    if (isset($_POST['action']) && $_POST['action'] == 'verify_otp') {
        if (isset($_POST['otp'])) {
            $otp = trim($_POST['otp']);

            // Check OTP in reset_password table
            $stmt = $pdo->prepare("SELECT * FROM reset_password WHERE token = ? AND expiration > NOW() AND used = 0");
            $stmt->execute([$otp]);
            $resetRequest = $stmt->fetch();

            if ($resetRequest) {
                $_SESSION['verified_otp'] = $otp;
                $_SESSION['npm'] = $resetRequest['npm'];
                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Kode OTP tidak valid atau telah kadaluarsa.']);
                exit();
            }
        }
    }

    // Handle password reset
    if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
        if (isset($_SESSION['verified_otp']) && isset($_POST['new_password'])) {
            $otp = $_SESSION['verified_otp'];
            $npm = $_SESSION['npm'];
            $newPassword = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

            // Update password
            $stmt = $pdo->prepare("UPDATE mahasiswa SET password = ? WHERE npm = ?");
            $stmt->execute([$newPassword, $npm]);

            // Mark OTP as used
            $stmt = $pdo->prepare("UPDATE reset_password SET used = 1 WHERE token = ?");
            $stmt->execute([$otp]);

            // Clear session
            unset($_SESSION['verified_otp']);
            unset($_SESSION['npm']);

            echo json_encode(['success' => true, 'message' => 'Password berhasil direset!']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Silakan ulangi proses dari awal.']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Administrasi</title>
    <link rel="icon" href="assets\images\logo Gunadarma.png" type="image/png">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
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
            transition: opacity 1s ease-in-out;
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

        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            width: 100%;
            max-width: 400px;
        }

        .reset-header {
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

        /* Styles for OTP input boxes */
        .otp-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .otp-input {
            width: 40px;
            height: 40px;
            padding: 0;
            font-size: 1.2rem;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: #0055FF;
            box-shadow: 0 0 0 2px rgba(0, 85, 255, 0.1);
        }

        .reset-button {
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

        .reset-button:hover {
            background-color: #0044CC;
        }

        .alert {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .text-center {
            text-align: center;
        }

        .instructions {
            font-size: 0.85rem;
            margin-bottom: 1rem;
            color: #666;
            text-align: center;
        }

        .back-to-login {
            font-size: 0.85rem;
            margin-top: 1rem;
            text-align: center;
        }

        a {
            color: #0055FF;
            text-decoration: none;
            font-weight: 500;
        }

        a:hover {
            text-decoration: underline !important;
        }

        /* Hide password form initially */
        #passwordForm {
            display: none;
        }

        /* Animation for form transition */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
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

    <div class="container">
        <div class="reset-card">
            <div class="reset-header">Reset Password</div>

            <!-- OTP Form -->
            <div id="otpForm">
                <div class="instructions">
                    Kode OTP telah dikirim ke email Anda. Silakan periksa email Anda untuk melanjutkan proses reset
                    password.
                </div>
                <div id="otpMessage"></div>
                <div class="form-group">
                    <label for="otp">Kode OTP</label>
                    <div class="otp-container">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">

                    </div>
                    <input type="hidden" id="otp" name="otp">
                </div>
                <button type="button" class="reset-button" onclick="verifyOTP()">Verifikasi OTP</button>
            </div>

            <!-- Password Form (Initially Hidden) -->
            <div id="passwordForm">
                <div class="instructions">
                    OTP telah terverifikasi. Silakan masukkan password baru Anda.
                </div>
                <div id="passwordMessage"></div>
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" class="form-control" id="new_password" name="new_password"
                        placeholder="Masukkan Password Baru" required>
                </div>
                <button type="button" class="reset-button" onclick="resetPassword()">Reset Password</button>
            </div>

            <div class="back-to-login">
                <a href="login.php">Kembali ke halaman login</a>
            </div>
        </div>
    </div>

    <script>
        // Slideshow functionality
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(nextSlide, 5000);

        // OTP input handling
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpHidden = document.getElementById('otp');

        otpInputs.forEach((input, index) => {
            // Handle input
            input.addEventListener('input', (e) => {
                // Allow only numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');

                // Move to next input if value is entered
                if (e.target.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }

                // Update hidden input with complete OTP
                updateHiddenInput();
            });

            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').split('');

                otpInputs.forEach((input, i) => {
                    if (pastedData[i]) {
                        input.value = pastedData[i];
                        if (i < otpInputs.length - 1) {
                            otpInputs[i + 1].focus();
                        }
                    }
                });

                updateHiddenInput();
            });
        });

        function updateHiddenInput() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpHidden.value = otp;
        }

        // Function to show message
        function showMessage(elementId, message, isError = false) {
            const messageDiv = document.getElementById(elementId);
            messageDiv.innerHTML = `<div class="alert ${isError ? 'alert-danger' : 'alert-success'}">${message}</div>`;
        }

        // Function to verify OTP
        async function verifyOTP() {
            const otp = document.getElementById('otp').value;

            if (otp.length !== 5) {
                showMessage('otpMessage', 'Silakan masukkan 5 digit kode OTP', true);
                return;
            }

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=verify_otp&otp=${otp}`
                });
                const data = await response.json();

                if (data.success) {
                    // Hide OTP form and show password form
                    document.getElementById('otpForm').style.display = 'none';
                    document.getElementById('passwordForm').style.display = 'block';
                    document.getElementById('passwordForm').classList.add('fade-in');
                } else {
                    showMessage('otpMessage', data.message, true);
                }
            } catch (error) {
                showMessage('otpMessage', 'Terjadi kesalahan. Silakan coba lagi.', true);
            }
        }

        // Function to reset password
        async function resetPassword() {
            const newPassword = document.getElementById('new_password').value;

            if (!newPassword) {
                showMessage('passwordMessage', 'Silakan masukkan password baru', true);
                return;
            }

            // Basic password validation
            if (newPassword.length < 8) {
                showMessage('passwordMessage', 'Password harus minimal 8 karakter', true);
                return;
            }

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=reset_password&new_password=${encodeURIComponent(newPassword)}`
                });

                const data = await response.json();

                if (data.success) {
                    showMessage('passwordMessage', data.message);
                    // Redirect to login page after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage('passwordMessage', data.message, true);
                }
            } catch (error) {
                showMessage('passwordMessage', 'Terjadi kesalahan. Silakan coba lagi.', true);
            }
        }
    </script>
</body>

</html>