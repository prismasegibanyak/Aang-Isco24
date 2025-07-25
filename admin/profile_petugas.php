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

// Mendapatkan data petugas dari database
$sql = "SELECT * FROM petugas";
$result = $conn->query($sql);
if ($result === false) {
    die("Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Petugas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #2A3132;
            --secondary-color: #336B87;
            --accent-color: #90AFC5;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --sidebar-width: 280px;
            --border-color: #dee2e6;
            --text-color: #212529;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(rgba(255,255,255,0.8),rgba(255,255,255,0.8)), url(../assets/img/2.jpg);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: var(--dark-color);
            overflow-x: hidden;
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
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            padding: 20px 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }
        .sidebar.visible { transform: translateX(0); }
        .sidebar .logo {
            padding: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            text-align: center;
        }
        .sidebar .logo img {
            max-width: 80%;
            height: auto;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .sidebar .logo img:hover { transform: scale(1.05); }
        .menu-title { font-size: 1.4rem; font-weight: 600; margin-top: 10px; color: white; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 10px 0; }
        .sidebar a {
            display: flex; align-items: center;
            padding: 14px 25px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            border-radius: 6px;
            margin: 6px 15px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        .sidebar a i {
            margin-right: 12px; font-size: 1.1rem; width: 24px; text-align: center;
        }
        #toggleSidebar, .toggle-sidebar-btn {
            position: fixed;
            top: 20px; left: 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 1100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        #toggleSidebar:hover, .toggle-sidebar-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--light-color);
        }
        .main-content.expanded, .main-content.active { margin-left: 0; }
        .header {
            padding: 20px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0; z-index: 900;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-title { font-size: 1.5rem; font-weight: 600; color: var(--primary-color); }
        .user-info { font-size: 0.9rem; color: var(--secondary-color); }
        .profile-list {
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
        .profile-list h1, .profile-list h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 18px;
            font-weight: 600;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            table-layout: auto;
            background: white;
            margin-bottom: 16px;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }
        th {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }
        tr:nth-child(even) { background-color: rgba(0,0,0,0.02); }
        tr:hover { background-color: rgba(0,0,0,0.05); }
        .button-edit, .button-hapus {
            padding: 7px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.98rem;
            margin-right: 6px;
            margin-bottom: 3px;
        }
        .button-edit { background: var(--accent-color); color: var(--primary-color); border: none; }
        .button-edit:hover { background: var(--secondary-color); color: #fff; }
        .button-hapus { background: var(--danger-color); color: #fff; border: none; }
        .button-hapus:hover { background: #a31515; color: #fff; }
        .footer {
            position: fixed;
            left: 0; bottom: 0;
            width: 100%;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            text-align: center;
            padding: 20px 0;
            z-index: 100;
        }
        .footer p { margin: 0; color: var(--secondary-color); font-size: 1.2rem; }
        @media (max-width: 1200px) {
            .profile-list { padding: 20px 5px 16px 5px; }
            table { min-width: 520px; }
        }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.visible { transform: translateX(0); }
            .main-content { margin-left: 0; }
            #toggleSidebar, .toggle-sidebar-btn { display: flex !important; }
        }
        @media (min-width: 993px) {
            .sidebar { transform: translateX(0); }
            .main-content { margin-left: var(--sidebar-width); }
            #toggleSidebar, .toggle-sidebar-btn { display: none !important; }
        }
        @media (max-width: 900px) {
            .profile-list { padding: 12px 2px 12px 2px; margin-top: 12px; }
            table { min-width: 320px; }
        }
        @media (max-width: 768px) {
            .profile-list { padding: 8px 2px 8px 2px; }
            .footer p { font-size: 1rem; }
            table, thead, tbody, th, td, tr { display: block; width: 100%; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { margin-bottom: 10px; border: 1px solid var(--border-color); border-radius: 8px; }
            td {
                border: none; border-bottom: 1px solid var(--border-color);
                position: relative;
                padding-left: 50%;
                text-align: right;
                min-height: 36px;
            }
            td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                padding-right: 10px;
                font-weight: bold;
                text-align: left;
                color: var(--primary-color);
            }
            th, td { padding: 8px 10px; }
        }
    </style>
</head>
<body>
    <button id="toggleSidebar"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="../assets/img/logoo.png" alt="SiPAK Logo">
            <h2 class="menu-title">Menu Admin</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="home_admin.php"><i class="fas fa-home"></i> Home</a>
            <a href="dashboard1.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="daftar_seluruh_konsultasi.php"><i class="fas fa-list"></i> Daftar Usulan</a>
            <a href="daftar_konsultasi_belum_dijawab.php"><i class="fas fa-clock"></i> Belum Dijawab</a>
            <a href="daftar_konsultasi_dijawab.php"><i class="fas fa-check-circle"></i> Sudah Dijawab</a>
            <a href="profile_pegawai.php"><i class="fas fa-users"></i> Profil Pegawai</a>
            <a href="profile_petugas.php" class="active"><i class="fas fa-user-shield"></i> Profil Petugas</a>
            <a href="register_admin.php"><i class="fas fa-user-plus"></i> Registrasi Admin</a>
            <a href="register_petugas.php"><i class="fas fa-user-edit"></i> Registrasi Petugas</a>
            <a href="../web/logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>
    <div class="main-content" id="mainContent">
        <header class="header">
            <h1 class="main-title">Sistem Informasi Pemeliharaan Aset Kantor (SiPAK)</h1>
            <div class="user-info">
                <span>Selamat Datang Admin, <?php echo htmlspecialchars($username); ?></span>
            </div>
        </header>
        <div class="profile-list">
            <div class="welcome-section">
                <h1>Profile Petugas</h1>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="NIP"><?php echo htmlspecialchars($row['nip_petugas']); ?></td>
                            <td data-label="Nama"><?php echo htmlspecialchars($row['nama_petugas']); ?></td>
                            <td data-label="Username"><?php echo htmlspecialchars($row['username_petugas']); ?></td>
                            <td data-label="Email"><?php echo htmlspecialchars($row['email_petugas']); ?></td>
                            <td data-label="No HP"><?php echo htmlspecialchars($row['no_hp_petugas']); ?></td>
                            <td data-label="Role"><?php echo htmlspecialchars($row['role']); ?></td>
                            <td data-label="Aksi">
                                <a href="edit_petugas.php?nip=<?php echo urlencode($row['nip_petugas']); ?>" class="button-edit"><i class="fas fa-edit"></i> Edit</a>
                                <a href="hapus_petugas.php?nip=<?php echo urlencode($row['nip_petugas']); ?>" class="button-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i> Hapus</a>
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