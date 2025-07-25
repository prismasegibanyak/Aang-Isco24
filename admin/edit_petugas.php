<?php
session_start();
include '../config/db.php';

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan pengguna sudah login sebagai admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

// Periksa apakah parameter "nip" ada
if (!isset($_GET['nip'])) {
    die("NIP petugas tidak ditemukan.");
}

$nip = $_GET['nip'];

// Ambil data petugas berdasarkan NIP
$sql = "SELECT * FROM petugas WHERE nip_petugas = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Petugas dengan NIP tersebut tidak ditemukan.");
}

$petugas = $result->fetch_assoc();

// Proses form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];

    // Validasi input
    if (empty($nama) || empty($username) || empty($email) || empty($no_hp)) {
        $error = "Semua kolom harus diisi.";
    } else {
        // Hash password jika ada perubahan
        $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : $petugas['password_petugas'];

        // Update data petugas
        $updateSql = "UPDATE petugas SET nama_petugas = ?, username_petugas = ?, password_petugas = ?, email_petugas = ?, no_hp_petugas = ? WHERE nip_petugas = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssssss", $nama, $username, $hashedPassword, $email, $no_hp, $nip);

        if ($updateStmt->execute()) {
            $success = "Data petugas berhasil diperbarui.";
            $petugas['nama_petugas'] = $nama;
            $petugas['username_petugas'] = $username;
            $petugas['email_petugas'] = $email;
            $petugas['no_hp_petugas'] = $no_hp;
        } else {
            $error = "Terjadi kesalahan saat memperbarui data: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Petugas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #2A3132;
            --secondary-color: #336B87;
            --accent-color: #90AFC5;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --sidebar-width: 280px;
            --border-color: #dee2e6;
            --text-color: #212529;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(rgba(255,255,255,0.8),rgba(255,255,255,0.8)), url(../assets/img/2.jpg);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: var(--dark-color);
            overflow-x: hidden;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            padding: 20px 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }
        .sidebar.visible { transform: translateX(0); }
        .sidebar .logo {
            padding: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }
        .sidebar .logo img {
            max-width: 80%;
            height: auto;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .sidebar .logo img:hover { transform: scale(1.05); }
        .menu-title { font-size: 1.4rem; font-weight: 600; margin-top: 10px; color: white; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 10px 0; }
        .sidebar a {
            display: flex; align-items: center;
            padding: 14px 25px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            border-radius: 6px;
            margin: 6px 15px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        .sidebar a i {
            margin-right: 12px; font-size: 1.1rem; width: 24px; text-align: center;
        }
        #toggleSidebar, .toggle-sidebar-btn {
            position: fixed;
            top: 20px; left: 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 1100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        #toggleSidebar:hover, .toggle-sidebar-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--light-color);
        }
        .main-content.expanded, .main-content.active { margin-left: 0; }
        .header {
            padding: 20px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0; z-index: 900;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-title { font-size: 1.5rem; font-weight: 600; color: var(--primary-color); }
        .user-info { font-size: 0.9rem; color: var(--secondary-color); }
        .edit-pegawai-content, .edit-petugas-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
        }
        .register-form {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 32px 24px 24px 24px;
            margin: 30px 0;
        }
        .register-form h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 18px;
            font-weight: 600;
        }
        .register-form h3 {
            text-align: center;
            color: var(--secondary-color);
            margin-bottom: 15px;
            font-size: 1.07rem;
        }
        .input-group {
            margin-bottom: 16px;
        }
        .input-group label {
            display: block;
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 6px;
        }
        .input-group input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1.02rem;
            background: #f7fafd;
            color: var(--primary-color);
        }
        .input-group input:read-only { background: #f2f2f2; }
        .input-group .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 16px;
            top: 37px;
            font-size: 1.07rem;
            color: #999;
            user-select: none;
        }
        button[type="submit"] {
            width: 100%;
            background: var(--secondary-color);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 1.06rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: var(--primary-color);
        }
        .error {
            color: var(--danger-color);
            text-align: center;
            margin-bottom: 10px;
        }
        .success {
            color: var(--success-color);
            text-align: center;
            margin-bottom: 10px;
        }
        .footer {
            position: fixed;
            left: 0; bottom: 0;
            width: 100%;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            text-align: center;
            padding: 20px 0;
            z-index: 100;
        }
        .footer p { margin: 0; color: var(--secondary-color); font-size: 1.2rem; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.visible { transform: translateX(0); }
            .main-content { margin-left: 0; }
            #toggleSidebar, .toggle-sidebar-btn { display: flex !important; }
        }
        @media (min-width: 993px) {
            .sidebar { transform: translateX(0); }
            .main-content { margin-left: var(--sidebar-width); }
            #toggleSidebar, .toggle-sidebar-btn { display: none !important; }
        }
        @media (max-width: 600px) {
            .register-form { padding: 16px 5px 16px 5px; }
            .footer p { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <button id="toggleSidebar"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../assests/img/logoo.png" alt="SiPAK Logo">
            <h2 class="menu-title">Menu Admin</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="home_admin.php"><i class="fas fa-home"></i> Home</a>
            <a href="dashboard1.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="daftar_seluruh_konsultasi.php"><i class="fas fa-list"></i> Daftar Usulan</a>
            <a href="daftar_konsultasi_belum_dijawab.php"><i class="fas fa-clock"></i> Belum Dijawab</a>
            <a href="daftar_konsultasi_dijawab.php"><i class="fas fa-check-circle"></i> Sudah Dijawab</a>
            <a href="profile_pegawai.php"><i class="fas fa-users"></i> Profil Pegawai</a>
            <a href="profile_petugas.php" class="active"><i class="fas fa-user-shield"></i> Profil Petugas</a>
            <a href="register_admin.php"><i class="fas fa-user-plus"></i> Registrasi Admin</a>
            <a href="register_petugas.php"><i class="fas fa-user-edit"></i> Registrasi Petugas</a>
            <a href="../web/logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>
    <div class="main-content" id="mainContent">
        <header class="header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <span>Selamat Datang Admin, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </header>
        <div class="edit-petugas-content">
            <div class="register-form">
                <h2>Edit Petugas</h2>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="input-group">
                        <label for="nip">NIP Petugas</label>
                        <input type="text" id="nip" name="nip" value="<?php echo htmlspecialchars($petugas['nip_petugas']); ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label for="nama">Nama Petugas</label>
                        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($petugas['nama_petugas']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($petugas['username_petugas']); ?>" required>
                    </div>
                    <div class="input-group" style="position: relative;">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password Anda">
                        <span class="toggle-password" onclick="togglePasswordVisibility()">👁️</span>
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($petugas['email_petugas']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="no_hp">No. HP</label>
                        <input type="text" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($petugas['no_hp_petugas']); ?>" required>
                    </div>
                    <button type="submit">Simpan</button>
                </form>
            </div>
        </div>
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Aang_Isco24. All rights reserved.</p>
        </footer>
    </div>
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
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
            // Highlight active link
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                if (link.getAttribute('href').endsWith(currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>