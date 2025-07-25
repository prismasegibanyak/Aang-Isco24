<?php
session_start();
include '../config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $new2 = $_POST['new_password2'];

    if (empty($old) || empty($new) || empty($new2)) {
        $msg = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Semua kolom wajib diisi!</div>';
    } elseif ($new !== $new2) {
        $msg = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Password baru tidak sama!</div>';
    } else {
        $sql = "SELECT password_petugas FROM petugas WHERE username_petugas = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($old, $user['password_petugas'])) {
            $new_hash = password_hash($new, PASSWORD_BCRYPT);
            $sql = "UPDATE petugas SET password_petugas = ? WHERE username_petugas = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_hash, $username);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Password berhasil diganti!</div>';
            } else {
                $msg = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Gagal memperbarui password!</div>';
            }
        } else {
            $msg = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Password lama salah!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password | SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_unified.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-container input {
            flex: 1;
            padding-right: 35px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            cursor: pointer;
            color: #666;
        }
        .toggle-password:hover {
            color: #333;
        }
        .form-section {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .button-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .button {
            flex: 1;
        }
        @media (max-width: 576px) {
            .button-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <button id="toggleSidebar"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../assets/img/logoo.png" alt="Logo SiPAK">
            <h2 class="menu-title">Menu Petugas</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="../web/home_petugas.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="view_konsultasi_petugas.php"><i class="fas fa-list"></i> Daftar Usulan</a>
            <a href="profile2.php"><i class="fas fa-user"></i> Profil</a>
            <a href="../web/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>
    <div class="main-content" id="mainContent">
        <header class="header sticky-header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <h3>Selamat Datang, <?php echo htmlspecialchars($username); ?></h3>
            </div>
        </header>
        <div class="dashboard-content">
            <div class="form-section">
                <h2>Ganti Password</h2>
                <?php echo $msg; ?>
                <form method="POST" action="">
                    <div class="input-group">
                        <label for="old_password">Password Lama</label>
                        <div class="password-container">
                            <input type="password" id="old_password" name="old_password" required>
                            <span class="toggle-password" onclick="togglePassword('old_password', this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="new_password">Password Baru</label>
                        <div class="password-container">
                            <input type="password" id="new_password" name="new_password" required>
                            <span class="toggle-password" onclick="togglePassword('new_password', this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="new_password2">Ulangi Password Baru</label>
                        <div class="password-container">
                            <input type="password" id="new_password2" name="new_password2" required>
                            <span class="toggle-password" onclick="togglePassword('new_password2', this)">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="button-container">
                        <button type="submit" class="button">Simpan</button>
                        <a href="profile2.php" class="button danger">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Aang_Isco24. All rights reserved.</p>
        </footer>
    </div>
    <script>
    function togglePassword(fieldId, element) {
        const passwordField = document.getElementById(fieldId);
        const icon = element.querySelector("i");
        
        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        
        function initSidebar() {
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('visible');
                toggleBtn.style.display = 'flex';
                if (localStorage.getItem('sidebarState') === 'visible') {
                    sidebar.classList.add('visible');
                }
            } else {
                sidebar.classList.add('visible');
                toggleBtn.style.display = 'none';
            }
        }
        
        function toggleSidebar() {
            sidebar.classList.toggle('visible');
            localStorage.setItem('sidebarState', sidebar.classList.contains('visible') ? 'visible' : 'hidden');
        }
        
        function handleResize() {
            if (window.innerWidth <= 992) {
                toggleBtn.style.display = 'flex';
            } else {
                sidebar.classList.add('visible');
                toggleBtn.style.display = 'none';
            }
        }
        
        initSidebar();
        toggleBtn.addEventListener('click', toggleSidebar);
        window.addEventListener('resize', handleResize);
    });
    </script>
</body>
</html>