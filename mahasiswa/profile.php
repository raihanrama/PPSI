<?php
session_start();
require_once 'session_handler.php';
checkSessionTimeout();
require '../config.php';

// Cek apakah user sudah login dan memiliki role mahasiswa
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

// Ambil NPM mahasiswa dari sesi login
$npm = $_SESSION['npm'];

// Ambil data mahasiswa dari database berdasarkan NPM
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE npm = ?");
$stmt->execute([$npm]);
$mahasiswa = $stmt->fetch();

if (!$mahasiswa) {
    echo "Data mahasiswa tidak ditemukan.";
    exit;
}

// Jika form untuk mengganti password di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_baru'])) {
    $password_baru = password_hash($_POST['password_baru'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("UPDATE mahasiswa SET password = ? WHERE npm = ?");
    $stmt->execute([$password_baru, $npm]);

    echo "<script>alert('Password berhasil diperbarui!'); window.location.href = 'profile.php';</script>";
}

// Jika form untuk mengunggah foto di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_profil'])) {
    $foto = $_FILES['foto_profil'];
    $uploadDir = '../uploads/profile/';
    $fileName = 'profile_' . $npm . '.' . pathinfo($foto['name'], PATHINFO_EXTENSION);
    $uploadPath = $uploadDir . $fileName;

    // Validasi file
    if ($foto['size'] > 2048000) { // Maksimum ukuran 2MB
        echo "<script>alert('Ukuran file terlalu besar. Maksimum 2MB.');</script>";
    } elseif (!in_array(strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
        echo "<script>alert('Format file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.');</script>";
    } else {
        // Pindahkan file dan update database
        if (move_uploaded_file($foto['tmp_name'], $uploadPath)) {
            $stmt = $pdo->prepare("UPDATE mahasiswa SET foto_profil = ? WHERE npm = ?");
            $stmt->execute([$fileName, $npm]);

            echo "<script>alert('Foto profil berhasil diperbarui!'); window.location.href = 'profile.php';</script>";
        } else {
            echo "<script>alert('Gagal mengunggah foto. Coba lagi.');</script>";
        }
    }
}

// Tentukan foto profil yang akan ditampilkan
$timestamp = time(); // Add this line
$fotoProfil = $mahasiswa['foto_profil'] ? '../uploads/profile/' . $mahasiswa['foto_profil'] . '?v=' . $timestamp : '../uploads/profile/default-profile.png';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Profil Mahasiswa</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
    <style>
        :root {
            --primary-color: #2575fc;
            --secondary-color: #6a11cb;
            --text-dark: #2c3e50;
            --bg-light: #f5f7fa;
        }

        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
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

        /* Profile Card Styles */
        .profile-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            overflow: hidden;
            background: white;
            position: relative;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            padding: 3rem;
            text-align: center;
            color: white;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            margin: 0 auto 0.5rem;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            margin-bottom: 0.5rem;
            object-fit: cover;
        }

        .profile-info {
            padding: 2rem;
        }

        .info-item {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .info-value {
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .btn-change-password {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 117, 252, 0.2);
            color: white;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-change-password:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 117, 252, 0.3);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-verified {
            background-color: #dcf5e3;
            color: #0d6832;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Modal Password Styles */
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
            border-bottom: none;
        }

        .modal-header .btn-close {
            color: white;
            opacity: 1;
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-title::before {
            content: '\f084';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        /* Form Controls */
        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.8rem 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.1);
            background-color: white;
        }

        /* Password Strength Indicator */
        .password-strength-container {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .progress {
            height: 8px;
            border-radius: 50px;
            background-color: #e9ecef;
            margin: 1rem 0;
        }

        .progress-bar {
            border-radius: 50px;
            transition: all 0.4s ease;
        }

        .password-requirements {
            margin-top: 1rem;
            font-size: 0.85rem;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .requirement-item i {
            font-size: 0.8rem;
        }

        .requirement-item.valid {
            color: #198754;
        }

        .requirement-item.invalid {
            color: #dc3545;
        }

        /* Submit Button */
        .modal .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(37, 117, 252, 0.2);
        }

        .modal .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 117, 252, 0.3);
        }

        /* Password Input Container */
        .password-input-container {
            position: relative;
        }

        .password-input-container .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .password-input-container .toggle-password:hover {
            color: var(--primary-color);
        }

        /* Password Strength Text */
        #passwordStrengthText {
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Profile Upload Section Styles */
        .profile-upload-section {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
        }

        .upload-btn {
            border: 2px dashed rgba(255, 255, 255, 0.3);
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        /* Profile Avatar Container */
        .profile-avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-avatar-container:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .avatar-overlay i {
            color: white;
            font-size: 1.5rem;
        }

        /* File Input Custom Styles */
        .file-input-container {
            margin-top: 1rem;
            display: none;
        }

        .file-input-label {
            color: white;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }

        .selected-file-name {
            font-size: 0.85rem;
            color: white;
            margin-top: 0.5rem;
            opacity: 0.8;
        }

        /* Upload Button Styles */
        .btn-upload-photo {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .btn-upload-photo:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        /* Preview Image Styles */
        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        /* Loading Animation */
        .upload-loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .upload-loading .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
            color: white;
        }

        /* Error Message Styles */
        .upload-error {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        /* Success Message Styles */
        .upload-success {
            color: #69db7c;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        /* Animation for strength updates */
        @keyframes strengthUpdate {
            0% {
                transform: scale(0.95);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .strength-update {
            animation: strengthUpdate 0.3s ease;
        }
    </style>
</head>

<body>

    <?php include '../komponen/navbarus.php'; ?>

    <div class="container main-content">
        <div class="welcome-banner">
            <h2>Profil Mahasiswa</h2>
            <p>Administrasi Akademik Universitas Gunadarma Fakultas Ilmu Komputer & Teknologi Informasi</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <!-- Profile Avatar and Upload Section -->
                        <div class="profile-avatar-container">
                            <img src="<?php echo htmlspecialchars($fotoProfil); ?>" alt="Profile Picture"
                                class="preview-image">
                            <div class="avatar-overlay" onclick="document.getElementById('foto_profil').click()">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="profile-upload-section">
                            <div class="file-input-container">
                                <div class="upload-btn-wrapper">
                                    <input type="file" class="form-control" id="foto_profil" name="foto_profil"
                                        accept="image/jpeg, image/png" hidden>
                                </div>
                                <div class="selected-file-name"></div>
                            </div>

                            <button type="submit" class="btn-upload-photo">
                                <i class="fas fa-check"></i>
                                Simpan Foto
                                <div class="upload-loading">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </button>

                            <div class="upload-error"></div>
                            <div class="upload-success"></div>
                        </form>

                        <h3 class="mb-0"><?php echo htmlspecialchars($mahasiswa['nama_lengkap']); ?></h3>
                        <p class="text-white-50 mb-0"><?php echo htmlspecialchars($mahasiswa['npm']); ?></p>
                    </div>

                    <div class="profile-info">
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($mahasiswa['email']); ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Status Verifikasi</div>
                            <div class="info-value">
                                <span class="status-badge <?php
                                echo $mahasiswa['status_verifikasi'] === 'Diverifikasi' ? 'status-verified' : ($mahasiswa['status_verifikasi'] === 'Ditolak' ? 'status-rejected' : 'status-pending');
                                ?>">
                                    <i class="fas <?php
                                    echo $mahasiswa['status_verifikasi'] === 'Diverifikasi' ? 'fa-check-circle' : ($mahasiswa['status_verifikasi'] === 'Ditolak' ? 'fa-times-circle' : 'fa-clock');
                                    ?>"></i>
                                    <?php echo htmlspecialchars($mahasiswa['status_verifikasi']); ?>
                                </span>
                                <?php if ($mahasiswa['status_verifikasi'] === 'Ditolak' && !empty($mahasiswa['pesan_informasi'])): ?>
                                    <div class="text-danger mt-2">
                                        <strong>Pesan:</strong>
                                        <?php echo htmlspecialchars($mahasiswa['pesan_informasi']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">KRS</div>
                            <div class="info-value">
                                <?php if (!empty($mahasiswa['krs'])): ?>
                                    <a href="../uploads/krs/<?php echo htmlspecialchars($mahasiswa['krs']); ?>"
                                        target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-file-download me-2"></i>Lihat KRS
                                    </a>
                                <?php else: ?>
                                    <span class="text-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>Belum mengunggah KRS
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="button" class="btn-change-password" data-bs-toggle="modal"
                            data-bs-target="#ubahPasswordModal">
                            <i class="fas fa-key me-2"></i>Ubah Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ubah Password -->
    <div class="modal fade" id="ubahPasswordModal" tabindex="-1" aria-labelledby="ubahPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ubahPasswordModalLabel">Ubah Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">
                        Harap masukkan password baru Anda di bawah ini. Gunakan kombinasi huruf besar, huruf kecil,
                        angka, dan simbol untuk membuat password yang kuat.
                    </p>

                    <form method="POST" onsubmit="return validatePassword()">
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="password_baru" id="password_baru" required
                                minlength="6" placeholder="Masukkan password baru" oninput="checkPasswordStrength()">
                        </div>
                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="konfirmasi_password" required minlength="6"
                                placeholder="Ulangi password baru">
                        </div>

                        <div class="mb-3">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" id="passwordStrengthBar" role="progressbar" style="width: 0;"
                                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small id="passwordStrengthText" class="form-text text-muted">Masukkan password untuk
                                melihat kekuatannya.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Perbarui Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password_baru').value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');

            let strength = 0;

            // Aturan Penilaian
            if (password.length >= 6) strength += 1; // Panjang minimal 6
            if (password.length >= 12) strength += 1; // Panjang minimal 12
            if (/[A-Z]/.test(password)) strength += 1; // Huruf besar
            if (/\d/.test(password)) strength += 1; // Angka
            if (/[@$!%*?&]/.test(password)) strength += 1; // Simbol khusus

            // Update Status Bar dan Teks
            switch (strength) {
                case 0:
                    strengthBar.style.width = '0%';
                    strengthBar.className = 'progress-bar bg-danger';
                    strengthText.textContent = 'Masukkan password untuk melihat kekuatannya.';
                    break;
                case 1:
                    strengthBar.style.width = '20%';
                    strengthBar.className = 'progress-bar bg-danger';
                    strengthText.textContent = 'Password terlalu lemah (Bad).';
                    break;
                case 2:
                    strengthBar.style.width = '40%';
                    strengthBar.className = 'progress-bar bg-warning';
                    strengthText.textContent = 'Password cukup baik (Weak).';
                    break;
                case 3:
                    strengthBar.style.width = '60%';
                    strengthBar.className = 'progress-bar bg-info';
                    strengthText.textContent = 'Password cukup kuat (Good).';
                    break;
                case 4:
                    strengthBar.style.width = '80%';
                    strengthBar.className = 'progress-bar bg-primary';
                    strengthText.textContent = 'Password sangat kuat (Strong).';
                    break;
                case 5:
                    strengthBar.style.width = '100%';
                    strengthBar.className = 'progress-bar bg-success';
                    strengthText.textContent = 'Password sempurna (Perfect).';
                    break;
            }
        }

        function validatePassword() {
            const passwordBaru = document.getElementById('password_baru').value;
            const konfirmasiPassword = document.getElementById('konfirmasi_password').value;

            if (passwordBaru !== konfirmasiPassword) {
                alert('Password baru dan konfirmasi password harus sama!');
                return false;
            }

            return true;
        }

        // File input handler
        document.getElementById('foto_profil').addEventListener('change', function (e) {
            const fileName = e.target.files[0]?.name;
            const fileSize = e.target.files[0]?.size;
            const fileType = e.target.files[0]?.type;

            // Update selected file name display
            const fileNameDisplay = document.querySelector('.selected-file-name');
            if (fileName) {
                fileNameDisplay.textContent = fileName;
                fileNameDisplay.style.display = 'block';
            } else {
                fileNameDisplay.style.display = 'none';
            }

            // Validate file
            const errorDisplay = document.querySelector('.upload-error');
            if (fileSize > 2048000) {
                errorDisplay.textContent = 'File terlalu besar. Maksimum 2MB.';
                errorDisplay.style.display = 'block';
                e.target.value = '';
                return;
            }

            // error jpeg
            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(fileType)) {
                errorDisplay.textContent = 'Format file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.';
                errorDisplay.style.display = 'block';
                e.target.value = '';
                return;
            }

            errorDisplay.style.display = 'none';

            // Preview image
            const reader = new FileReader();
            reader.onload = function (e) {
                document.querySelector('.preview-image').src = e.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        });

        // Form submit handler
        document.querySelector('form').addEventListener('submit', function (e) {
            const loadingSpinner = document.querySelector('.upload-loading');
            const submitButton = document.querySelector('.btn-upload-photo');
            const successMessage = document.querySelector('.upload-success');

            loadingSpinner.style.display = 'block';
            submitButton.disabled = true;

            // Reset messages
            document.querySelector('.upload-error').style.display = 'none';
            successMessage.style.display = 'none';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.9.1/font/bootstrap-icons.min.css"
        rel="stylesheet">
</body>

</html>