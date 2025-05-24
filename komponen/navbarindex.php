<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .news-section {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .section-title {
            font-size: 24px;
            color: white;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .news-item {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .news-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .news-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .news-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
        }

        .news-category{
            font-family: Arial, sans-serif;
            font-size: 15px;
            font-weight: bold;
        }

        .news-tag {
            background: #ff0000;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .news-title {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            flex: 1;
        }

        .news-title:hover {
            color: #0066cc;
        }

        .news-date {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
        }

        .notification-icon {
            width: 24px;
            height: 24px;
            vertical-align: middle;
            margin-right: 8px;
        }


        .read-more {
            display: inline-block;
            background: #2196f3;
            color: white;
            padding: 5px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
            transition: background 0.3s ease;
        }

        .read-more:hover {
            background: #1976d2;
        }

        @media (max-width: 768px) {
            .news-header {
                flex-wrap: wrap;
            }

            .news-title {
                width: 100%;
                margin-top: 10px;
                order: 3;
            }

            .news-section {
                padding: 0 10px;
            }
        }
</style>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Logo Universitas" class="logo-image">
            <span class="brand-text">SISTEM ADMINISTRASI</span>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="login.php" class="login-btn">Login</a>
        </div>
    </nav>