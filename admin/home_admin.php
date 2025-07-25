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

$username = $_SESSION['username'];

// Query dengan prepared statement untuk mencegah SQL injection
$sql = "SELECT nip_petugas FROM petugas WHERE username_petugas = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$petugas = $result->fetch_assoc();

if (!$petugas) {
    die("Petugas tidak ditemukan.");
}

$nip_petugas = $petugas['nip_petugas'];

// Fungsi untuk mendapatkan jumlah data dengan error handling
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

// Data usulan
$total = getCount($conn, "SELECT COUNT(*) as total FROM usulan");
$proses = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE status = 'proses'");
$selesai = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE status = 'selesai'");
$pihak_ke_3 = getCount($conn, "SELECT COUNT(*) as total FROM usulan WHERE status = 'pihak ke-3'");

// Data level resiko dengan default value 0
$tinggi = getCount($conn, "SELECT COUNT(*) as total FROM jwb_usulan WHERE level_resiko = 'Tinggi'");
$sedang = getCount($conn, "SELECT COUNT(*) as total FROM jwb_usulan WHERE level_resiko = 'Sedang'");
$rendah = getCount($conn, "SELECT COUNT(*) as total FROM jwb_usulan WHERE level_resiko = 'Rendah'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPAK | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            <h2 class="menu-title">Menu Admin</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="#" class="active" id="homeLink">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="dashboard1.php" id="dashboardLink">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="daftar_seluruh_konsultasi.php" id="usulanLink">
                <i class="fas fa-list"></i> Daftar Usulan
            </a>
            <a href="daftar_konsultasi_belum_dijawab.php" id="belumDijawabLink">
                <i class="fas fa-clock"></i> Belum Dijawab
            </a>
            <a href="daftar_konsultasi_dijawab.php" id="dijawabLink">
                <i class="fas fa-check-circle"></i> Sudah Dijawab
            </a>
            <a href="profile_pegawai.php" id="pegawaiLink">
                <i class="fas fa-users"></i> Profil Pegawai
            </a>
            <a href="profile_petugas.php" id="petugasLink">
                <i class="fas fa-user-shield"></i> Profil Petugas
            </a>
            <a href="register_admin.php" id="registrasiadminLink">
                <i class="fas fa-user-plus"></i> Registrasi Admin
            </a>
            <a href="register_petugas.php" id="registrasipetugasLink">
                <i class="fas fa-user-edit"></i> Registrasi Petugas
            </a>
            <a href="../web/logout.php" id="logoutLink" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <header class="header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <span>Selamat Datang Admin, <?php echo htmlspecialchars($username); ?></span>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="welcome-section">
                <h1>Home Admin</h1>
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
                        <h3>Total Usulan Dalam Proses</h3>
                        <div class="stat-value"><?php echo $proses; ?></div>
                    </div>
                </div>

                <div class="stat-card selesai">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Total Usulan Dijawab</h3>
                        <div class="stat-value"><?php echo $selesai; ?></div>
                    </div>
                </div>

                <div class="stat-card pihak-ke-3">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Usulan Dikelola Pihak Ke-3</h3>
                        <div class="stat-value"><?php echo $pihak_ke_3; ?></div>
                    </div>
                </div>
            </div>

            <!-- Risk Level Cards -->
            <div class="stats-grid">
                <div class="stat-card tinggi">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Resiko Tinggi</h3>
                        <div class="stat-value"><?php echo $tinggi; ?></div>
                    </div>
                </div>

                <div class="stat-card sedang">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Resiko Sedang</h3>
                        <div class="stat-value"><?php echo $sedang; ?></div>
                    </div>
                </div>

                <div class="stat-card rendah">
                    <div class="stat-header"></div>
                    <div class="stat-content">
                        <h3>Resiko Rendah</h3>
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
        
        // Initialize sidebar state
        function initSidebar() {
            if (window.innerWidth <= 992) {
                // Mobile view - hide sidebar by default
                sidebar.classList.remove('visible');
                toggleBtn.style.display = 'flex';
                
                // Check for saved state
                if (localStorage.getItem('sidebarState') === 'visible') {
                    sidebar.classList.add('visible');
                }
            } else {
                // Desktop view - show sidebar always
                sidebar.classList.add('visible');
                toggleBtn.style.display = 'none';
            }
        }
        
        // Toggle sidebar function
        function toggleSidebar() {
            sidebar.classList.toggle('visible');
            localStorage.setItem('sidebarState', sidebar.classList.contains('visible') ? 'visible' : 'hidden');
        }
        
        // Handle window resize
        function handleResize() {
            if (window.innerWidth <= 992) {
                toggleBtn.style.display = 'flex';
            } else {
                sidebar.classList.add('visible');
                toggleBtn.style.display = 'none';
            }
        }
        
        // Initialize
        initSidebar();
        
        // Event listeners
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