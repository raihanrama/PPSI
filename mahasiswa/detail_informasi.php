<?php
session_start();
require_once 'session_handler.php';
checkSessionTimeout();


// Cek apakah user adalah mahasiswa
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

require '../config.php'; // Koneksi ke database

// Cek apakah ID informasi ada di URL
if (!isset($_GET['id'])) {
    echo "ID informasi tidak ditemukan.";
    exit;
}

$id = $_GET['id'];

// Ambil data informasi berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM informasi WHERE id = ?");
$stmt->execute([$id]);
$info = $stmt->fetch();

if (!$info) {
    echo "Informasi tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <title>Detail Informasi</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
</head>

<body>
    <?php include '../komponen/navbarus.php'; ?>

    <div class="container mt-5">
        <h1><?php echo htmlspecialchars($info['judul']); ?></h1>
        <p><?php echo htmlspecialchars($info['konten']); ?></p>
        <p><small>Diposting pada: <?php echo date('d M Y', strtotime($info['tanggal'])); ?></small></p>

        <a href="mahasiswa.php" class="btn btn-primary">Kembali ke Beranda</a>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>