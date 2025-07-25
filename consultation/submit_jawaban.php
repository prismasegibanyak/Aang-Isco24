<?php
session_start();
include '../db.php';

// Pastikan pengguna sudah login sebagai petugas
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas')) {
    header("Location: ../login/index.php");
    exit();
}

// Get petugas info
$username = $_SESSION['username'];
$sql_petugas = "SELECT nama_petugas FROM petugas WHERE username_petugas = ?";
$stmt_petugas = $conn->prepare($sql_petugas);
$stmt_petugas->bind_param("s", $username);
$stmt_petugas->execute();
$result_petugas = $stmt_petugas->get_result();
$petugas = $result_petugas->fetch_assoc();
$nama_petugas = htmlspecialchars($petugas['nama_petugas']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Jawaban | SiPAK</title>
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
            <a href="view_konsultasi_petugas.php">
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
            <div class="consultation-form">
                <h2>Form Jawaban Konsultasi</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                
                <form action="submit_jawaban_process.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_konsultasi" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                    
                    <div class="input-group">
                        <label for="isi_jawaban">Isi Jawaban*</label>
                        <textarea id="isi_jawaban" name="isi_jawaban" rows="4" required></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="file_jawaban">File Jawaban (Opsional)</label>
                        <div class="file-input-container">
                            <label for="file_jawaban" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Pilih File (PDF, JPG, PNG, GIF)</span>
                            </label>
                            <input type="file" id="file_jawaban" name="file_jawaban" class="file-input" accept=".pdf,.jpg,.jpeg,.png,.gif">
                            <div id="file-name" class="file-name">Belum memilih file</div>
                        </div>
                    </div>
                    
                    <div class="button-container">
                        <button type="submit" class="button"><i class="fas fa-paper-plane"></i> Kirim Jawaban</button>
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