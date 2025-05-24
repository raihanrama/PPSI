<?php
session_start();
require_once 'session_handler.php';
checkSessionTimeout();

// Cek apakah user sudah login
if (!isset($_SESSION['npm'])) {
    header("Location: ../login.php");
    exit;
}

// Cek timeout
checkSessionTimeout();

require '../config.php';

$npm = $_SESSION['npm'];
$stmt = $pdo->prepare("SELECT msib.status, msib.cv, msib.rangkuman_nilai, msib.surat, msib.pesan_informasi, 
                      msib.jenis_kegiatan, msib.nama_mitra, msib.posisi 
                      FROM msib WHERE msib.npm = ?");
$stmt->execute([$npm]);
$msib = $stmt->fetch();

// Proses unggah ulang file jika mahasiswa mengunggah file baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $msib['status'] == 'ditolak') {
    // Direktori penyimpanan file
    $uploadDir = '../uploads/msib/';

    // Proses unggah file CV
    $cvFileName = $_FILES['cv']['name'];
    $cvFileTmp = $_FILES['cv']['tmp_name'];
    $cvFilePath = $uploadDir . $cvFileName;

    // Proses unggah file Rangkuman Nilai
    $rangkumanFileName = $_FILES['rangkuman_nilai']['name'];
    $rangkumanFileTmp = $_FILES['rangkuman_nilai']['tmp_name'];
    $rangkumanFilePath = $uploadDir . $rangkumanFileName;

    // Cek dan unggah file CV dan Rangkuman Nilai
    if (move_uploaded_file($cvFileTmp, $cvFilePath) && move_uploaded_file($rangkumanFileTmp, $rangkumanFilePath)) {
        // Update status MSIB dan set kolom cv dan rangkuman_nilai ke file yang baru diunggah
        $stmt = $pdo->prepare("UPDATE msib SET status = 'Belum diverifikasi', cv = ?, rangkuman_nilai = ? WHERE npm = ?");
        $stmt->execute([$cvFileName, $rangkumanFileName, $npm]);

        // Reload halaman agar data terbaru terlihat
        header("Location: cek_msib.php");
        exit;
    } else {
        $error = "Gagal mengunggah file. Coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cek Status MSIB</title>
    <link rel="icon" href="../assets/images/logo Gunadarma.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
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

        /* Status badge styles */
        .status-badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .status-diverifikasi {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }

        .status-ditolak {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffd700;
        }

        /* Message box styles */
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #6366f1;
            padding: 1.25rem;
            border-radius: 8px;
            text-align: left;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin: 0.5rem 0;
            position: relative;
            transition: all 0.3s ease;
        }

        .message-box.rejected {
            border-left-color: #ef4444;
            background-color: #fef2f2;
        }

        .message-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .message-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .message-content {
            color: #4b5563;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 0;
        }

        .message-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: #9ca3af;
            font-size: 1.25rem;
        }

        /* Card and Table Styles */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            border: none;
        }

        .table thead th:first-child {
            border-top-left-radius: 15px;
        }

        .table thead th:last-child {
            border-top-right-radius: 15px;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(37, 117, 252, 0.05);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        /* File link styles */
        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background-color: #f3f4f6;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .file-link:hover {
            background-color: #e5e7eb;
            transform: translateY(-1px);
        }

        /* Upload form styles */
        .upload-form {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.1);
        }

        .btn-upload {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 117, 252, 0.2);
        }

        .btn-ajukan-msib {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .btn-ajukan-msib:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(37, 117, 252, 0.3);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['upload_success'])): ?>
        <div id="notification-root"></div>
        <script type="text/babel">
            const SuccessNotification = () => {
                const [isVisible, setIsVisible] = React.useState(true);

                React.useEffect(() => {
                    const confetti = () => {
                        const colors = ['#2575fc', '#6a11cb', '#f5f7fa'];
                        const canvas = document.createElement('canvas');
                        canvas.style.position = 'fixed';
                        canvas.style.top = '0';
                        canvas.style.left = '0';
                        canvas.style.width = '100%';
                        canvas.style.height = '100%';
                        canvas.style.pointerEvents = 'none';
                        canvas.style.zIndex = '9999';
                        document.body.appendChild(canvas);

                        const ctx = canvas.getContext('2d');
                        canvas.width = window.innerWidth;
                        canvas.height = window.innerHeight;

                        const particles = [];

                        for (let i = 0; i < 100; i++) {
                            particles.push({
                                x: Math.random() * canvas.width,
                                y: canvas.height + Math.random() * 100,
                                vx: (Math.random() - 0.5) * 10,
                                vy: -Math.random() * 10 - 10,
                                size: Math.random() * 5 + 5,
                                color: colors[Math.floor(Math.random() * colors.length)]
                            });
                        }

                        const animate = () => {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            particles.forEach((p, i) => {
                                p.x += p.vx;
                                p.y += p.vy;
                                p.vy += 0.2;
                                p.size *= 0.99;
                                ctx.fillStyle = p.color;
                                ctx.beginPath();
                                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                                ctx.fill();
                                if (p.size < 0.1) particles.splice(i, 1);
                            });
                            if (particles.length > 0) {
                                requestAnimationFrame(animate);
                            } else {
                                document.body.removeChild(canvas);
                            }
                        };
                        animate();
                    };
                    confetti();
                }, []);

                if (!isVisible) return null;

                return (
                    <div style={{
                        position: 'fixed',
                        top: '80px',
                        right: '20px',
                        width: '384px',
                        zIndex: 9999,
                        animation: 'slideIn 0.5s ease-out'
                    }}>
                        <div style={{
                            background: 'linear-gradient(to right, #7928CA, #FF0080)',
                            padding: '16px',
                            borderRadius: '8px',
                            color: 'white',
                            boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                            position: 'relative'
                        }}>
                            <button
                                onClick={() => setIsVisible(false)}
                                style={{
                                    position: 'absolute',
                                    top: '8px',
                                    right: '8px',
                                    background: 'none',
                                    border: 'none',
                                    color: 'white',
                                    cursor: 'pointer',
                                    padding: '4px'
                                }}
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <div>
                                    <h4 style={{ fontSize: '1.125rem', fontWeight: '600', marginBottom: '8px' }}>
                                        Dokumen Berhasil Diunggah!
                                    </h4>
                                    <div style={{ color: 'rgba(255, 255, 255, 0.9)' }}>
                                        <div>
                                            <p>Semua dokumen persyaratan sidang telah berhasil disimpan.</p>
                                            <div style={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '8px',
                                                marginTop: '8px',
                                                background: 'rgba(255, 255, 255, 0.2)',
                                                padding: '8px',
                                                borderRadius: '6px'
                                            }}>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                <span>Menunggu verifikasi dari admin/staff</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            };

            ReactDOM.createRoot(document.getElementById('notification-root')).render(<SuccessNotification />);
        </script>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        </style>
        <?php unset($_SESSION['upload_success']); ?>
    <?php endif; ?>
    <?php include '../komponen/navbarus.php'; ?>
    <br>
    <div class="container main-content">
        <div class="welcome-banner">
            <h2>Cek Status Pengajuan MSIB</h2>
            <p>Administrasi Akademik Universitas Gunadarma Fakultas Ilmu Komputer & Teknologi Informasi</p>
        </div>

        <?php if ($msib) { ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status MSIB</th>
                                <th>Jenis Kegiatan</th>
                                <th>Nama Mitra</th>
                                <th>Posisi</th>
                                <th>Dokumen</th>
                                <?php if ($msib['status'] == 'Diverifikasi') { ?>
                                    <th>Surat Rekomendasi</th>
                                <?php } elseif ($msib['status'] == 'ditolak') { ?>
                                    <th>Informasi</th>
                                    <th>Aksi</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusIcon = '';
                                    switch (strtolower($msib['status'])) {
                                        case 'diverifikasi':
                                            $statusClass = 'status-diverifikasi';
                                            $statusIcon = '<i class="fas fa-check-circle"></i>';
                                            break;
                                        case 'ditolak':
                                            $statusClass = 'status-ditolak';
                                            $statusIcon = '<i class="fas fa-times-circle"></i>';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                            $statusIcon = '<i class="fas fa-clock"></i>';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo $statusIcon; ?>     <?php echo htmlspecialchars($msib['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($msib['jenis_kegiatan']); ?></td>
                                <td><?php echo htmlspecialchars($msib['nama_mitra']); ?></td>
                                <td><?php echo htmlspecialchars($msib['posisi']); ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-2">
                                        <a href="../uploads/msib/<?php echo htmlspecialchars($msib['cv']); ?>"
                                            class="file-link" target="_blank">
                                            <i class="fas fa-file-pdf"></i> CV
                                        </a>
                                        <a href="../uploads/msib/<?php echo htmlspecialchars($msib['rangkuman_nilai']); ?>"
                                            class="file-link" target="_blank">
                                            <i class="fas fa-file-pdf"></i> Rangkuman Nilai
                                        </a>
                                    </div>
                                </td>
                                <?php if ($msib['status'] == 'Diverifikasi') { ?>
                                    <td>
                                        <a href="../uploads/msib/<?php echo htmlspecialchars($msib['surat']); ?>"
                                            class="file-link" target="_blank">
                                            <i class="fas fa-file-pdf"></i> Download Surat
                                        </a>
                                    </td>
                                <?php } elseif ($msib['status'] == 'ditolak') { ?>
                                    <td>
                                        <div class="message-box rejected">
                                            <div class="message-title">
                                                <i class="fas fa-exclamation-circle"></i>
                                                Pesan dari Admin
                                            </div>
                                            <p class="message-content"><?php echo htmlspecialchars($msib['pesan_informasi']); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
                                            <div class="mb-3">
                                                <label for="cv" class="form-label">CV Baru</label>
                                                <input type="file" class="form-control" id="cv" name="cv" required
                                                    accept=".pdf">
                                            </div>
                                            <div class="mb-3">
                                                <label for="rangkuman_nilai" class="form-label">Rangkuman Nilai Baru</label>
                                                <input type="file" class="form-control" id="rangkuman_nilai"
                                                    name="rangkuman_nilai" required accept=".pdf">
                                            </div>
                                            <button type="submit" class="btn btn-upload">
                                                <i class="fas fa-upload me-2"></i>Upload Ulang
                                            </button>
                                        </form>
                                    </td>
                                <?php } ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } else { ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-file-upload"></i>
                </div>
                <h3>Belum Ada Pengajuan Surat Rekomendasi MSIB</h3>
                <p>Anda belum mengajukan Surat Rekomendasi MSIB. Silakan klik tombol di bawah untuk membuat pengajuan baru.
                </p>
                <a href="upload_msib.php" class="btn-ajukan-msib">
                    <i class="fas fa-plus-circle"></i>
                    Ajukan Surat Rekomendasi
                </a>
            </div>
        <?php } ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>