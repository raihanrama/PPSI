<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Sistem Administrasi</title>
    <link rel="icon" href="assets\images\logo Gunadarma.png" type="image/png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #ffffff;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-image {
            height: 40px;
            width: auto;
        }

        .brand-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }

        /* Navigation Links */
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 1rem;
        }

        .nav-links a:hover {
            color: #0066cc;
        }

        .login-btn {
            color: black;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #0052a3;
            color: white;
        }

        /* Container and Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .subtitle {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 3rem;
            text-align: justify;
            max-width: 1600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Enhanced Feature Cards */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .feature-card {
            background: linear-gradient(145deg, #592693, #7439b8);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 350px;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(89, 38, 147, 0.2);
        }

        .feature-icon {
            background: rgba(255, 255, 255, 0.1);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .feature-title::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: white;
            margin-top: 0.5rem;
            transition: width 0.3s ease;
        }

        .feature-card:hover .feature-title::after {
            width: 100px;
        }

        .feature-description {
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Enhanced Workflow Section */
        .workflow-container {
            border: none;
            background: white;
            border-radius: 20px;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .steps-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 1.5rem;
            padding: 1rem 0;
            flex-wrap: wrap;
        }

        .step {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-weight: bold;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            margin: 5px;
            padding: 0.5rem;
        }

        .step:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .step-text {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            text-align: center;
            opacity: 0.9;
            line-height: 1.1;
        }

        .step-number {
            font-size: 1.5rem;
            margin-bottom: 0.3rem;
        }

        .titlestep {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
            text-align: center;
            font-family: "Times New Roman", Times, serif;
        }

        /* Progress Bar Styles */
        .progress-bar {
            height: 4px;
            background: #eee;
            border-radius: 2px;
            margin: 1rem 0;
            position: relative;
        }

        .progress {
            height: 100%;
            border-radius: 2px;
            width: 0;
            transition: width 0.3s ease;
        }

        /* Responsive Design Updates */
        @media (max-width: 768px) {
            .workflow-container {
                padding: 1rem;
            }

            .step {
                width: 90px;
                height: 90px;
            }

            .step-text {
                font-size: 0.75rem;
            }

            .titlestep {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .step {
                width: 80px;
                height: 80px;
            }

            .step-text {
                font-size: 0.7rem;
            }

            .titlestep {
                font-size: 1.2rem;
            }
        }

        /* Hamburger Menu Styles */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 25px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
        }

        .hamburger span {
            width: 30px;
            height: 3px;
            background: #333;
            border-radius: 10px;
            transition: all 0.3s linear;
            position: relative;
            transform-origin: 1px;
        }

        /* Specific Step Colors */
        .step-ilmiah {
            background-color: #592693;
        }

        .step-sarjana {
            background-color: #6baf5f;
        }

        .step-msib {
            background-color: #4a6fa1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }

            .hamburger {
                display: flex;
                position: absolute;
                top: 1rem;
                right: 1rem;
            }

            .logo-container {
                margin-bottom: 1rem;
            }

            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                padding: 1rem 0;
            }

            .nav-links.active {
                display: flex;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .step {
                min-width: 100px;
                height: 100px;
            }

            .step-text {
                font-size: 0.9rem;
            }

            .titlestep,
            .titlestep1 {
                font-size: 1.4rem;
            }

            .container {
                padding: 1rem;
            }

            .feature-card {
                min-height: auto;
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .workflow-container {
                padding: 1.5rem 1rem;
            }

            .steps-container {
                gap: 20px;
            }

            .step {
                min-width: 90px;
                height: 90px;
            }

            .step-text {
                font-size: 0.8rem;
            }

            .title {
                font-size: 1.5rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .feature-title {
                font-size: 1.3rem;
            }

            .feature-description {
                font-size: 0.9rem;
            }
        }

        /* Utility Classes */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        .fade-out {
            animation: fadeOut 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Logo Universitas" class="logo-image">
            <span class="brand-text">SISTEM ADMINISTRASI UNIVERSITAS GUNADARMA</span>
            <link rel="stylesheet" href="style.css">
        </div>
        <button class="hamburger" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="login.php" class="login-btn">Login</a>
        </div>
    </nav>


    <div class="container">
        <h1 class="title">Sistem Administrasi Program Studi Sistem Informasi</h1>
        <p class="subtitle">
            Sistem ini dirancang untuk membantu mahasiswa dan staf administrasi dalam menyelesaikan berbagai
            proses administrasi akademik secara efisien dan terstruktur. Dimana administrasi yang dapat dilakukan
            didalam sistem ini adalah seperti berikut:
        </p>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="feature-title">Status Penelitian Ilmiah</h3>
                <p class="feature-description">
                    Memudahkan mahasiswa untuk melakukan pengecekan status penelitian ilmiah secara real-time
                    dan mendapatkan informasi terkini mengenai progress penelitian.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="feature-title">Pendaftaran Sidang Sarjana</h3>
                <p class="feature-description">
                    Sistem terpadu untuk proses pendaftaran sidang sarjana dengan sistem yang efisien
                    dan terorganisir untuk kelancaran proses sidang para mahasiswa.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="feature-title">Surat Rekomendasi MSIB</h3>
                <p class="feature-description">
                    Layanan pembuatan surat rekomendasi untuk program Magang dan Studi Independen
                    Bersertifikat (MSIB) secara efektif dan efisien.
                </p>
            </div>
        </div>

        <!-- Workflow sections -->
        <div class="workflow-container">
            <h1 class="titlestep">1. Penelitian Ilmiah Semester 6</h1>
            <div class="progress-bar">
                <div class="progress" id="progress1"></div>
            </div>
            <div class="steps-container" id="workflow1">
            </div>
        </div>
        <div class="workflow-container">
            <h1 class="titlestep">2. Daftar Online Sidang Sarjana</h1>
            <div class="progress-bar">
                <div class="progress" id="progress2"></div>
            </div>
            <div class="steps-container" id="workflow2">
            </div>
        </div>
        <div class="workflow-container">
            <h1 class="titlestep">3. MSIB</h1>
            <div class="progress-bar">
                <div class="progress" id="progress3"></div>
            </div>
            <div class="steps-container" id="workflow3">
            </div>
        </div>
        <script>
            const steps = {
                'penelitian': [
                    'ACC PI', 'Daftar Sidang', 'Sidang PI', 'ACC Revisi PI',
                    'TTD Kasubag', 'TTD Kajur', 'Hardcover', 'Sumbang Buku', 'Sertifikat PI'
                ],
                'sarjana': [
                    'Ajukan Sidang', 'Upload Dokumen', 'Verifikasi', 'Diverifikasi'
                ],
                'msib': [
                    'Ajukan SR', 'Upload Dokumen', 'Verifikasi', 'Surat Rekomendasi'
                ]
            };

            function createSteps(containerId, stepsArray, color) {
                const container = document.getElementById(containerId);
                if (!container) return;

                container.innerHTML = ''; // Clear existing content

                stepsArray.forEach((step, index) => {
                    const stepElement = document.createElement('div');
                    stepElement.className = 'step';
                    stepElement.style.backgroundColor = color;
                    stepElement.innerHTML = `
                <span class="step-number">${index + 1}</span>
                <span class="step-text">${step}</span>
            `;
                    container.appendChild(stepElement);
                });
            }

            function updateProgress(progressId, stepsComplete, totalSteps) {
                const progressBar = document.getElementById(progressId);
                if (!progressBar) return;
                const percentage = (stepsComplete / totalSteps) * 100;
                progressBar.style.width = `${percentage}%`;
            }

            document.addEventListener('DOMContentLoaded', function () {
                createSteps('workflow1', steps.penelitian, '#592693');
                createSteps('workflow2', steps.sarjana, '#6baf5f');
                createSteps('workflow3', steps.msib, '#4a6fa1');

                updateProgress('progress1', 1, 9);
                updateProgress('progress2', 1, 5);
                updateProgress('progress3', 1, 5);
            });
        </script>
</body>

</html>




