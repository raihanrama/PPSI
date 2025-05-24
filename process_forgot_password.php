<?php
require 'config.php';
date_default_timezone_set('Asia/Jakarta'); // Ensure timezone is set to Asia/Jakarta

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'assets/phpmailer/src/Exception.php';
require 'assets/phpmailer/src/PHPMailer.php';
require 'assets/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Cek apakah email ada di tabel mahasiswa
    $stmt = $pdo->prepare("SELECT npm FROM mahasiswa WHERE email = ?");
    $stmt->execute([$email]);
    $mahasiswa = $stmt->fetch();

    if ($mahasiswa) {
        $npm = $mahasiswa['npm'];
        $otp = rand(10000, 99999); // Kode OTP 5 digit
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // Set expiration time based on Asia/Jakarta

        // Simpan OTP di tabel reset_password
        $stmt = $pdo->prepare("INSERT INTO reset_password (npm, token, expiration, used) VALUES (?, ?, ?, 0) ON DUPLICATE KEY UPDATE token = ?, expiration = ?, used = 0");
        $stmt->execute([$npm, $otp, $expiry, $otp, $expiry]);

        // Kirim email OTP dengan PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'budikenway@gmail.com'; // Gmail address
            $mail->Password = 'mrzl osnu uxkk ithw';  // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Pengaturan pengirim dan penerima
            $mail->setFrom('budikenway@gmail.com', 'Budi Kenway'); // Sesuaikan nama
            $mail->addAddress($email);
            $mail->isHTML(true);

            $mail->Subject = "Kode OTP Reset Password";
            $mail->Body = "Kode OTP Anda adalah: <b>$otp</b>. Kode ini akan kedaluwarsa dalam 1 jam.";

            $mail->send();

            echo "<script>
                alert('Kode OTP telah dikirim ke email Anda.');
                window.location.href = 'reset_password.php';
            </script>";
        } catch (Exception $e) {
            echo "<script>
                alert('Gagal mengirim email: {$mail->ErrorInfo}');
                window.history.back();
            </script>";
        }
    } else {
        // Email tidak ditemukan
        echo "<script>
            alert('Email tidak ditemukan. Silakan cek kembali email Anda.');
            window.history.back();
        </script>";
    }
}
?>