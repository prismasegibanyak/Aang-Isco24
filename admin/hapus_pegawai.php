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

// Periksa apakah parameter "nip" ada
if (!isset($_GET['nip'])) {
    die("NIP pegawai tidak ditemukan.");
}

$nip = $_GET['nip'];

// Menghapus data pegawai berdasarkan NIP
$sql = "DELETE FROM pegawai WHERE nip_pegawai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nip);

if ($stmt->execute()) {
    header("Location: profile_pegawai.php?message=success");
    exit();
} else {
    $error = "Terjadi kesalahan saat menghapus data: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Pegawai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f8f9fa; min-height: 100vh; color: #333; }
        .hapus-content {
            background: white;
            border-radius: 10px;
            max-width: 420px;
            margin: 80px auto 0 auto;
            box-shadow: 0 5px 18px rgba(51,107,135,0.12);
            padding: 34px 25px 28px 25px;
            text-align: center;
        }
        .hapus-content h2 { color: #dc3545; margin-bottom: 16px; }
        .hapus-content p { font-size: 1.1rem; margin-bottom: 15px; }
        .hapus-content a { display: inline-block; padding: 8px 18px; border-radius: 5px; text-decoration: none; background: #336B87; color: #fff; font-weight: 600; }
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
        .footer p { margin: 0; color: #336B87; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="hapus-content">
        <h2><i class="fas fa-exclamation-triangle"></i> Gagal Menghapus!</h2>
        <p><?php echo isset($error) ? htmlspecialchars($error) : "Terjadi kesalahan tak terduga."; ?></p>
        <a href="profile_pegawai.php">Kembali ke Profile Pegawai</a>
    </div>
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Aang_Isco24. All rights reserved.</p>
    </footer>
</body>
</html>