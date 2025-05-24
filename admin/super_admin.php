<?php
session_start();

// Pastikan super admin yang login
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin_pi', 'admin_sidang', 'admin_msib', 'super_admin'])) {
    header("Location: ../login.php");
    exit;
}

require '../config.php';

// Mendapatkan jumlah total data
$stmt = $pdo->query("SELECT COUNT(*) as total_mahasiswa FROM mahasiswa");
$total_mahasiswa = $stmt->fetch()['total_mahasiswa'];

$stmt = $pdo->query("SELECT COUNT(*) as total_admin FROM users WHERE role LIKE 'admin%'");
$total_admin = $stmt->fetch()['total_admin'];

$stmt = $pdo->query("SELECT COUNT(*) as total_pi FROM penelitian");
$total_pi = $stmt->fetch()['total_pi'];

$stmt = $pdo->query("SELECT COUNT(*) as total_msib FROM msib");
$total_msib = $stmt->fetch()['total_msib'];

$stmt = $pdo->query("SELECT COUNT(*) as total_sidang FROM sidang");
$total_sidang = $stmt->fetch()['total_sidang'];

include '../komponen/navbar.php'; // Navbar untuk super admin
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Super Admin</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Desain yang lebih bersih */
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .card-text {
            font-size: 2rem;
            font-weight: bold;
        }

        .filter-buttons button {
            margin: 5px;
        }

        h1 {
            font-weight: 700;
            color: #333;
        }

        .chart-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Dashboard Super Admin</h1>

        <!-- Ringkasan Data dalam Card -->
        <div class="row">
            <div class="col-md-3">
                <div class="card dashboard-card text-center bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Mahasiswa</h5>
                        <p class="card-text fs-4"><?php echo $total_mahasiswa; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-center bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Admin</h5>
                        <p class="card-text fs-4"><?php echo $total_admin; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-center bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Total PI</h5>
                        <p class="card-text fs-4"><?php echo $total_pi; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card text-center bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total MSIB</h5>
                        <p class="card-text fs-4"><?php echo $total_msib; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mt-4">
                <div class="card dashboard-card text-center bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Sidang</h5>
                        <p class="card-text fs-4"><?php echo $total_sidang; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Filter Data -->
        <div class="text-center mt-4 filter-buttons">
            <button class="btn btn-outline-primary" onclick="filterChart('all')">Semua Data</button>
            <button class="btn btn-outline-info" onclick="filterChart('mahasiswa')">Mahasiswa</button>
            <button class="btn btn-outline-primary" onclick="filterChart('admin')">Admin</button>
            <button class="btn btn-outline-warning" onclick="filterChart('pi')">PI</button>
            <button class="btn btn-outline-success" onclick="filterChart('msib')">MSIB</button>
            <button class="btn btn-outline-danger" onclick="filterChart('sidang')">Sidang</button>
        </div>

        <!-- Chart untuk visualisasi data -->
        <div class="mt-5">
            <canvas id="adminChart" style="max-height: 400px;"></canvas>
        </div>

        <script>
            // Data awal untuk chart (semua data)
            const allData = {
                labels: ['Mahasiswa', 'Admin', 'PI', 'MSIB', 'Sidang'],
                datasets: [{
                    label: 'Jumlah',
                    data: [<?php echo $total_mahasiswa; ?>, <?php echo $total_admin; ?>, <?php echo $total_pi; ?>, <?php echo $total_msib; ?>, <?php echo $total_sidang; ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 99, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 2
                }]
            };

            const chartOptions = {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#333'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#333'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };

            // Inisialisasi chart
            const ctx = document.getElementById('adminChart').getContext('2d');
            const adminChart = new Chart(ctx, {
                type: 'bar',
                data: allData,
                options: chartOptions
            });

            // Fungsi untuk filter chart berdasarkan kategori
            function filterChart(category) {
                let newData;
                switch (category) {
                    case 'mahasiswa':
                        newData = { labels: ['Mahasiswa'], datasets: [{ label: 'Jumlah', data: [<?php echo $total_mahasiswa; ?>], backgroundColor: ['rgba(75, 192, 192, 0.5)'], borderColor: ['rgba(75, 192, 192, 1)'], borderWidth: 2 }] };
                        break;
                    case 'admin':
                        newData = { labels: ['Admin'], datasets: [{ label: 'Jumlah', data: [<?php echo $total_admin; ?>], backgroundColor: ['rgba(54, 162, 235, 0.5)'], borderColor: ['rgba(54, 162, 235, 1)'], borderWidth: 2 }] };
                        break;
                    case 'pi':
                        newData = { labels: ['PI'], datasets: [{ label: 'Jumlah', data: [<?php echo $total_pi; ?>], backgroundColor: ['rgba(255, 206, 86, 0.5)'], borderColor: ['rgba(255, 206, 86, 1)'], borderWidth: 2 }] };
                        break;
                    case 'msib':
                        newData = { labels: ['MSIB'], datasets: [{ label: 'Jumlah', data: [<?php echo $total_msib; ?>], backgroundColor: ['rgba(153, 102, 255, 0.5)'], borderColor: ['rgba(153, 102, 255, 1)'], borderWidth: 2 }] };
                        break;
                    case 'sidang':
                        newData = { labels: ['Sidang'], datasets: [{ label: 'Jumlah', data: [<?php echo $total_sidang; ?>], backgroundColor: ['rgba(255, 99, 132, 0.5)'], borderColor: ['rgba(255, 99, 132, 1)'], borderWidth: 2 }] };
                        break;
                    default:
                        newData = allData;
                }
                adminChart.data = newData;
                adminChart.update();
            }

        </script>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>