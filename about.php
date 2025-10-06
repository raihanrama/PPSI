<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Sistem Administrasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Navbar Modern */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.2rem 5%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-image {
            height: 45px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo-image:hover {
            transform: scale(1.05);
        }

        .brand-text {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #592693, #7439b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 1rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #592693, #7439b8);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .login-btn {
            background: linear-gradient(135deg, #592693, #7439b8);
            color: white !important;
            padding: 0.7rem 2rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(89, 38, 147, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(89, 38, 147, 0.4);
        }

        .login-btn::after {
            display: none;
        }

        /* Hero Section */
        .hero-section {
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 5%;
            text-align: center;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #592693, #7439b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            animation: fadeInDown 0.8s ease;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #666;
            line-height: 1.8;
            max-width: 900px;
            margin: 0 auto 4rem;
            animation: fadeInUp 0.8s ease;
        }

        /* Feature Cards - Modern Grid */
        .features-grid {
            max-width: 1400px;
            margin: 0 auto 5rem;
            padding: 0 5%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .feature-card {
            background: white;
            border-radius: 24px;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(135deg, #592693, #7439b8);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 60px rgba(89, 38, 147, 0.15);
        }

        .feature-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, #592693, #7439b8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            transition: all 0.4s ease;
        }

        .feature-card:hover .feature-icon-wrapper {
            transform: rotate(10deg) scale(1.1);
        }

        .feature-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .feature-description {
            color: #666;
            line-height: 1.7;
            font-size: 1.05rem;
        }

        /* Workflow Section - Modern Timeline */
        .workflow-section {
            max-width: 1400px;
            margin: 0 auto 3rem;
            padding: 0 5%;
        }

        .workflow-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .workflow-card:hover {
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
        }

        .workflow-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .workflow-number {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
        }

        .workflow-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
        }

        .progress-container {
            background: #f0f0f0;
            height: 8px;
            border-radius: 50px;
            overflow: hidden;
            margin-bottom: 2.5rem;
        }

        .progress-bar {
            height: 100%;
            border-radius: 50px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .steps-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }

        .step-item {
            flex: 0 1 calc(20% - 1rem);
            min-width: 140px;
            background: white;
            border-radius: 16px;
            padding: 1.5rem 1rem;
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
            border: 2px solid #f0f0f0;
        }

        .step-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: white;
        }

        .step-text {
            color: #555;
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.4;
        }

        /* Color Schemes */
        .penelitian-color {
            background: linear-gradient(135deg, #592693, #7439b8);
        }

        .sarjana-color {
            background: linear-gradient(135deg, #6baf5f, #4a9b3d);
        }

        .msib-color {
            background: linear-gradient(135deg, #4a6fa1, #366290);
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .hamburger span {
            width: 28px;
            height: 3px;
            background: #592693;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .features-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }

            .step-item {
                flex: 0 1 calc(25% - 1rem);
            }
        }

        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 2rem;
                gap: 1.5rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                display: none;
            }

            .nav-links.active {
                display: flex;
            }

            .hero-title {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .step-item {
                flex: 0 1 calc(50% - 1rem);
            }

            .workflow-card {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 1rem 4%;
            }

            .hero-title {
                font-size: 1.8rem;
            }

            .step-item {
                flex: 0 1 100%;
            }

            .workflow-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Logo Universitas" class="logo-image">
                <span class="brand-text">SISTEM ADMINISTRASI</span>
            </div>
            <button class="hamburger" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="login.php" class="login-btn">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title">Sistem Administrasi Program Studi</h1>
        <p class="hero-subtitle">
            Sistem ini dirancang untuk membantu mahasiswa dan staf administrasi dalam menyelesaikan berbagai
            proses administrasi akademik secara efisien dan terstruktur. Dimana administrasi yang dapat dilakukan
            di dalam sistem ini adalah sebagai berikut:
        </p>
    </section>

    <!-- Features Grid -->
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon-wrapper">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <div class="feature-icon-wrapper">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="feature-title">Pendaftaran Sidang Sarjana</h3>
            <p class="feature-description">
                Sistem terpadu untuk proses pendaftaran sidang sarjana dengan sistem yang efisien
                dan terorganisir untuk kelancaran proses sidang para mahasiswa.
            </p>
        </div>

        <div class="feature-card">
            <div class="feature-icon-wrapper">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="feature-title">Surat Rekomendasi MSIB</h3>
            <p class="feature-description">
                Layanan pembuatan surat rekomendasi untuk program Magang dan Studi Independen
                Bersertifikat (MSIB) secara efektif dan efisien.
            </p>
        </div>
    </div>

    <!-- Workflow Section -->
    <div class="workflow-section">
        <!-- Penelitian Ilmiah -->
        <div class="workflow-card">
            <div class="workflow-header">
                <div class="workflow-number penelitian-color">1</div>
                <h2 class="workflow-title">Penelitian Ilmiah</h2>
            </div>
            <div class="progress-container">
                <div class="progress-bar penelitian-color" style="width: 11%"></div>
            </div>
            <div class="steps-container" id="workflow1"></div>
        </div>

        <!-- Sidang Sarjana -->
        <div class="workflow-card">
            <div class="workflow-header">
                <div class="workflow-number sarjana-color">2</div>
                <h2 class="workflow-title">Sidang Sarjana</h2>
            </div>
            <div class="progress-container">
                <div class="progress-bar sarjana-color" style="width: 25%"></div>
            </div>
            <div class="steps-container" id="workflow2"></div>
        </div>

        <!-- MSIB -->
        <div class="workflow-card">
            <div class="workflow-header">
                <div class="workflow-number msib-color">3</div>
                <h2 class="workflow-title">MSIB</h2>
            </div>
            <div class="progress-container">
                <div class="progress-bar msib-color" style="width: 25%"></div>
            </div>
            <div class="steps-container" id="workflow3"></div>
        </div>
    </div>

    <script>
        const workflows = {
            penelitian: ['ACC PI', 'Daftar Sidang', 'Sidang PI', 'ACC Revisi PI', 'TTD Kasubag', 'TTD Kajur', 'Hardcover', 'Sumbang Buku', 'Sertifikat PI'],
            sarjana: ['Ajukan Sidang', 'Upload Dokumen', 'Verifikasi', 'Diverifikasi'],
            msib: ['Ajukan SR', 'Upload Dokumen', 'Verifikasi', 'Surat Rekomendasi']
        };

        function createWorkflow(containerId, steps, colorClass) {
            const container = document.getElementById(containerId);
            steps.forEach((step, index) => {
                const stepEl = document.createElement('div');
                stepEl.className = 'step-item';
                stepEl.innerHTML = `
                    <div class="step-number ${colorClass}">${index + 1}</div>
                    <div class="step-text">${step}</div>
                `;
                container.appendChild(stepEl);
            });
        }

        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        // Initialize workflows
        createWorkflow('workflow1', workflows.penelitian, 'penelitian-color');
        createWorkflow('workflow2', workflows.sarjana, 'sarjana-color');
        createWorkflow('workflow3', workflows.msib, 'msib-color');
    </script>
</body>
</html>
