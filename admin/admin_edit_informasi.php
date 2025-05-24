<?php
session_start();
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

require '../config.php';

if (!isset($_GET['id'])) {
    header("Location: kelola_informasi.php");
    exit;
}

$id = $_GET['id'];

// Mendapatkan data informasi berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM informasi WHERE id = ?");
$stmt->execute([$id]);
$info = $stmt->fetch();

if (!$info) {
    header("Location: kelola_informasi.php");
    exit;
}

// Update data informasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $konten = $_POST['konten'];

    $stmt = $pdo->prepare("UPDATE informasi SET judul = ?, konten = ? WHERE id = ?");
    $stmt->execute([$judul, $konten, $id]);

    if ($stmt->rowCount() > 0) {
        $success = "Informasi berhasil diperbarui!";
        // Ambil data terbaru setelah update berhasil
        $stmt = $pdo->prepare("SELECT * FROM informasi WHERE id = ?");
        $stmt->execute([$id]);
        $info = $stmt->fetch();
    } else {
        $error = "Gagal memperbarui informasi.";
    }
}

include '../komponen/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Informasi</title>
    <link rel="icon" href="../assets\images\logo Gunadarma.png" type="image/png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 650px;
            margin-top: 50px;
            padding: 2rem;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        h1 {
            font-weight: 600;
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .btn-primary {
            background-color: #0056d2;
            border-color: #0056d2;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #0041a1;
            border-color: #0041a1;
        }

        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            border-color: #4e555b;
        }

        .alert {
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .text-center {
            font-size: 1.2rem;
        }

        textarea {
            resize: vertical;
        }

        .d-flex>*:not(:last-child) {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Edit Informasi</h1>

        <?php if (isset($success)) { ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php } elseif (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul Informasi</label>
                <input type="text" class="form-control" id="judul" name="judul"
                    value="<?php echo htmlspecialchars($info['judul']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="konten" class="form-label">Konten Informasi</label>
                <textarea class="form-control" id="konten" name="konten" rows="5"
                    required><?php echo htmlspecialchars($info['konten']); ?></textarea>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="admin_informasi.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Informasi
                </button>
            </div>
        </form>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>