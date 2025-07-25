<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['role'])) {
        header("Location: index.php?error=Input tidak valid!");
        exit();
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi input
    if (empty($username) || empty($password) || empty($role)) {
        header("Location: index.php?error=Harap isi semua field!");
        exit();
    }

    // Escape input untuk keamanan
    $username = mysqli_real_escape_string($conn, $username);

    // Query untuk memeriksa petugas
    $sql = "SELECT * FROM petugas WHERE username_petugas = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement error: " . $conn->error);
        header("Location: index.php?error=Terjadi kesalahan sistem!");
        exit();
    }

    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Debugging log untuk username
        error_log("Username ditemukan: $username");

        // Verifikasi password yang di-hash
        if (password_verify($password, $user['password_petugas'])) {
            session_regenerate_id(true); // Amankan sesi

            $_SESSION['username'] = $user['username_petugas'];
            $_SESSION['role'] = $user['role'];

            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                header("Location: ../home_admin.php");
                exit();
            } elseif ($user['role'] == 'petugas') {
                header("Location: ../web/home_petugas.php");
                exit();
            }
        } else {
            error_log("Password salah untuk username: $username");
            header("Location: index.php?error=Password salah!");
            exit();
        }
    } else {
        error_log("Username tidak ditemukan: $username");
        header("Location: index.php?error=Username tidak ditemukan!");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>