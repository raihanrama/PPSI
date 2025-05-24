<?php
session_start();
if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}
require '../config.php';

if (!isset($_GET['edit']) || !isset($_GET['type'])) {
    header("Location: super_admin.php");
    exit;
}

$npm = $_GET['edit'];
$type = $_GET['type'];

if ($type == 'admin') {
    // Mendapatkan data admin berdasarkan NPM
    $stmt = $pdo->prepare("SELECT * FROM users WHERE npm = ?");
    $stmt->execute([$npm]);
    $data = $stmt->fetch();

    if (!$data) {
        header("Location: super_admin.php");
        exit;
    }

    // Update data admin
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_npm = $_POST['npm'];
        $role = $_POST['role'];

        // Update tanpa mengubah password
        $stmt = $pdo->prepare("UPDATE users SET npm = ?, role = ? WHERE npm = ?");
        $stmt->execute([$new_npm, $role, $npm]);

        header("Location: super_admin.php");
        exit;
    }

} elseif ($type == 'mahasiswa') {
    // Mendapatkan data mahasiswa berdasarkan NPM
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE npm = ?");
    $stmt->execute([$npm]);
    $data = $stmt->fetch();

    if (!$data) {
        header("Location: super_admin.php");
        exit;
    }

    // Update data mahasiswa
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_npm = $_POST['npm'];
        $email = $_POST['email'];
        $nama_lengkap = $_POST['nama_lengkap'];

        // Update tanpa mengubah password
        $stmt = $pdo->prepare("UPDATE mahasiswa SET npm = ?, email = ?, nama_lengkap = ? WHERE npm = ?");
        $stmt->execute([$new_npm, $email, $nama_lengkap, $npm]);

        header("Location: super_admin.php");
        exit;
    }
} else {
    header("Location: super_admin.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Akun</title>
    <link rel="icon" href="assets\images\logo Gunadarma.png" type="image/png">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            max-width: 600px;
            margin-top: 60px;
            margin-bottom: 40px;
        }

        .card {
            padding: 30px;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .text-center h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 0;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .text-center a {
            font-size: 1rem;
            font-weight: 500;
        }

        .btn-back {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include '../komponen/navbar.php'; ?>

    <div class="container">
        <h1 class="text-center">Edit Akun</h1>
        <div class="card">
            <?php if ($type == 'admin') { ?>
                <!-- Form Edit Admin -->
                <form method="POST" action="">
                    <div class="form-group mb-4">
                        <label for="npm">NPM Admin</label>
                        <input type="text" class="form-control" id="npm" name="npm"
                            value="<?php echo htmlspecialchars($data['npm']); ?>" required>
                    </div>
                    <div class="form-group mb-4">
                        <label for="role">Role Admin</label>
                        <select class="form-control" id="role" name="role">
                            <option value="admin_pi" <?php if ($data['role'] == 'admin_pi')
                                echo 'selected'; ?>>Admin PI
                            </option>
                            <option value="admin_sidang" <?php if ($data['role'] == 'admin_sidang')
                                echo 'selected'; ?>>Admin
                                Sidang</option>
                            <option value="admin_msib" <?php if ($data['role'] == 'admin_msib')
                                echo 'selected'; ?>>Admin MSIB
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Admin</button>
                </form>

            <?php } elseif ($type == 'mahasiswa') { ?>
                <!-- Form Edit Mahasiswa -->
                <form method="POST" action="">
                    <div class="form-group mb-4">
                        <label for="npm">NPM Mahasiswa</label>
                        <input type="text" class="form-control" id="npm" name="npm"
                            value="<?php echo htmlspecialchars($data['npm']); ?>" required>
                    </div>
                    <div class="form-group mb-4">
                        <label for="email">Email Mahasiswa</label>
                        <input type="text" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($data['email']); ?>" required>
                    </div>
                    <div class="form-group mb-4">
                        <label for="nama_lengkap">Nama Lengkap Mahasiswa</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                            value="<?php echo htmlspecialchars($data['nama_lengkap']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Mahasiswa</button>
                </form>
            <?php } ?>

            <div class="btn-back">
                <a href="admin_akun.php" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>