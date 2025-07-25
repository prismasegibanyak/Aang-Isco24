<?php
session_start();
include '../config/db.php';

// Authentication check
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

function getCount($conn, $sql) {
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] ?? 0 : 0;
}

$total_seluruh = getCount($conn, "SELECT COUNT(*) AS total FROM usulan");
$total_dijawab = getCount($conn, "SELECT COUNT(*) AS total FROM usulan WHERE status = 'selesai'");
$total_belum_dijawab = getCount($conn, "SELECT COUNT(*) AS total FROM usulan WHERE status = 'proses'");
$total_pegawai = getCount($conn, "SELECT COUNT(*) AS total FROM pegawai");
$total_petugas = getCount($conn, "SELECT COUNT(*) AS total FROM petugas WHERE role = 'petugas'");
$total_admin = getCount($conn, "SELECT COUNT(*) AS total FROM petugas WHERE role = 'admin'");
$total_tinggi = getCount($conn, "SELECT COUNT(*) AS total FROM jwb_usulan WHERE level_resiko = 'Tinggi'");
$total_sedang = getCount($conn, "SELECT COUNT(*) AS total FROM jwb_usulan WHERE level_resiko = 'Sedang'");
$total_rendah = getCount($conn, "SELECT COUNT(*) AS total FROM jwb_usulan WHERE level_resiko = 'Rendah'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPAK | Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-content {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .charts-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px; /* 50% lebih besar dari v10 (33px) */
            min-height: 0;
        }
        .charts-row {
            display: flex;
            gap: 40px; /* 50% lebih besar dari v10 (33px) */
            margin-bottom: 0;
            min-height: 0;
        }
        .chart-wrapper {
            flex: 1 1 0;
            min-width: 0;
            background: white;
            border-radius: 12px;
            padding: 32.4px 22px 22px 22px; /* 50% lebih besar dari v10 (27px 18px 18px 18px) */
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 270px;    /* 50% lebih besar dari v10 (225px) */
            max-height: 396px;    /* 50% lebih besar dari v10 (330px) */
            justify-content: flex-start;
        }
        .chart-wrapper h2 {
            font-size: 1.2rem; /* 50% lebih besar dari v10 (1.62rem) */
            color: var(--secondary-color);
            margin-bottom: 18px;
            text-align: center;
        }
        .chart-container {
            position: relative;
            width: 90%;
            height: 190px;     /* 50% lebih besar dari v10 (157.5px) */
            max-width: 432px;  /* 50% lebih besar dari v10 (360px) */
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width:991px) {
            .charts-row {
                flex-direction: column;
                gap: 18px; /* 50% lebih besar dari v10 (15px) */
            }
            .chart-wrapper {
                min-height: 162px;    /* 50% lebih besar dari v10 (135px) */
                max-height: 270px;    /* 50% lebih besar dari v10 (225px) */
                padding: 10.8px 3.6px 10.8px 3.6px; /* 50% lebih besar dari v10 (9px 3px 9px 3px) */
            }
            .chart-container {
                height: 108px; /* 50% lebih besar dari v10 (90px) */
            }
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
            <h2 class="menu-title">Menu Admin</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="home_admin.php" id="homeLink">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="dashboard1.php" class="active" id="dashboardLink">
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

        <div class="list-content">
            <div class="welcome-section">
                <h1>Dashboard Admin</h1>
            </div>
            <div class="charts-container">
                <div class="charts-row">
                    <div class="chart-wrapper">
                        <h2><i class="fas fa-file-alt"></i> Status Usulan</h2>
                        <div class="chart-container">
                            <canvas id="usulanChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <h2><i class="fas fa-users"></i> Distribusi Pengguna</h2>
                        <div class="chart-container">
                            <canvas id="penggunaChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="charts-row">
                    <div class="chart-wrapper">
                        <h2><i class="fas fa-exclamation-triangle"></i> Level Resiko</h2>
                        <div class="chart-container">
                            <canvas id="resikoChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <h2><i class="fas fa-chart-pie"></i> Ringkasan Data</h2>
                        <div class="chart-container">
                            <canvas id="summaryChart"></canvas>
                        </div>
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
        // Highlight active link
        const currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            if (link.getAttribute('href').endsWith(currentPage)) {
                link.classList.add('active');
            }
        });
    });

    // Chart Data
    const chartData = {
        usulan: {
            labels: ['Total Usulan', 'Sudah Dijawab', 'Belum Dijawab'],
            data: [<?php echo $total_seluruh; ?>, <?php echo $total_dijawab; ?>, <?php echo $total_belum_dijawab; ?>],
            colors: ['#2A3132', '#336B87', '#90AFC5']
        },
        pengguna: {
            labels: ['Pegawai', 'Petugas', 'Admin'],
            data: [<?php echo $total_pegawai; ?>, <?php echo $total_petugas; ?>, <?php echo $total_admin; ?>],
            colors: ['#2C7873', '#6FB98F', '#004445']
        },
        resiko: {
            labels: ['Tinggi', 'Sedang', 'Rendah'],
            data: [<?php echo $total_tinggi; ?>, <?php echo $total_sedang; ?>, <?php echo $total_rendah; ?>],
            colors: ['#46211A', '#693D3D', '#BA5536']
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('usulanChart'), {
            type: 'bar',
            data: {
                labels: chartData.usulan.labels,
                datasets: [{
                    data: chartData.usulan.data,
                    backgroundColor: chartData.usulan.colors,
                    borderColor: chartData.usulan.colors.map(color => color + 'DD'),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        new Chart(document.getElementById('penggunaChart'), {
            type: 'doughnut',
            data: {
                labels: chartData.pengguna.labels,
                datasets: [{
                    data: chartData.pengguna.data,
                    backgroundColor: chartData.pengguna.colors,
                    borderWidth: 0
                }]
            },
            options: getChartOptions()
        });

        new Chart(document.getElementById('resikoChart'), {
            type: 'doughnut',
            data: {
                labels: chartData.resiko.labels,
                datasets: [{
                    data: chartData.resiko.data,
                    backgroundColor: chartData.resiko.colors,
                    borderWidth: 0
                }]
            },
            options: getChartOptions()
        });

        new Chart(document.getElementById('summaryChart'), {
            type: 'bar',
            data: {
                labels: ['Total Data'],
                datasets: [
                    {
                        label: 'Usulan',
                        data: [<?php echo $total_seluruh; ?>],
                        backgroundColor: '#2A3132',
                        borderColor: '#2A3132DD',
                        borderWidth: 1
                    },
                    {
                        label: 'Pengguna',
                        data: [<?php echo $total_pegawai + $total_petugas + $total_admin; ?>],
                        backgroundColor: '#336B87',
                        borderColor: '#336B87DD',
                        borderWidth: 1
                    },
                    {
                        label: 'Resiko',
                        data: [<?php echo $total_tinggi + $total_sedang + $total_rendah; ?>],
                        backgroundColor: '#90AFC5',
                        borderColor: '#90AFC5DD',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                ...getChartOptions(),
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    });

    function getChartOptions(title = '') {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: !!title,
                    text: title,
                    font: { size: 15 }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        };
    }
    </script>
</body>
</html>