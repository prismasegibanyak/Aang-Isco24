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

$sql = "SELECT nip_pegawai, nama_pegawai FROM pegawai WHERE username_pegawai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();

if (!$pegawai) {
    die("Pegawai tidak ditemukan.");
}

$nip_pegawai = $pegawai['nip_pegawai'];
$nama_pegawai = htmlspecialchars($pegawai['nama_pegawai']);

$sql = "SELECT u.*, j.isi_tanggapan, j.dokumen_tanggapan, j.tgl_tanggapan, j.level_resiko 
        FROM usulan u 
        LEFT JOIN jwb_usulan j ON u.Id_usulan = j.id_usulan 
        WHERE u.nip_pegawai = ? 
        ORDER BY u.Tgl_usulan DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nip_pegawai);
$stmt->execute();
$result = $stmt->get_result();

// Format timestamp
function formatWaktu($timestamp) {
    date_default_timezone_set('Asia/Jakarta');
    return date('d/m/Y H:i', strtotime($timestamp));
}

// Fungsi untuk menentukan jenis file
function getFileType($filename) {
    if (!$filename) return false;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $imageExts = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($ext, $imageExts)) return 'image';
    if ($ext === 'pdf') return 'pdf';
    return 'other';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Usulan Pemeliharaan | SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_unified.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .file-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .file-preview img {
            max-width: 100px;
            max-height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .file-preview iframe {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #3498db;
            text-decoration: none;
        }
        .file-link:hover {
            text-decoration: underline;
        }
        .text-muted {
            color: #999;
            font-style: italic;
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
            <h2 class="menu-title">Menu Pegawai</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../web/home.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="view_consultations.php" class="active">
                <i class="fas fa-list-check"></i> Daftar Usulan
            </a>
            <a href="consultation.php">
                <i class="fas fa-plus-circle"></i> Usulan Baru
            </a>
            <a href="profile1.php">
                <i class="fas fa-user-cog"></i> Profil
            </a>
            <a href="../web/logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <header class="header sticky-header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <h3>Selamat Datang, <?php echo htmlspecialchars($nama_pegawai); ?></h3>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="welcome-section">
                <h1>Daftar Usulan Pemeliharaan</h1>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Aset</th>
                            <th>Lokasi</th>
                            <th>File Pendukung</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Tanggapan</th>
                            <th>Keterangan</th>
                            <th>File Tanggapan</th>
                            <th>Level Resiko</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo formatWaktu($row['Tgl_usulan']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_aset'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['lokasi_aset'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($row['dokumen_pendukung'])): 
                                    $fileType = getFileType($row['dokumen_pendukung']); ?>
                                    <div class="file-preview">
                                        <?php if ($fileType === 'image'): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($row['dokumen_pendukung']); ?>" alt="Preview Dokumen">
                                            <a href="../uploads/<?php echo htmlspecialchars($row['dokumen_pendukung']); ?>" target="_blank" class="file-link">
                                                <i class="fas fa-expand"></i> Lihat Full
                                            </a>
                                        <?php elseif ($fileType === 'pdf'): ?>
                                            <iframe src="../uploads/<?php echo htmlspecialchars($row['dokumen_pendukung']); ?>#toolbar=0&navpanes=0" scrolling="no"></iframe>
                                            <a href="../uploads/<?php echo htmlspecialchars($row['dokumen_pendukung']); ?>" target="_blank" class="file-link">
                                                <i class="fas fa-file-pdf"></i> Buka PDF
                                            </a>
                                        <?php else: ?>
                                            <i class="fas fa-file-alt fa-2x"></i>
                                            <a href="../uploads/<?php echo htmlspecialchars($row['dokumen_pendukung']); ?>" target="_blank" class="file-link">
                                                <i class="fas fa-download"></i> Unduh File
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada file</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['isi_usulan']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['tgl_tanggapan'] ? formatWaktu($row['tgl_tanggapan']) : '-'; ?></td>
                            <td><?php echo $row['isi_tanggapan'] ? htmlspecialchars($row['isi_tanggapan']) : '-'; ?></td>
                            <td>
                                <?php if (!empty($row['dokumen_tanggapan'])): 
                                    $fileTypeTanggapan = getFileType($row['dokumen_tanggapan']); ?>
                                    <div class="file-preview">
                                        <?php if ($fileTypeTanggapan === 'image'): ?>
                                            <img src="../uploads/file_jawaban/<?php echo htmlspecialchars($row['dokumen_tanggapan']); ?>" alt="Preview Tanggapan">
                                            <a href="../uploads/file_jawaban/<?php echo htmlspecialchars($row['dokumen_tanggapan']); ?>" target="_blank" class="file-link">
                                                <i class="fas fa-expand"></i> Lihat Full
                                            </a>
                                        <?php elseif ($fileTypeTanggapan === 'pdf'): ?>
                                            <iframe src="../uploads/file_jawaban/<?php echo htmlspecialchars($row['dokumen_tanggapan']); ?>#toolbar=0&navpanes=0" scrolling="no"></iframe>
                                            <a href="../uploads/file_jawaban/<?php echo htmlspecialchars($row['dokumen_tanggapan']); ?>" target="_blank" class="file-link">
                                                <i class="fas fa-file-pdf"></i> Buka PDF
                                            </a>
                                        <?php else: ?>
                                            <i class="fas fa-file-alt fa-2x"></i>
                                            <a href="../uploads/file_jawaban/<?php echo htmlspecialchars($row['dokumen_tanggapan']); ?>" target="_blank" class="file-link">
                                                <i class="fas fa-download"></i> Unduh File
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada file</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['level_resiko']): ?>
                                    <span class="status-badge">
                                        <?php echo htmlspecialchars($row['level_resiko']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
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