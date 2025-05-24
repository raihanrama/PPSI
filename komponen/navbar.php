<?php
// Pastikan user sudah login, jika tidak redirect ke halaman login
if (!isset($_SESSION['npm'])) {
    header("Location: ../login.php");
    exit;
}

// Dapatkan role dari session
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Administrasi</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
/* Reset default styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
}

/* Navbar style with gradient */
nav {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 15px 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: all 0.3s ease;
}

nav:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

/* Brand style with animation */
.nav-brand {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.1rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    margin-right: 40px;
    position: relative;
    transition: all 0.3s ease;
}

.nav-brand::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 0;
    height: 2px;
    background: #3498db;
    transition: width 0.3s ease;
}

.nav-brand:hover::after {
    width: 100%;
}

.nav-brand i {
    margin-right: 10px;
    color: #3498db;
    transition: transform 0.3s ease;
}

.nav-brand:hover i {
    transform: rotate(360deg);
}

/* Navigation menu with modern styling */
.nav-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    flex-grow: 1;
    gap: 10px;
}

.nav-menu li a {
    color: #576574;
    text-decoration: none;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    padding: 10px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-menu li a::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.nav-menu li a:hover, .nav-menu li a.active {
    background-color: #e3f2fd;
    color: #2980b9;
    transform: translateY(-2px);
}

.nav-menu li a:hover::before, .nav-menu li a.active::before {
    left: 100%;
}

.nav-menu li a i {
    margin-right: 8px;
    transition: transform 0.3s ease;
}

.nav-menu li a:hover i {
    transform: scale(1.2);
}

/* Logout button with modern design */
.logout-btn {
    background: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(214, 48, 49, 0.2);
}

.logout-btn i {
    margin-right: 8px;
    transition: transform 0.3s ease;
}

.logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(214, 48, 49, 0.3);
    background: linear-gradient(135deg, #ff8b8b 0%, #e03131 100%);
}

.logout-btn:hover i {
    transform: translateX(3px);
}

/* Tooltips */
[data-tooltip] {
    position: relative;
}

[data-tooltip]:before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    padding: 5px 10px;
    background: #2c3e50;
    color: white;
    font-size: 0.8rem;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

[data-tooltip]:hover:before {
    opacity: 1;
    visibility: visible;
}

/* Responsive design */
@media (max-width: 1024px) {
    nav {
        padding: 15px 20px;
    }

    .nav-menu {
        gap: 5px;
    }

    .nav-menu li a {
        padding: 8px 12px;
    }
}

@media (max-width: 768px) {
    .nav-brand span {
        display: none;
    }

    .nav-menu li a span {
        display: none;
    }

    .nav-menu li a {
        padding: 8px;
    }

    .nav-menu li a i {
        margin: 0;
        font-size: 1.2rem;
    }

    .logout-btn span {
        display: none;
    }

    .logout-btn {
        padding: 8px;
    }

    .logout-btn i {
        margin: 0;
    }
}
    </style>
</head>
<body>
    <nav>
        <a href="#" class="nav-brand" data-tooltip="Dashboard">
            <i class="fas fa-building"></i>
            <span>Sistem Administrasi</span>
        </a>
        
        <ul class="nav-menu">
            <li>
                <a href="super_admin.php" class="active" data-tooltip="Beranda">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <?php if ($role == 'super_admin'): ?>
                <li>
                    <a href="admin_akun.php" data-tooltip="Kelola Akun">
                        <i class="fas fa-user-plus"></i>
                        <span>Tambah Akun</span>
                    </a>
                </li>
                <li>
                    <a href="admin_pi.php" data-tooltip="Kelola Penelitian">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Penelitian Ilmiah (PI)</span>
                    </a>
                </li>
                <li>
                    <a href="admin_sidang.php" data-tooltip="Kelola Sidang">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Sidang Skripsi</span>
                    </a>
                </li>
                <li>
                    <a href="admin_msib.php" data-tooltip="Kelola MSIB">
                        <i class="fas fa-briefcase"></i>
                        <span>MSIB</span>
                    </a>
                </li>
                <li>
                    <a href="admin_informasi.php" data-tooltip="Kelola Berita">
                        <i class="fas fa-newspaper"></i>
                        <span>Berita</span>
                    </a>
                </li>
            <?php elseif ($role == 'admin_pi'): ?>
                <li>
                    <a href="admin_pi.php" data-tooltip="Kelola Penelitian">
                        <i class="fas fa-flask"></i>
                        <span>Penelitian Ilmiah (PI)</span>
                    </a>
                </li>
            <?php elseif ($role == 'admin_sidang'): ?>
                <li>
                    <a href="admin_sidang.php" data-tooltip="Kelola Sidang">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Sidang Skripsi</span>
                    </a>
                </li>
            <?php elseif ($role == 'admin_msib'): ?>
                <li>
                    <a href="admin_msib.php" data-tooltip="Kelola MSIB">
                        <i class="fas fa-briefcase"></i>
                        <span>MSIB</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <a href="../logout.php" class="logout-btn" data-tooltip="Keluar">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <!-- Optional JavaScript for additional interactivity -->
    <script>
        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.href;
            const menuItems = document.querySelectorAll('.nav-menu a');
            
            menuItems.forEach(item => {
                if(item.href === currentLocation) {
                    item.classList.add('active');
                }
            });
        });

        // Scroll effect for navbar
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if(window.scrollY > 0) {
                nav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
            } else {
                nav.style.boxShadow = '0 4px 12px rgba(0,0,0,0.05)';
            }
        });
    </script>
</body>
</html>