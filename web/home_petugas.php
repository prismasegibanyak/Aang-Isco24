<?php
session_start();
include '../config/db.php';

// Error reporting untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect jika belum login atau bukan petugas
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil data petugas
$sql = "SELECT nip_petugas, nama_petugas FROM petugas WHERE username_petugas = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Petugas tidak ditemukan.");
}

$petugas = $result->fetch_assoc();
$nama_petugas = htmlspecialchars($petugas['nama_petugas']);

// Fungsi untuk mendapatkan jumlah data
function getCount($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return 0;
    }
}

// Ambil data statistik (dengan auto-refresh jika ada parameter updated)
$total = getCount($conn, "SELECT COUNT(*) as total FROM usulan");
$proses = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE status = 'proses'");
$selesai = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE status = 'selesai'");
$pihak_ke_3 = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE status = 'pihak ke-3'");
$tinggi = getCount($conn, "SELECT COUNT(*) as total FROM jwb_usulan WHERE level_resiko = 'Tinggi'");
$sedang = getCount($conn, "SELECT COUNT(*) as total FROM jwb_usulan WHERE level_resiko = 'Sedang'");
$rendah = getCount($conn, "SELECT COUNT(*) as total FROM jwb_usulan WHERE level_resiko = 'Rendah'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPAK | Dashboard Petugas</title>
    <link rel="stylesheet" href="../assets/css/styles_unified2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Auto refresh jika ada perubahan data -->
    <?php if (isset($_GET['updated'])): ?>
    <meta http-equiv="refresh" content="2;url=home_petugas.php">
    <?php endif; ?>
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
            <h2 class="menu-title">Menu Petugas</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="home_petugas.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="../consultation/view_konsultasi_petugas.php">
                <i class="fas fa-list"></i> Daftar Usulan
            </a>
            <a href="../consultation/profile2.php">
                <i class="fas fa-user"></i> Profil
            </a>
            <a href="../web/logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <header class="header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <h3>Selamat Datang, <?php echo htmlspecialchars($nama_petugas); ?></h3>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Notifikasi -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="welcome-section">
                <h1>Dashboard Petugas</h1>
                <?php if (isset($_GET['updated'])): ?>
                    <p class="text-info">Memperbarui data...</p>
                <?php endif; ?>
            </div>

            <!-- Proposal Status Cards -->
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
                        <h3>Usulan Dalam Proses</h3>
                        <div class="stat-value"><?php echo $proses; ?></div>
                    </div>
                </div>

                <div class="stat-card selesai">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Usulan Selesai</h3>
                        <div class="stat-value"><?php echo $selesai; ?></div>
                    </div>
                </div>

                <div class="stat-card pihak-ke-3">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Usulan Pihak Ke-3</h3>
                        <div class="stat-value"><?php echo $pihak_ke_3; ?></div>
                    </div>
                </div>
            </div>

            <!-- Risk Level Cards -->
            <div class="stats-grid">
                <div class="stat-card tinggi">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Level Resiko Tinggi</h3>
                        <div class="stat-value"><?php echo $tinggi; ?></div>
                    </div>
                </div>

                <div class="stat-card sedang">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Level Resiko Sedang</h3>
                        <div class="stat-value"><?php echo $sedang; ?></div>
                    </div>
                </div>

                <div class="stat-card rendah">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Level Resiko Rendah</h3>
                        <div class="stat-value"><?php echo $rendah; ?></div>
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
        const mainContent = document.getElementById('mainContent');
        
        // Inisialisasi sidebar
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
        
        // Toggle sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('visible');
            localStorage.setItem('sidebarState', sidebar.classList.contains('visible') ? 'visible' : 'hidden');
        }
        
        // Handle resize
        function handleResize() {
            if (window.innerWidth <= 992) {
                toggleBtn.style.display = 'flex';
            } else {
                sidebar.classList.add('visible');
                toggleBtn.style.display = 'none';
            }
        }
        
        // Highlight menu aktif
        function highlightActiveMenu() {
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                if (link.getAttribute('href').endsWith(currentPage)) {
                    link.classList.add('active');
                }
            });
        }
        
        // Inisialisasi
        initSidebar();
        highlightActiveMenu();
        toggleBtn.addEventListener('click', toggleSidebar);
        window.addEventListener('resize', handleResize);
        
        // Auto close alert setelah 5 detik
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    });
    </script>
</body>
</html>