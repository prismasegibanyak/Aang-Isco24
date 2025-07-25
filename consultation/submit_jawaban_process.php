<?php
session_start();
include '../db.php';

// Pastikan pengguna sudah login sebagai petugas atau admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')) {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

// Get petugas info
$sql_petugas = "SELECT nama_petugas FROM petugas WHERE username_petugas = ?";
$stmt_petugas = $conn->prepare($sql_petugas);
$stmt_petugas->bind_param("s", $username);
$stmt_petugas->execute();
$result_petugas = $stmt_petugas->get_result();
$petugas = $result_petugas->fetch_assoc();
$nama_petugas = htmlspecialchars($petugas['nama_petugas']);

// Mendapatkan daftar usulan dengan nama pegawai
$sql = "SELECT u.*, j.isi_tanggapan, j.tgl_tanggapan, j.nip_petugas, p.nama_pegawai, j.level_resiko, u.warning_confirmation
        FROM usulan u
        LEFT JOIN jwb_usulan j ON u.Id_usulan = j.id_usulan
        LEFT JOIN pegawai p ON u.nip_pegawai = p.nip_pegawai
        ORDER BY u.Tgl_usulan DESC";
$result = $conn->query($sql);

function formatWaktu($timestamp) {
    date_default_timezone_set('Asia/Jakarta');
    return date('l, d/m/Y H:i:s', strtotime($timestamp));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Usulan Pemeliharaan | SiPAK</title>
    <link rel="stylesheet" href="styles_unified.css">
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
            <img src="../logoo.png" alt="SiPAK Logo">
            <h2 class="menu-title">Menu Petugas</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../home_petugas.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="view_konsultasi_petugas.php" class="active">
                <i class="fas fa-list"></i> Daftar Usulan
            </a>
            <a href="profile2.php">
                <i class="fas fa-user"></i> Profil
            </a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <header class="header sticky-header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <h3>Selamat Datang, <?php echo htmlspecialchars($nama_petugas); ?></h3>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="welcome-section">
                <h1>Daftar Usulan Pemeliharaan</h1>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal Usulan</th>
                            <th>Nama Pegawai</th>
                            <th>Nama Aset</th>
                            <th>Lokasi Aset</th>
                            <th>File Pendukung</th>
                            <th>Isi Usulan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                            <th>Level Resiko</th>
                            <th>Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo formatWaktu($row['Tgl_usulan']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_pegawai']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_aset'] ?? 'Tidak tersedia'); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi_aset'] ?? 'Tidak tersedia'); ?></td>
                            <td>
                                <?php if ($row['dokumen_pendukung']): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($row['dokumen_pendukung']); ?>" target="_blank" class="button">
                                        <i class="fas fa-file"></i> Lihat
                                    </a>
                                <?php else: ?>
                                    Tidak ada file
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['isi_usulan']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'proses'): ?>
                                    <a href="jawab_konsultasi.php?id=<?php echo $row['Id_usulan']; ?>" class="button response-button">
                                        <i class="fas fa-edit"></i> Jawab
                                    </a>
                                <?php else: ?>
                                    <a href="view_jawaban.php?id=<?php echo $row['Id_usulan']; ?>" class="button response-button" style="background:var(--success-color);">
                                        <i class="fas fa-comment-dots"></i> Tanggapan
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['level_resiko'] ?? 'Dalam Proses'); ?></td>
                            <td><?php echo htmlspecialchars($row['warning_confirmation']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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