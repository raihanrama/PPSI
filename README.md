# 🎓 PPSI - Sistem Informasi Jurusan

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://javascript.com/)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://html.spec.whatwg.org/)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://www.w3.org/Style/CSS/)

> **Sistem Pengelolaan Data Akademik Jurusan Sistem Informasi**  
> Platform terintegrasi untuk mengelola data Praktik Industri (PI), Sidang, dan MSIB (Magang dan Studi Independen Bersertifikat)

---

## 📋 Deskripsi Proyek

PPSI (Proyek Praktikum Sistem Informasi) adalah sebuah aplikasi web yang dikembangkan untuk memenuhi tugas mata kuliah PPSI. Sistem ini dirancang khusus untuk membantu pengelolaan data akademik di Jurusan Sistem Informasi, mencakup:

- ### **Manajemen Data PI** - Pengelolaan data Praktik Industri mahasiswa
- ### **Manajemen Pendaftaran Sidang** - Administrasi jadwal dan data sidang
- ### **Program MSIB** - Tracking dan manajemen program MSIB

## ✨ Fitur-Fitur Utama

### 🔐 **Sistem Autentikasi**
- Login dan registrasi pengguna
- Reset password dengan email verification
- Session management yang aman

### 📈 **Dashboard Admin**
- Overview statistik real-time
- Manajemen data komprehensif
- Interface yang user-friendly

### 📧 **Email Integration**
- Notifikasi otomatis via PHPMailer
- Konfirmasi pendaftaran
- Reset password via email

### 📱 **Responsive Design**
- Mobile-first approach
- Bootstrap framework
- Cross-browser compatibility

## 🛠️ Tech Stack

| Technology | Purpose | Version |
|------------|---------|---------|
| **PHP** | Backend Logic | 7.4+ |
| **Bootstrap** | UI Framework | 5.x |
| **JavaScript** | Frontend Interactivity | ES6+ |
| **PHPMailer** | Email Service | Latest |
| **MySQL** | Database | 5.7+ |
| **HTML5/CSS3** | Frontend Structure | Latest |

## 📁 Struktur Proyek

```
ppsi-project/
├── 📁 admin/              # Panel administrasi
├── 📁 komponen/           # Komponen reusable
├── 📁 mahasiswa/          # Interface mahasiswa
├── 📁 uploads/            # File uploads
├── 📄 index.php           # Halaman utama
├── 📄 login.php           # Sistem login
├── 📄 register.php        # Registrasi user
├── 📄 config.php          # Konfigurasi database
├── 📄 about.php           # Halaman about
├── 📄 style.css           # Custom styling
└── 📄 README.md           # Dokumentasi
```

## 🚀 Instalasi & Setup

### Prerequisites
- **XAMPP/WAMP** atau web server dengan PHP 7.4+
- **MySQL** Database
- **Web Browser** modern

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/username/ppsi-project.git
   cd ppsi-project
   ```

2. **Setup Database**
   ```sql
   CREATE DATABASE ppsi_db;
   -- Import file SQL jika tersedia
   ```

3. **Konfigurasi Database**
   ```php
   // config.php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $database = 'ppsi_db';
   ```

4. **Setup PHPMailer**
   - Konfigurasi SMTP settings di `config.php`
   - Pastikan extension openssl aktif

5. **Jalankan Aplikasi**
   - Akses `http://localhost/ppsi-project`
   - Login dengan akun admin default

## 👨‍💻 Penggunaan

### 🔑 **Login Admin**
```
Username: admin
Password: admin123
```

### 📝 **Fitur Mahasiswa**
- Registrasi akun baru
- Upload dokumen PI/Sidang
- Tracking status aplikasi
- Notifikasi email otomatis

### 👥 **Fitur Admin**
- Dashboard analytics
- CRUD operations untuk semua data
- Export data ke Excel/PDF
- Manajemen user accounts

## 📊 Database Schema

```sql
-- Contoh tabel utama
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255),
    role ENUM('admin', 'mahasiswa'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE praktik_industri (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mahasiswa_id INT,
    perusahaan VARCHAR(100),
    periode_mulai DATE,
    periode_selesai DATE,
    status ENUM('pending', 'approved', 'rejected')
);
```

## 🎨 Screenshot

| Dashboard Admin | Login Page | Student Panel |
|----------------|------------|---------------|
| ![Dashboard](https://via.placeholder.com/250x150?text=Dashboard) | ![Login](https://via.placeholder.com/250x150?text=Login) | ![Student](https://via.placeholder.com/250x150?text=Student) |

## 🔧 Konfigurasi Email

```php
// PHPMailer Configuration
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

## 🤝 Kontribusi

Proyek ini dikembangkan untuk keperluan akademik. Untuk kontribusi:

1. Fork repository ini
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📝 Todo List

- [ ] Implementasi API REST
- [ ] Integrasi dengan sistem kampus
- [ ] Mobile app development
- [ ] Real-time notifications
- [ ] Advanced reporting features
- [ ] Multi-language support

## 🐛 Bug Reports

Jika menemukan bug atau ingin request fitur baru, silakan buat issue di repository ini atau hubungi kami melalui:

📧 Email: [muhammadraihan291003@gmail.com](mailto:muhammadraihan291003@gmail.com)  
📱 WhatsApp: +62-821-1278-0864

## 📄 License

Proyek ini dibuat untuk keperluan edukasi mata kuliah PPSI. Tidak untuk penggunaan komersial.

```
MIT License - Educational Purpose Only
Copyright (c) 2024 [Nama Anda]
```

## 🎯 Roadmap

### Phase 1 (Current) ✅
- [x] Basic CRUD operations
- [x] Authentication system
- [x] Email integration
- [x] Responsive design

### Phase 2 (Future) 📋
- [ ] Advanced analytics
- [ ] Mobile app
- [ ] API development
- [ ] Cloud deployment

---

<div align="center">


Made with ❤️ by **[Kelompok PPS - Muhammad Raihan Ramadhan, Muhamad Fadlan, Kevin Deniswara Harvian, Bobby Risky Susanto, Vhi Andra Pijar Z]**  
Jurusan Sistem Informasi -  [Gunadarma University]

[🔝 Back to top](#-ppsi---sistem-informasi-jurusan)

</div>
