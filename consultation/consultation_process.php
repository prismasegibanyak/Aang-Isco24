<?php
session_start();
include '../config/db.php';

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security check
if (!isset($_SESSION['username'])) {
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

function showError($message) {
    global $username;
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - SiPAK</title>
        <link rel="stylesheet" href="../assets/css/styles_unified.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <style>
            .process-message { 
                max-width: 420px; 
                margin: 80px auto; 
                background: #fff; 
                border-radius: 12px; 
                box-shadow: 0 4px 18px rgba(51,107,135,0.13); 
                padding: 36px 28px 28px 28px; 
                text-align: center;
            }
            .process-message h2 { 
                color: var(--danger-color); 
                margin-bottom: 10px; 
            }
            .process-message p { 
                font-size: 1.1rem; 
                margin-bottom: 18px; 
            }
            .process-message .icon { 
                font-size: 2.5rem; 
                margin-bottom: 10px; 
            }
            .button { 
                background: var(--secondary-color); 
                color: #fff; 
                border: none; 
                border-radius: 6px; 
                padding: 10px 22px; 
                font-size: 1.09rem; 
                font-weight: 600; 
                text-decoration: none; 
                cursor: pointer; 
                display: inline-block;
            }
            .button:hover { 
                background: var(--primary-color);
            }
        </style>
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
                    <h3>Selamat Datang, '.htmlspecialchars($username).'</h3>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="process-message process-error">
                    <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                    <h2>Terjadi Kesalahan</h2>
                    <p>'.$message.'</p>
                    <a href="consultation.php" class="button">Kembali ke Form Usulan</a>
                </div>
            </div>
            <footer class="footer">
                <p>&copy; '.date('Y').' Aang_Isco24. All rights reserved.</p>
            </footer>
        </div>
        <script src="../js/sidebar.js"></script>
    </body>
    </html>';
    exit();
}

function showSuccess($message) {
    global $username;
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sukses - SiPAK</title>
        <link rel="stylesheet" href="../assets/css/styles_unified.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <style>
            .success-container { 
                max-width: 410px; 
                margin: 80px auto; 
                background: #fff; 
                border-radius: 12px; 
                box-shadow: 0 4px 18px rgba(51,107,135,0.13); 
                padding: 36px 28px 28px 28px; 
                text-align: center;
            }
            .success-icon { 
                font-size: 2.7rem; 
                color: var(--success-color); 
                margin-bottom: 15px;
            }
            .success-title { 
                color: var(--primary-color); 
                margin-bottom: 12px;
            }
            .success-message { 
                color: var(--secondary-color);
            }
            .button { 
                background: var(--secondary-color); 
                color: #fff; 
                border: none; 
                border-radius: 6px; 
                padding: 10px 22px; 
                font-size: 1.09rem; 
                font-weight: 600; 
                text-decoration: none; 
                cursor: pointer; 
                margin-top: 22px; 
                display: inline-block; 
            }
            .button:hover { 
                background: var(--primary-color);
            }
        </style>
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
                    <h3>Selamat Datang, '.htmlspecialchars($username).'</h3>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="success-container">
                    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                    <h1 class="success-title">Usulan Berhasil</h1>
                    <p class="success-message">'.$message.'</p>
                    <a href="view_consultations.php" class="button">Lihat Daftar Usulan</a>
                </div>
            </div>
            <footer class="footer">
                <p>&copy; '.date('Y').' Aang_Isco24. All rights reserved.</p>
            </footer>
        </div>
        <script src="../js/sidebar.js"></script>
    </body>
    </html>';
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    $required_fields = ['isi_usulan', 'nip', 'nama_aset', 'lokasi_aset'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            showError("Field ".ucfirst(str_replace('_', ' ', $field))." harus diisi.");
        }
    }

    // Sanitize inputs
    $isi_usulan = $conn->real_escape_string($_POST['isi_usulan']);
    $nip_pegawai = $conn->real_escape_string($_POST['nip']);
    $nama_aset = $conn->real_escape_string($_POST['nama_aset']);
    $lokasi_aset = $conn->real_escape_string($_POST['lokasi_aset']);
    $target_file = NULL;

    // Handle file upload
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                showError("Gagal membuat folder upload.");
            }
        }

        $file_name = basename($_FILES["file"]["name"]);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid("file_", true) . "." . $file_extension;
        $target_file = $target_dir . $new_file_name;

        $allowed_extensions = ["jpg", "jpeg", "png", "gif", "pdf"];
        if (!in_array($file_extension, $allowed_extensions)) {
            showError("Hanya file gambar (JPG, JPEG, PNG, GIF) dan PDF yang diperbolehkan.");
        }
        if ($_FILES["file"]["size"] > 5000000) {
            showError("Ukuran file terlalu besar. Maksimal 5MB.");
        }
        if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            showError("Terjadi kesalahan saat mengupload file.");
        }
    }

    // Insert proposal into database
    $stmt = $conn->prepare("INSERT INTO usulan (nip_pegawai, nama_aset, lokasi_aset, isi_usulan, dokumen_pendukung, status, Tgl_usulan, warning_confirmation) 
                            VALUES (?, ?, ?, ?, ?, 'proses', NOW(), ?)");
    $warning_confirmation = isset($_POST['warning_confirmation']) ? $_POST['warning_confirmation'] : '';
    $stmt->bind_param("ssssss", $nip_pegawai, $nama_aset, $lokasi_aset, $isi_usulan, $target_file, $warning_confirmation);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        showSuccess("Usulan berhasil dikirim dan sedang diproses.");
    } else {
        showError("Terjadi kesalahan saat menyimpan data. Silakan coba lagi.");
    }
} else {
    showError("Metode request tidak valid.");
}
?>