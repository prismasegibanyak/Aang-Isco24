<?php
session_start();
include '../config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'pegawai') {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

$sql = "SELECT nip_pegawai, nama_pegawai, username_pegawai, email_pegawai, no_hp_pegawai FROM pegawai WHERE username_pegawai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Pegawai tidak ditemukan.");
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pegawai | SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_unified.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <button id="toggleSidebar"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../logoo.png" alt="Logo SiPAK">
            <h2 class="menu-title">Menu Pegawai</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="../web/home.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="view_consultations.php"><i class="fas fa-list-check"></i> Daftar Usulan</a>
            <a href="consultation.php"><i class="fas fa-plus-circle"></i> Usulan Baru</a>
            <a href="profile1.php" class="active"><i class="fas fa-user-cog"></i> Profil</a>
            <a href="../web/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
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
            <div class="profile-section">
                <h2>Profil Pegawai</h2>
                <table class="profile-table">
                    <tr>
                        <th>NIP:</th>
                        <td><?php echo htmlspecialchars($user['nip_pegawai']); ?></td>
                    </tr>
                    <tr>
                        <th>Nama:</th>
                        <td><?php echo htmlspecialchars($user['nama_pegawai']); ?></td>
                    </tr>
                    <tr>
                        <th>Username:</th>
                        <td><?php echo htmlspecialchars($user['username_pegawai']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($user['email_pegawai']); ?></td>
                    </tr>
                    <tr>
                        <th>No HP:</th>
                        <td><?php echo htmlspecialchars($user['no_hp_pegawai']); ?></td>
                    </tr>
                </table>
                <div class="button-container">
                    <a href="change_password1.php" class="button">Ganti Password</a>
                </div>
            </div>
        </div>
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Aang_Isco24. All rights reserved.</p>
        </footer>
    </div>
    <script>
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
        
        // Highlight current menu
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