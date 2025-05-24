<?php
// session_handler.php - Simpan file ini di folder yang bisa diakses semua halaman (misal: di root atau folder 'includes')

function checkSessionTimeout()
{
    // Set timeout 10 menit
    $timeout_duration = 900; // 10 menit dalam detik

    // Cek apakah user sudah login
    if (!isset($_SESSION['npm'])) {
        // Jika belum login, redirect ke login
        header("Location: ../login.php");
        exit;
    }

    // Cek waktu terakhir aktif
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = time() - $_SESSION['last_activity'];

        if ($inactive_time >= $timeout_duration) {
            // Hapus semua data session
            session_unset();
            session_destroy();

            // Buat session baru untuk pesan
            session_start();
            $_SESSION['login_message'] = "Sesi telah berakhir. Silahkan login kembali.";

            // Redirect ke login
            header("Location: ../login.php");
            exit;
        }
    }

    // Update waktu aktivitas terakhir
    $_SESSION['last_activity'] = time();
}