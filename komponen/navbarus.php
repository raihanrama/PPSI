<nav class="navbar navbar-expand-lg custom-navbar sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="fas fa-university me-2"></i>
            <span>Sistem Administrasi</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Main Navigation -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../mahasiswa/mahasiswa.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../mahasiswa/cek_sidang.php">
                        <i class="fas fa-tasks me-1"></i> Cek Sidang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../mahasiswa/cek_msib.php">
                        <i class="fas fa-briefcase me-1"></i> Cek MSIB
                    </a>
                </li>
            </ul>

            <!-- User Menu -->
            <ul class="navbar-nav">
                <li class="nav-item me-2">
                    <a class="nav-link" href="../mahasiswa/profile.php">
                        <i class="fas fa-user-edit me-1"></i> Profil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-danger" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
.custom-navbar {
    background-color: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1rem 0;
    font-family: Arial, sans-serif;
}

.navbar-brand {
    font-weight: 600;
    color: #2c3e50 !important;
    font-size: 1.25rem;
}

.nav-link {
    color: #34495e !important;
    font-weight: 500;
    padding: 0.5rem 1rem !important;
    transition: all 0.3s ease;
    border-radius: 5px;
    margin: 0 0.2rem;
}

.nav-link:hover {
    background-color: #f8f9fa;
    color: #e74c3c !important;
}

.btn-danger {
    background-color: #e74c3c;
    border: none;
    padding: 0.5rem 1.25rem;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background-color: #c0392b;
    transform: translateY(-1px);
}

@media (max-width: 991.98px) {
    .navbar-nav {
        padding: 1rem 0;
    }
    
    .nav-link {
        padding: 0.75rem 1rem !important;
    }
    
    .btn-danger {
        margin-top: 0.5rem;
        width: 100%;
    }
    
    .nav-item.me-2 {
        margin-right: 0 !important;
    }
}
</style>

<!-- Required FontAwesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">