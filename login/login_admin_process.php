<?php
session_start();
include '../config/db.php';

// Menampilkan semua error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fungsi untuk redirect dengan pesan error
function redirectWithError($message) {
    $_SESSION['login_error'] = $message;
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    if (empty($_POST['username']) || empty($_POST['password'])) {
        redirectWithError("Username dan password harus diisi");
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi panjang input
    if (strlen($username) > 50 || strlen($password) > 255) {
        redirectWithError("Input terlalu panjang");
    }

    // Query aman dengan prepared statement
    $sql = "SELECT * FROM petugas WHERE username_petugas = ? AND role = 'admin' LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        redirectWithError("Terjadi kesalahan sistem");
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password_petugas'])) {
            // Regenerate session ID untuk mencegah fixation
            session_regenerate_id(true);
            
            // Menyimpan session
            $_SESSION['username'] = $user['username_petugas'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_login'] = time();

            // Redirect ke halaman admin
            header("Location: ../admin/home_admin.php");
            exit();
        }
    }
    
    // Berikan pesan error yang sama untuk username/password salah
    // Untuk mencegah user enumeration
    redirectWithError("Username atau password salah");
} else {
    // Jika bukan metode POST
    header("Location: index.php");
    exit();
}
?>