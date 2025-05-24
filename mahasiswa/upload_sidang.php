<?php
session_start();
require_once 'session_handler.php';
checkSessionTimeout();

if (!isset($_SESSION['npm']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

require '../config.php'; // Pastikan file konfigurasi database di-include
$error = '';
$success = '';

// Daftar file yang perlu diunggah
$fileFields = [
    'surat_krs' => 'Surat KRS',
    'sertifikat_pi' => 'Sertifikat PI',
    'sertifikat_workshop' => 'Sertifikat Workshop',
    'sertifikat_kursus' => 'Sertifikat Kursus',
    'sertifikat_apptitude' => 'Sertifikat Aptitude',
    'acc_pembimbing_1' => 'ACC Pembimbing 1',
    'acc_pembimbing_2' => 'ACC Pembimbing 2',
    'buku_bimbingan' => 'Buku Bimbingan',
    'manual_book' => 'Manual Book',
    'draft_jurnal' => 'Draft Jurnal',
    'poster' => 'Poster'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $npm = $_SESSION['npm'];
    $target_dir = "../uploads/sidang/"; // Direktori upload
    $uploadSuccess = true;
    $uploadedFiles = [];

    // Memproses upload setiap file
    foreach ($fileFields as $field => $label) {
        if (isset($_FILES[$field]['name']) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES[$field]["tmp_name"];
            $fileExt = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $fileName = $npm . "_" . $field . "." . $fileExt; // Penamaan file dengan format NPM_jenisFile
            $targetFilePath = $target_dir . $fileName;

            if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                $uploadedFiles[$field] = $fileName;
            } else {
                $uploadSuccess = false;
                $error = "Maaf, terjadi kesalahan saat mengunggah file: $label.";
                break;
            }
        } else {
            $uploadSuccess = false;
            $error = "File $label tidak ditemukan atau terjadi kesalahan.";
            break;
        }
    }

    if ($uploadSuccess) {
        $stmt = $pdo->prepare("INSERT INTO sidang (npm, surat_krs, sertifikat_pi, sertifikat_workshop, sertifikat_kursus, sertifikat_apptitude, acc_pembimbing_1, acc_pembimbing_2, buku_bimbingan, manual_book, draft_jurnal, poster, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'belum diverifikasi')");

        $stmt->execute(array_merge([$npm], array_values($uploadedFiles)));

        // Tambahkan session flag untuk notifikasi
        $_SESSION['upload_success'] = true;

        header("Location: cek_sidang.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Upload Sidang Skripsi</title>
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
            background: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255, 255, 255, 0.05) 10px, rgba(255, 255, 255, 0.05) 20px);
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

        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
            margin-bottom: 1rem;
        }

        .upload-area:hover {
            border-color: var(--primary-color);
        }

        .upload-area i {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .file-info {
            display: none;
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 5px;
            background: var(--bg-light);
        }

        .progress {
            height: 4px;
            margin-top: 0.5rem;
        }

        .upload-category {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .upload-category h3 {
            color: var(--text-dark);
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--bg-light);
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
            <h2>Upload Dokumen File Syarat Sidang Sarjana</h2>
            <p>Administrasi Akademik Universitas Gunadarma Fakultas Ilmu Komputer & Teknologi Informasi</p>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="sidangForm">
            <!-- Academic Documents -->
            <div class="upload-category">
                <h3><i class="fas fa-graduation-cap me-2"></i>Dokumen Akademik</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="upload-area" id="surat_krsArea">
                            <input type="file" class="d-none" id="surat_krs" name="surat_krs" accept=".pdf,.doc,.docx"
                                required>
                            <i class="fas fa-file-alt"></i>
                            <h5>Surat KRS</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="upload-area" id="buku_bimbinganArea">
                            <input type="file" class="d-none" id="buku_bimbingan" name="buku_bimbingan" required>
                            <i class="fas fa-book"></i>
                            <h5>Buku Bimbingan</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificates -->
            <div class="upload-category">
                <h3><i class="fas fa-certificate me-2"></i>Sertifikat</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="upload-area" id="sertifikat_piArea">
                            <input type="file" class="d-none" id="sertifikat_pi" name="sertifikat_pi" required>
                            <i class="fas fa-building"></i>
                            <h5>Sertifikat PI</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="upload-area" id="sertifikat_workshopArea">
                            <input type="file" class="d-none" id="sertifikat_workshop" name="sertifikat_workshop"
                                required>
                            <i class="fas fa-tools"></i>
                            <h5>Sertifikat Workshop</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="upload-area" id="sertifikat_kursusArea">
                            <input type="file" class="d-none" id="sertifikat_kursus" name="sertifikat_kursus" required>
                            <i class="fas fa-award"></i>
                            <h5>Sertifikat Kursus</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="upload-area" id="sertifikat_apptitudeArea">
                            <input type="file" class="d-none" id="sertifikat_apptitude" name="sertifikat_apptitude"
                                required>
                            <i class="fas fa-brain"></i>
                            <h5>Sertifikat Aptitude</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!------ Masih belum selesai------->
            <!-- Supervisor Approval -->
            <div class="upload-category">
                <h3><i class="fas fa-check-circle me-2"></i>Persetujuan Pembimbing</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="upload-area" id="acc_pembimbing_1Area">
                            <input type="file" class="d-none" id="acc_pembimbing_1" name="acc_pembimbing_1" required>
                            <i class="fas fa-user-check"></i>
                            <h5>ACC Pembimbing 1</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="upload-area" id="acc_pembimbing_2Area">
                            <input type="file" class="d-none" id="acc_pembimbing_2" name="acc_pembimbing_2" required>
                            <i class="fas fa-user-check"></i>
                            <h5>ACC Pembimbing 2</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Research Documents -->
            <div class="upload-category">
                <h3><i class="fas fa-file-alt me-2"></i>Dokumen Penelitian</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="upload-area" id="manual_bookArea">
                            <input type="file" class="d-none" id="manual_book" name="manual_book" required>
                            <i class="fas fa-book-open"></i>
                            <h5>Manual Book</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="upload-area" id="draft_jurnalArea">
                            <input type="file" class="d-none" id="draft_jurnal" name="draft_jurnal" required>
                            <i class="fas fa-newspaper"></i>
                            <h5>Draft Jurnal</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="upload-area" id="posterArea">
                            <input type="file" class="d-none" id="poster" name="poster" required>
                            <i class="fas fa-image"></i>
                            <h5>Poster</h5>
                            <p class="text-muted small">Klik atau drag file kesini</p>
                            <div class="file-info">
                                <p class="selected-file mb-1"></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-submit w-100">
                Upload Semua Dokumen
                <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>
    </div>

    <!-- Add this HTML for the modal right before the closing </body> tag -->
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
                                <!-- Documents will be listed here -->
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('sidangForm');
            const fileAreas = document.querySelectorAll('.upload-area');
            const documentsUploaded = {};
            const confirmUploadModal = new bootstrap.Modal(document.getElementById('confirmUploadModal'));
            const confirmUploadBtn = document.getElementById('confirmUpload');
            const documentList = document.getElementById('documentList');
            const verificationProgress = document.getElementById('verificationProgress');
            const progressPercentage = document.getElementById('progressPercentage');

            // Utility function to format file sizes
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Setup file upload functionality for each area
            function setupFileUpload(area) {
                const input = area.querySelector('input[type="file"]');
                const fileInfo = area.querySelector('.file-info');
                const fileName = area.querySelector('.selected-file');
                const progressBar = area.querySelector('.progress-bar');

                // Initialize upload status
                documentsUploaded[input.id] = false;

                // Click handler for the upload area
                area.addEventListener('click', () => input.click());

                // Drag and drop handlers
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

                // Reset status when clicking input
                input.addEventListener('click', () => {
                    documentsUploaded[input.id] = false;
                    fileInfo.style.display = 'none';
                    progressBar.style.width = '0%';
                    progressBar.classList.remove('bg-success');
                    fileName.textContent = '';
                });

                // Handle file selection
                input.addEventListener('change', handleFile);

                function handleFile() {
                    if (input.files && input.files[0]) {
                        const file = input.files[0];
                        fileName.textContent = file.name;
                        fileInfo.style.display = 'block';

                        let progress = 0;
                        progressBar.style.width = '0%';
                        progressBar.classList.remove('bg-success');

                        const interval = setInterval(() => {
                            progress += 10;
                            progressBar.style.width = `${progress}%`;
                            if (progress >= 100) {
                                clearInterval(interval);
                                progressBar.classList.add('bg-success');
                                documentsUploaded[input.id] = true;
                            }
                        }, 200);
                    } else {
                        // Reset if no file is selected
                        documentsUploaded[input.id] = false;
                        fileInfo.style.display = 'none';
                        progressBar.style.width = '0%';
                        progressBar.classList.remove('bg-success');
                        fileName.textContent = '';
                    }
                }

                // Return reset function for this upload area
                return {
                    reset: () => {
                        documentsUploaded[input.id] = false;
                        fileInfo.style.display = 'none';
                        progressBar.style.width = '0%';
                        progressBar.classList.remove('bg-success');
                        fileName.textContent = '';
                        input.value = ''; // Clear the input
                    }
                };
            }

            // Setup all file upload areas
            const uploadAreas = Array.from(fileAreas).map(area => ({
                area,
                controller: setupFileUpload(area)
            }));

            // Simulate document verification process
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

            // Update the document list in the confirmation modal
            function updateDocumentList() {
                documentList.innerHTML = '';
                fileAreas.forEach(area => {
                    const input = area.querySelector('input[type="file"]');
                    const title = area.querySelector('h5').textContent;
                    if (input.files && input.files[0]) {
                        const listItem = document.createElement('div');
                        listItem.className = 'list-group-item';
                        listItem.innerHTML = `
                    <div class="document-item-header">
                        <div class="file-info-container">
                            <i class="fas fa-file-alt file-icon"></i>
                            <div class="file-details">
                                <div class="file-name">${title}</div>
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

            // Handle confirm upload button click
            confirmUploadBtn.addEventListener('click', function () {
                const buttonContent = this.querySelector('.button-content');
                const buttonLoader = this.querySelector('.button-loader');

                // Show loading state
                buttonContent.classList.add('d-none');
                buttonLoader.classList.remove('d-none');
                this.disabled = true;

                // Submit form after a short delay
                setTimeout(() => {
                    confirmUploadModal.hide();
                    form.submit();
                }, 1000);
            });

            // Handle form submission
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const requiredInputs = form.querySelectorAll('input[required]');
                let allUploaded = true;

                // Check if all required documents are uploaded
                requiredInputs.forEach(input => {
                    if (!documentsUploaded[input.id]) {
                        allUploaded = false;
                    }
                });

                if (!allUploaded) {
                    alert('Harap upload semua dokumen yang diperlukan terlebih dahulu.');
                    return;
                }

                // Show confirmation modal with document list
                updateDocumentList();
                confirmUploadModal.show();
                simulateDocumentVerification();
            });

            // Optional: Add a reset all function if needed
            function resetAllUploads() {
                uploadAreas.forEach(({ controller }) => controller.reset());
            }

            // Optional: Handle modal close
            document.getElementById('cancelUpload').addEventListener('click', () => {
                // Reset modal state
                confirmUploadBtn.disabled = true;
                document.querySelector('.status-circle').classList.remove('active');
                verificationProgress.style.width = '0%';
                progressPercentage.textContent = '0%';
                const verifiedItems = document.querySelectorAll('.list-group-item.verified');
                verifiedItems.forEach(item => item.classList.remove('verified'));
            });
        });
    </script>
</body>

</html>