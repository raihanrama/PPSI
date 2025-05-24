<?php
session_start();
require 'config.php';

if (!isset($_SESSION['npm']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: login.php");
    exit;
}

$pesanInformasi = '';

$stmt = $pdo->prepare("SELECT status_verifikasi, pesan_informasi FROM mahasiswa WHERE npm = ?");
$stmt->execute([$_SESSION['npm']]);
$result = $stmt->fetch();

if ($result) {
    if ($result['status_verifikasi'] == 'Ditolak') {
        $pesanInformasi = $result['pesan_informasi'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['krs'])) {
            $krsFile = $_FILES['krs']['name'];
            $targetDir = "uploads/krs/";

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($krsFile, PATHINFO_EXTENSION));
            if ($fileExtension != "pdf") {
                $error = "File KRS hanya boleh berupa PDF.";
            } else {
                $newFileName = "krs_" . $_SESSION['npm'] . ".pdf";
                $targetFile = $targetDir . $newFileName;

                if (file_exists($targetFile)) {
                    unlink($targetFile);
                }

                if (move_uploaded_file($_FILES['krs']['tmp_name'], $targetFile)) {
                    $stmtUpdate = $pdo->prepare("UPDATE mahasiswa SET krs = ?, status_verifikasi = 'Belum diverifikasi' WHERE npm = ?");
                    if ($stmtUpdate->execute([$newFileName, $_SESSION['npm']])) {
                        $success = "File KRS berhasil diunggah. Menunggu verifikasi ulang.";
                    } else {
                        $error = "Gagal menyimpan data ke database.";
                    }
                } else {
                    $error = "Terjadi kesalahan saat mengunggah file.";
                }
            }
        }
    } else {
        header("Location: mahasiswa/mahasiswa.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perbaiki Data KRS - Sistem Administrasi</title>
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

        .form-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            width: 100%;
            max-width: 500px;
        }

        .form-header {
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
        }

        .form-control:focus {
            outline: none;
            border-color: #0055FF;
            box-shadow: 0 0 0 2px rgba(0, 85, 255, 0.1);
        }

        .submit-button {
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

        .submit-button:hover {
            background-color: #0044CC;
        }

        .back-button {
            width: 100%;
            padding: 0.5rem;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            height: 35px;
            margin-top: 0.5rem;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #5a6268;
            text-decoration: none;
            color: white;
        }

        .alert {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
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
        <div class="form-card">
            <div class="form-header">Perbaiki Data KRS</div>

            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <?php if (isset($success)) { ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php } ?>

            <?php if (!empty($pesanInformasi)) { ?>
                <div class="alert alert-warning">
                    <strong>Alasan Ditolak:</strong> <?php echo htmlspecialchars($pesanInformasi); ?>
                </div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="krs">Unggah KRS yang Benar (PDF)</label>
                    <input type="file" class="form-control" id="krs" name="krs" accept=".pdf" required>
                </div>
                <button type="submit" class="submit-button">Unggah KRS</button>
            </form>

            <a href="index.php" class="back-button">Kembali ke Beranda</a>
        </div>
    </div>

    <script>
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(nextSlide, 5000);
    </script>
</body>

</html>