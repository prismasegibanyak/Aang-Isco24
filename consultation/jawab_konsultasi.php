<?php
session_start();
include '../config/db.php';

// Pastikan pengguna sudah login sebagai petugas
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')) {
    header("Location: ../login/index.php");
    exit();
}

$usulan_id = $_GET['id'];
$username = $_SESSION['username'];

// Mendapatkan data usulan dan jawaban dari database
$sql = "SELECT u.*, p.nama_pegawai 
        FROM usulan u
        LEFT JOIN pegawai p ON u.nip_pegawai = p.nip_pegawai 
        WHERE u.Id_usulan = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usulan_id);
$stmt->execute();
$result = $stmt->get_result();
$usulan = $result->fetch_assoc();

if (!$usulan) {
    die("Usulan tidak ditemukan.");
}

// Get petugas info
$sql_petugas = "SELECT nama_petugas FROM petugas WHERE username_petugas = ?";
$stmt_petugas = $conn->prepare($sql_petugas);
$stmt_petugas->bind_param("s", $username);
$stmt_petugas->execute();
$result_petugas = $stmt_petugas->get_result();
$petugas = $result_petugas->fetch_assoc();
$nama_petugas = htmlspecialchars($petugas['nama_petugas']);

function getFileType($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $imageExts = ['jpg', 'jpeg', 'png', 'gif'];
    $pdfExts = ['pdf'];
    
    if (in_array($ext, $imageExts)) return 'image';
    if (in_array($ext, $pdfExts)) return 'pdf';
    return 'other';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanggapi Usulan | SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_unified.css">
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
            <h2 class="menu-title">Menu Petugas</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../web/home_petugas.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="view_konsultasi_petugas.php">
                <i class="fas fa-list"></i> Daftar Usulan
            </a>
            <a href="profile2.php">
                <i class="fas fa-user"></i> Profil
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
                <h3>Selamat Datang, <?php echo htmlspecialchars($nama_petugas); ?></h3>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="consultation-form">
                <h2>Form Tanggapan Usulan</h2>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                
                <div class="input-group">
                    <label>Nama Pegawai</label>
                    <input type="text" value="<?php echo htmlspecialchars($usulan['nama_pegawai']); ?>" readonly>
                </div>
                
                <div class="input-group">
                    <label>Isi Usulan</label>
                    <textarea readonly><?php echo htmlspecialchars($usulan['isi_usulan']); ?></textarea>
                </div>
                
                <div class="input-group">
                    <label>File Pendukung</label>
                    <?php if ($usulan['dokumen_pendukung']): 
                        $fileType = getFileType($usulan['dokumen_pendukung']);
                    ?>
                        <div class="file-preview">
                            <?php if ($fileType === 'image'): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($usulan['dokumen_pendukung']); ?>" style="max-width: 100%; max-height: 300px;">
                            <?php elseif ($fileType === 'pdf'): ?>
                                <iframe src="../uploads/<?php echo htmlspecialchars($usulan['dokumen_pendukung']); ?>" style="width:100%; height:500px;"></iframe>
                            <?php else: ?>
                                <a href="../uploads/<?php echo htmlspecialchars($usulan['dokumen_pendukung']); ?>" target="_blank" class="button">
                                    <i class="fas fa-file"></i> Lihat File
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Tidak ada file pendukung</p>
                    <?php endif; ?>
                </div>
                
                <form action="jawab_konsultasi_process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_usulan" value="<?php echo htmlspecialchars($usulan_id); ?>">
                    
                    <div class="input-group">
                        <label for="isi_tanggapan">Isi Tanggapan*</label>
                        <textarea id="isi_tanggapan" name="isi_tanggapan" rows="4" required></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="file_jawaban">File Tanggapan</label>
                        <div class="file-input-container">
                            <label for="file_jawaban" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Pilih File (PDF, JPG, PNG, GIF)</span>
                            </label>
                            <input type="file" id="file_jawaban" name="file_jawaban" class="file-input" accept=".pdf,.jpg,.jpeg,.png,.gif">
                            <div id="file-name" class="file-name">Belum memilih file</div>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="status">Status*</label>
                        <select id="status" name="status" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="proses">Proses</option>
                            <option value="selesai">Selesai</option>
                            <option value="pihak ke-3">Pihak ke-3</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="level_resiko">Level Resiko*</label>
                        <select id="level_resiko" name="level_resiko" required>
                            <option value="">-- Pilih Level Resiko --</option>
                            <option value="Tinggi">Tinggi</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Rendah">Rendah</option>
                        </select>
                    </div>
                    
                    <div class="button-container">
                        <button type="submit" class="button"><i class="fas fa-paper-plane"></i> Kirim Tanggapan</button>
                        <a href="view_konsultasi_petugas.php" class="button danger">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
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
        
        // File name display
        document.getElementById('file_jawaban').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Belum memilih file';
            document.getElementById('file-name').textContent = fileName;
        });
    });
    </script>
</body>
</html>