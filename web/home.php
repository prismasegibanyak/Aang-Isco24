<?php
session_start();
include '../config/db.php';

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable in production
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'pegawai') {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

$sql = "SELECT nip_pegawai, nama_pegawai FROM pegawai WHERE username_pegawai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Pegawai tidak ditemukan.");
}

$pegawai = $result->fetch_assoc();
$nama_pegawai = htmlspecialchars($pegawai['nama_pegawai']);
$nip_pegawai = $pegawai['nip_pegawai'];

function getCount($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'] ?? 0;
}

$total = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE nip_pegawai = ?", [$nip_pegawai]);
$proses = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE nip_pegawai = ? AND status = 'proses'", [$nip_pegawai]);
$selesai = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE nip_pegawai = ? AND status = 'selesai'", [$nip_pegawai]);
$pihak_ke_3 = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE nip_pegawai = ? AND status = 'pihak ke-3'", [$nip_pegawai]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPAK | Dashboard Pegawai</title>
    <link rel="stylesheet" href="../assets/css/styles_unified2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Auto-logout script -->
    <script>
    let inactivityTime = function() {
        let time;
        const logoutTime = 15 * 60 * 1000; // 15 minutes
        
        const resetTimer = () => {
            clearTimeout(time);
            time = setTimeout(logout, logoutTime);
        };
        
        const logout = () => {
            window.location.href = '../web/logout.php';
        };
        
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
    };
    inactivityTime();
    </script>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../assets/img/logoo.png" alt="SiPAK Logo">
            <h2 class="menu-title">Menu Pegawai</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="home.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="../consultation/view_consultations.php">
                <i class="fas fa-list-check"></i> Daftar Usulan
            </a>
            <a href="../consultation/consultation.php">
                <i class="fas fa-plus-circle"></i> Usulan Baru
            </a>
            <a href="../consultation/profile1.php">
                <i class="fas fa-user-cog"></i> Profil
            </a>
            <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <header class="header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <h3>Selamat Datang, <?php echo htmlspecialchars($nama_pegawai); ?></h3>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="welcome-section">
                <h1>Dashboard Pegawai</h1>
            </div>

            <!-- Statistik Usulan -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Total Usulan</h3>
                        <div class="stat-value"><?php echo $total; ?></div>
                    </div>
                </div>

                <div class="stat-card proses">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Dalam Proses</h3>
                        <div class="stat-value"><?php echo $proses; ?></div>
                    </div>
                </div>

                <div class="stat-card selesai">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Selesai</h3>
                        <div class="stat-value"><?php echo $selesai; ?></div>
                    </div>
                </div>

                <div class="stat-card pihak-ke-3">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Pihak Ke-3</h3>
                        <div class="stat-value"><?php echo $pihak_ke_3; ?></div>
                    </div>
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