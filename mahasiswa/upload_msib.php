<?php
session_start();
require_once 'session_handler.php';
checkSessionTimeout();

if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

require '../config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npm = $_SESSION['npm'];
    $rangkuman_nilai = $_FILES['rangkuman_nilai']['name'];
    $cv = $_FILES['cv']['name'];
    $jenis_kegiatan = $_POST['jenis_kegiatan'];
    $nama_mitra = $_POST['nama_mitra'];
    $posisi = $_POST['posisi'];
    $target_dir = "../uploads/msib/";

    // Validate file uploads
    $allowed_types = ['pdf', 'doc', 'docx'];
    $rangkuman_ext = strtolower(pathinfo($rangkuman_nilai, PATHINFO_EXTENSION));
    $cv_ext = strtolower(pathinfo($cv, PATHINFO_EXTENSION));

    if (!in_array($rangkuman_ext, $allowed_types) || !in_array($cv_ext, $allowed_types)) {
        $error = "Format file tidak valid. Hanya PDF, DOC, dan DOCX yang diperbolehkan.";
    } else {
        // Generate unique filenames
        $rangkuman_nilai = $npm . '_rangkuman_' . time() . '.' . $rangkuman_ext;
        $cv = $npm . '_cv_' . time() . '.' . $cv_ext;

        if (
            move_uploaded_file($_FILES["rangkuman_nilai"]["tmp_name"], $target_dir . $rangkuman_nilai) &&
            move_uploaded_file($_FILES["cv"]["tmp_name"], $target_dir . $cv)
        ) {
            try {
                $stmt = $pdo->prepare("INSERT INTO msib (npm, rangkuman_nilai, cv, jenis_kegiatan, nama_mitra, posisi, status) 
                                     VALUES (?, ?, ?, ?, ?, ?, 'Belum diverifikasi')");

                if ($stmt->execute([$npm, $rangkuman_nilai, $cv, $jenis_kegiatan, $nama_mitra, $posisi])) {

                    // Tambahkan session flag untuk notifikasi
                    $_SESSION['upload_success'] = true;

                    header("Location: cek_msib.php");
                    exit;
                } else {
                    $error = "Gagal menyimpan data ke database.";
                }
            } catch (PDOException $e) {
                $error = "Error database: " . $e->getMessage();
            }
        } else {
            $error = "Gagal mengunggah file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Ajukan Rekomendasi MSIB</title>
    <link rel="icon" href="../assets\images\logo Gunadarma.png" type="image/png">
    <style>
        :root {
            --primary-color: #2575fc;
            --secondary-color: #6a11cb;
            --text-dark: #2c3e50;
            --bg-light: #f5f7fa;
        }

        .main-content {
            padding: 2rem 0;
        }

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

        .form-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
            padding: 0 2rem;
        }

        .progress-line {
            position: absolute;
            top: 15px;
            left: 10%;
            width: 80%;
            height: 2px;
            background: #ddd;
            z-index: 0;
        }

        .progress-line-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .step {
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            color: var(--text-dark);
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .step.active .step-number {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .step.completed .step-number {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .step-label {
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-top: 0.5rem;
        }

        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            background: white;
        }

        .upload-area:hover {
            border-color: var(--primary-color);
        }

        .upload-area i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .upload-status {
            display: none;
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .upload-status.success {
            display: block;
            background: #d4edda;
            color: #155724;
        }

        .form-section {
            transition: opacity 0.3s ease;
        }

        .form-section-disabled {
            opacity: 0.5;
            pointer-events: none;
            position: relative;
        }

        .form-section-disabled::before {
            content: "Harap upload dokumen terlebih dahulu";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 1rem;
            border-radius: 5px;
            z-index: 10;
            white-space: nowrap;
            display: none;
        }

        .form-section-disabled:hover::before {
            display: block;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            border: none;
            padding: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
        }

        .file-info {
            margin-top: 1rem;
            padding: 0.5rem;
            border-radius: 5px;
            background: var(--bg-light);
            display: none;
        }

        .progress {
            height: 4px;
            margin-top: 0.5rem;
        }

        #confirmUploadModal .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(37, 117, 252, 0.2);
        }

        #confirmUploadModal .modal-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
        }

        .upload-icon-container {
            position: relative;
            display: inline-block;
            padding: 20px;
        }

        .main-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            position: relative;
            z-index: 1;
        }

        .status-circle {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 25px;
            height: 25px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
        }

        .status-circle.active {
            opacity: 1;
            transform: scale(1);
        }

        .check-icon {
            color: white;
            font-size: 0.8rem;
        }

        .document-checklist .list-group-item {
            border: none;
            border-left: 3px solid transparent;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            padding: 1rem;
            position: relative;
        }

        .document-checklist .list-group-item.verified {
            border-left-color: var(--primary-color);
            background: #f8fff9;
        }

        .document-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .file-info-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-details {
            flex-grow: 1;
        }

        .file-icon {
            color: var(--primary-color);
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .file-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.2rem;
        }

        .file-size {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .checkmark {
            width: 0;
            height: 0;
            border-bottom: 2px solid #28a745;
            border-right: 2px solid #28a745;
            transform: rotate(45deg);
            position: absolute;
            right: 1rem;
            top: 50%;
            margin-top: -0.5rem;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .document-checklist .list-group-item.verified .checkmark {
            width: 8px;
            height: 15px;
            opacity: 1;
        }
    </style>
</head>

<body>
    <?php include '../komponen/navbarus.php'; ?>
    <div class="container main-content">
        <div class="welcome-banner">
            <h2>Upload Dokumen Syarat MSIB</h2>
            <p>Administrasi Akademik Universitas Gunadarma Fakultas Ilmu Komputer & Teknologi Informasi</p>
        </div>

        <div class="form-container">
            <div class="step-indicator">
                <div class="progress-line">
                    <div class="progress-line-fill"></div>
                </div>
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Upload Dokumen</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-label">Informasi Kegiatan</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" id="msibForm">
                <!-- Dokumen Upload Section -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="upload-area" id="rangkumanNilaiArea">
                            <input type="file" class="d-none" id="rangkuman_nilai" name="rangkuman_nilai"
                                accept=".pdf,.doc,.docx" required>
                            <i class="fas fa-file-alt mb-3"></i>
                            <h5>Rangkuman Nilai</h5>
                            <p class="text-muted">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            <div class="upload-status"></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="upload-area" id="cvArea">
                            <input type="file" class="d-none" id="cv" name="cv" accept=".pdf,.doc,.docx" required>
                            <i class="fas fa-file-pdf mb-3"></i>
                            <h5>Curriculum Vitae</h5>
                            <p class="text-muted">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            <div class="upload-status"></div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Kegiatan Section -->
                <div class="form-section form-section-disabled" id="kegiatanSection">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kegiatan</label>
                            <select class="form-control" id="jenis_kegiatan" name="jenis_kegiatan" required disabled>
                                <option value="">Pilih Jenis Kegiatan</option>
                                <option value="Magang">Magang</option>
                                <option value="Magang Mandiri">Magang Mandiri</option>
                                <option value="Studi Independent">Studi Independent</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Mitra/Perusahaan</label>
                            <input type="text" class="form-control" id="nama_mitra" name="nama_mitra"
                                placeholder="Masukkan nama mitra" required disabled>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Posisi</label>
                            <input type="text" class="form-control" id="posisi" name="posisi"
                                placeholder="Masukkan posisi" required disabled>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-submit w-100 mt-4">
                    Ajukan Rekomendasi
                    <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-4">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success mt-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add this modal HTML right before the closing </body> tag -->
    <div class="modal fade" id="confirmUploadModal" tabindex="-1" aria-labelledby="confirmUploadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmUploadModalLabel">Konfirmasi Upload Dokumen</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="upload-status-container text-center mb-4">
                        <div class="upload-icon-container mb-3">
                            <i class="fas fa-file-upload main-icon"></i>
                            <div class="status-circle">
                                <i class="fas fa-check check-icon"></i>
                            </div>
                        </div>
                        <h4 class="confirmation-title">Verifikasi Dokumen Anda</h4>
                        <p class="text-muted">Silakan periksa kembali dokumen yang akan diupload</p>
                    </div>

                    <div class="document-verification">
                        <div class="progress-indicator mb-4">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"
                                    id="verificationProgress"></div>
                            </div>
                            <div class="progress-text text-center mt-2">
                                Memverifikasi dokumen... <span id="progressPercentage">0%</span>
                            </div>
                        </div>

                        <div class="document-checklist">
                            <div id="documentList" class="list-group">
                                <!-- Documents will be listed here dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelUpload">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmUpload" disabled>
                        <span class="button-content">
                            <i class="fas fa-check me-2"></i>Upload Dokumen
                        </span>
                        <div class="button-loader d-none">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('msibForm');
            const steps = document.querySelectorAll('.step');
            const progressLine = document.querySelector('.progress-line-fill');
            const kegiatanSection = document.getElementById('kegiatanSection');
            const confirmUploadModal = new bootstrap.Modal(document.getElementById('confirmUploadModal'));
            const confirmUploadBtn = document.getElementById('confirmUpload');
            const documentList = document.getElementById('documentList');
            const verificationProgress = document.getElementById('verificationProgress');
            const progressPercentage = document.getElementById('progressPercentage');

            let documentsUploaded = {
                rangkuman_nilai: false,
                cv: false
            };

            // Function to setup file upload areas
            function setupFileUpload(areaId, inputId, fileType) {
                const area = document.getElementById(areaId);
                const input = document.getElementById(inputId);
                const fileInfo = area.querySelector('.file-info');
                const fileName = area.querySelector('.selected-file');
                const progressBar = area.querySelector('.progress-bar');
                const uploadStatus = area.querySelector('.upload-status');

                area.addEventListener('click', () => input.click());

                area.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    area.style.borderColor = 'var(--primary-color)';
                });

                area.addEventListener('dragleave', () => {
                    area.style.borderColor = '#ddd';
                });

                area.addEventListener('drop', (e) => {
                    e.preventDefault();
                    area.style.borderColor = '#ddd';
                    input.files = e.dataTransfer.files;
                    handleFile();
                });

                input.addEventListener('change', handleFile);

                function handleFile() {
                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        fileName.textContent = file.name;
                        fileInfo.style.display = 'block';

                        // Simulate file upload progress
                        let progress = 0;
                        progressBar.style.width = '0%';
                        const interval = setInterval(() => {
                            progress += 10;
                            progressBar.style.width = `${progress}%`;

                            if (progress >= 100) {
                                clearInterval(interval);
                                uploadStatus.className = 'upload-status success';
                                uploadStatus.innerHTML = '<i class="fas fa-check-circle me-2"></i>File berhasil diunggah';
                                documentsUploaded[inputId] = true;

                                // Check if both documents are uploaded
                                if (documentsUploaded.rangkuman_nilai && documentsUploaded.cv) {
                                    kegiatanSection.classList.remove('form-section-disabled');
                                    enableFormInputs();
                                    updateProgress(2);
                                }
                            }
                        }, 200);
                    }
                }
            }

            // Function to enable form inputs
            function enableFormInputs() {
                const inputs = kegiatanSection.querySelectorAll('input, select');
                inputs.forEach(input => input.disabled = false);
            }

            // Function to update progress steps
            function updateProgress(step) {
                steps.forEach((s, index) => {
                    if (index < step) {
                        s.classList.add('completed');
                    } else if (index === step) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active', 'completed');
                    }
                });

                const progressWidth = ((step - 1) / (steps.length - 1)) * 100;
                progressLine.style.width = `${progressWidth}%`;
            }

            // Function to format file size
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Function to simulate document verification
            function simulateDocumentVerification(callback) {
                let progress = 0;
                const totalDocs = document.querySelectorAll('.list-group-item').length;
                let verifiedCount = 0;

                const interval = setInterval(() => {
                    progress += 2;
                    verificationProgress.style.width = `${progress}%`;
                    progressPercentage.textContent = `${progress}%`;

                    if (progress % (100 / totalDocs) === 0) {
                        const items = document.querySelectorAll('.list-group-item:not(.verified)');
                        if (items.length > 0) {
                            verifiedCount++;
                            const item = items[0];
                            item.classList.add('verified');
                        }
                    }

                    if (progress >= 100) {
                        clearInterval(interval);
                        document.querySelector('.status-circle').classList.add('active');
                        confirmUploadBtn.disabled = false;
                        if (callback) callback();
                    }
                }, 50);
            }

            // Function to update document list in modal
            function updateDocumentList() {
                documentList.innerHTML = '';
                const uploads = [
                    {
                        id: 'rangkuman_nilai',
                        title: 'Rangkuman Nilai',
                        icon: 'file-alt'
                    },
                    {
                        id: 'cv',
                        title: 'Curriculum Vitae',
                        icon: 'file-pdf'
                    }
                ];

                uploads.forEach(upload => {
                    const input = document.getElementById(upload.id);
                    if (input.files && input.files[0]) {
                        const listItem = document.createElement('div');
                        listItem.className = 'list-group-item';
                        listItem.innerHTML = `
                    <div class="document-item-header">
                        <div class="file-info-container">
                            <i class="fas fa-${upload.icon} file-icon"></i>
                            <div class="file-details">
                                <div class="file-name">${upload.title}</div>
                                <div class="file-meta">
                                    <small class="text-muted">${input.files[0].name}</small>
                                    <small class="file-size text-muted ms-2">(${formatFileSize(input.files[0].size)})</small>
                                </div>
                            </div>
                        </div>
                        <div class="checkmark"></div>
                    </div>
                `;
                        documentList.appendChild(listItem);
                    }
                });
            }

            // Initialize file upload handlers
            setupFileUpload('rangkumanNilaiArea', 'rangkuman_nilai', 'rangkuman');
            setupFileUpload('cvArea', 'cv', 'cv');

            // Handle confirm upload button click
            confirmUploadBtn.addEventListener('click', function () {
                const buttonContent = this.querySelector('.button-content');
                const buttonLoader = this.querySelector('.button-loader');

                buttonContent.classList.add('d-none');
                buttonLoader.classList.remove('d-none');
                this.disabled = true;

                setTimeout(() => {
                    confirmUploadModal.hide();
                    form.submit();
                }, 1000);
            });

            // Form submission handler
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                if (!documentsUploaded.rangkuman_nilai || !documentsUploaded.cv) {
                    alert('Harap upload semua dokumen yang diperlukan terlebih dahulu.');
                    return;
                }

                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (!isValid) {
                    alert('Harap lengkapi semua field yang diperlukan.');
                    return;
                }

                // Show confirmation modal
                updateDocumentList();
                confirmUploadModal.show();
                simulateDocumentVerification();
                updateProgress(3);
            });

            // Optional: Reset form state when modal is closed
            document.getElementById('confirmUploadModal').addEventListener('hidden.bs.modal', function () {
                const buttonContent = confirmUploadBtn.querySelector('.button-content');
                const buttonLoader = confirmUploadBtn.querySelector('.button-loader');

                buttonContent.classList.remove('d-none');
                buttonLoader.classList.add('d-none');
                confirmUploadBtn.disabled = true;

                document.querySelector('.status-circle').classList.remove('active');
                verificationProgress.style.width = '0%';
                progressPercentage.textContent = '0%';

                const verifiedItems = documentList.querySelectorAll('.verified');
                verifiedItems.forEach(item => item.classList.remove('verified'));
            });
        });
    </script>
</body>

</html>