<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sistem Administrasi</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        .forgot-password-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            width: 100%;
            max-width: 400px;
        }

        .forgot-password-header {
            text-align: center;
            margin-bottom: 1rem;
            color: #0055FF;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .forgot-password-description {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            padding: 0 1rem;
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
            height: 35px;
        }

        .form-control:focus {
            outline: none;
            border-color: #0055FF;
            box-shadow: 0 0 0 2px rgba(0, 85, 255, 0.1);
        }

        .reset-button {
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

        .reset-button:hover {
            background-color: #0044CC;
        }

        .alert {
            margin-top: 5px;
            margin-bottom: 5px;
            padding: 5px;
            font-size: 0.8rem;
        }

        .back-to-login {
            text-align: center;
            margin-top: 1rem;
        }

        .back-to-login a {
            color: #0055FF;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .back-to-login i {
            font-size: 0.8rem;
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
        <div class="forgot-password-card">
            <div class="forgot-password-header">Lupa Password</div>
            <div class="forgot-password-description">
                Masukkan email Anda dan kami akan mengirimkan link untuk mereset password Anda.
            </div>

            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <?php if (isset($success)) { ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php } ?>

            <form action="process_forgot_password.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email anda" required>
                </div>
                <button type="submit" class="reset-button">Kirim Kode OTP</button>

                <div class="back-to-login">
                    <a href="login.php">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Login
                    </a>
                </div>
            </form>
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