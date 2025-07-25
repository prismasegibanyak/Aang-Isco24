<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'pegawai') {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

$sql = "SELECT nip_pegawai, nama_pegawai, email_pegawai FROM pegawai WHERE username_pegawai = ? OR nip_pegawai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Usulan Pemeliharaan | SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_unified.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <button id="toggleSidebar"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../assets/img/logoo.png" alt="Logo SiPAK">
            <h2 class="menu-title">Menu Pegawai</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="../web/home.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="view_consultations.php"><i class="fas fa-list-check"></i> Daftar Usulan</a>
            <a href="consultation.php" class="active"><i class="fas fa-plus-circle"></i> Usulan Baru</a>
            <a href="profile1.php"><i class="fas fa-user-cog"></i> Profil</a>
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
            <div class="consultation-form">
                <h2>Form Usulan Pemeliharaan Baru</h2>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <form action="consultation_process.php" method="POST" enctype="multipart/form-data" id="consultationForm">
                    <div class="input-group">
                        <label for="nama">Nama Pegawai</label>
                        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($pegawai['nama_pegawai']); ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($pegawai['email_pegawai']); ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label for="nama_aset">Nama Aset*</label>
                        <input type="text" id="nama_aset" name="nama_aset" placeholder="Masukkan nama aset" required>
                    </div>
                    <div class="input-group">
                        <label for="lokasi_aset">Lokasi Aset*</label>
                        <input type="text" id="lokasi_aset" name="lokasi_aset" placeholder="Lokasi aset (cth: Ruang Server Lt.2)" required>
                    </div>
                    <div class="input-group">
                        <label for="file">File Pendukung*</label>
                        <div class="file-input-container">
                            <label for="file" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Pilih File (JPG, PNG, GIF, PDF)</span>
                            </label>
                            <input type="file" id="file" name="file" class="file-input" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
                            <div id="file-name" class="file-name">Belum memilih file</div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="isi_usulan">Deskripsi Masalah/Pemeliharaan*</label>
                        <textarea id="isi_usulan" name="isi_usulan" placeholder="Jelaskan secara detail masalah atau kebutuhan pemeliharaan" required></textarea>
                    </div>
                    <input type="hidden" id="warning_confirmation" name="warning_confirmation" value="">
                    <input type="hidden" name="nip" value="<?php echo htmlspecialchars($pegawai['nip_pegawai']); ?>">
                    <div class="button-container">
                        <button type="submit" class="button"><i class="fas fa-paper-plane"></i> Kirim Usulan</button>
                        <a href="view_consultations.php" class="button danger"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Aang_Isco24. All rights reserved.</p>
        </footer>
    </div>
    <script>
        document.getElementById('file').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Belum memilih file';
            document.getElementById('file-name').textContent = fileName;
        });
        document.getElementById('consultationForm').addEventListener('submit', function(e) {
            const confirmation = confirm("Anda yakin dengan data usulan pemeliharaan ini sudah benar?");
            if (!confirmation) {
                e.preventDefault();
            } else {
                document.getElementById("warning_confirmation").value = "Ya";
                const btn = this.querySelector('button[type="submit"]');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
                btn.disabled = true;
            }
        });
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
        });
    </script>
</body>
</html>