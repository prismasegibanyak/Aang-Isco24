<?php
session_start();
include '../config/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Authentication check
if (!isset($_SESSION['username'])) {
    header("Location: ../login/index.php");
    exit();
}

// Check if user has admin or petugas role
$allowed_roles = ['admin', 'petugas'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../unauthorized.php");
    exit();
}

// Get all consultation proposals with responses
$sql = "SELECT u.*, 
               j.isi_tanggapan, 
               j.dokumen_tanggapan AS file_jawaban, 
               j.tgl_tanggapan, 
               j.nip_petugas, 
               j.nama_petugas, 
               j.level_resiko, 
               p.nama_pegawai
        FROM usulan u
        LEFT JOIN pegawai p ON u.nip_pegawai = p.nip_pegawai 
        LEFT JOIN jwb_usulan j ON u.Id_usulan = j.id_usulan 
        ORDER BY u.Tgl_usulan DESC";
$result = $conn->query($sql);

if ($result === false) {
    die("Database error: " . $conn->error);
}

// Format date and time
function formatDateTime($timestamp) {
    date_default_timezone_set('Asia/Jakarta');
    return date('d/m/Y H:i', strtotime($timestamp));
}

// Get file icon based on extension
function getFileIcon($filename) {
    if (!$filename) return 'fas fa-times-circle';
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'pdf': return 'fas fa-file-pdf';
        case 'doc':
        case 'docx': return 'fas fa-file-word';
        case 'xls':
        case 'xlsx': return 'fas fa-file-excel';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif': return 'fas fa-file-image';
        default: return 'fas fa-file';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Seluruh Usulan - <?php echo htmlspecialchars($_SESSION['username']); ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .preview-img {
            max-width: 120px;
            max-height: 120px;
            display: block;
            margin: 0 auto;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(51,107,135,0.07);
        }
        @media (max-width: 768px) {
            .preview-img { max-width: 90vw; max-height: 90px; }
        }
        .list-content {
            flex: 1;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 32px 20px 24px 20px;
            margin-top: 30px;
            margin-bottom: 32px;
            min-height: 420px;
        }
    </style>
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
            <h2 class="menu-title">Menu <?php echo ucfirst($_SESSION['role']); ?></h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="home_admin.php" id="homeLink">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="dashboard1.php" id="dashboardLink">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="daftar_seluruh_konsultasi.php" class="active" id="usulanLink">
                <i class="fas fa-list"></i> Daftar Usulan
            </a>
            <a href="daftar_konsultasi_belum_dijawab.php" id="belumDijawabLink">
                <i class="fas fa-clock"></i> Belum Dijawab
            </a>
            <a href="daftar_konsultasi_dijawab.php" id="dijawabLink">
                <i class="fas fa-check-circle"></i> Sudah Dijawab
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
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
            <?php endif; ?>
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
                <span>Selamat Datang Admin, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </header>

        <div class="list-content">
            <div class="welcome-section">
                <h1>Daftar Seluruh Konsultasi</h1>
            </div>
            <div class="table-container">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pegawai</th>
                                <th>Aset</th>
                                <th>Lokasi</th>
                                <th>Usulan</th>
                                <th>File Pendukung</th>
                                <th>Status</th>
                                <th>Tanggapan</th>
                                <th>File Jawaban</th>
                                <th>Petugas</th>
                                <th>Resiko</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo formatDateTime($row['Tgl_usulan']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_pegawai'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_aset'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['lokasi_aset'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['isi_usulan'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($row['dokumen_pendukung']): ?>
                                        <?php 
                                            $file_extension = strtolower(pathinfo($row['dokumen_pendukung'], PATHINFO_EXTENSION));
                                            $file_url = '../uploads/' . htmlspecialchars($row['dokumen_pendukung']);
                                            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): 
                                        ?>
                                            <img class="preview-img" src="<?php echo $file_url; ?>" alt="File Pendukung">
                                        <?php else: ?>
                                            <a class="file-link" href="<?php echo $file_url; ?>" target="_blank">
                                                <i class="<?php echo getFileIcon($row['dokumen_pendukung']); ?>"></i>
                                                Lihat File
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="no-file"><i class="fas fa-times-circle"></i> Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td class="status-<?php echo strtolower($row['status'] ?? 'unknown'); ?>">
                                    <?php echo htmlspecialchars($row['status'] ?? '-'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['isi_tanggapan'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($row['file_jawaban']): ?>
                                        <?php
                                            $file_jawaban_ext = strtolower(pathinfo($row['file_jawaban'], PATHINFO_EXTENSION));
                                            $file_jawaban_url = '../uploads/file_jawaban/' . htmlspecialchars($row['file_jawaban']);
                                            if (in_array($file_jawaban_ext, ['jpg', 'jpeg', 'png', 'gif'])):
                                        ?>
                                            <img class="preview-img" src="<?php echo $file_jawaban_url; ?>" alt="File Jawaban">
                                        <?php else: ?>
                                            <a class="file-link" href="<?php echo $file_jawaban_url; ?>" target="_blank">
                                                <i class="<?php echo getFileIcon($row['file_jawaban']); ?>"></i>
                                                Lihat File
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="no-file"><i class="fas fa-times-circle"></i> Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['nama_petugas'] ?? '-'); ?></td>
                                <td class="risk-<?php echo strtolower($row['level_resiko'] ?? 'unknown'); ?>">
                                    <?php echo htmlspecialchars($row['level_resiko'] ?? '-'); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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