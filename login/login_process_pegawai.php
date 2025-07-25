<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Escape user inputs for security
    $username = mysqli_real_escape_string($conn, $username);

    // Query untuk pegawai
    $sql = "SELECT * FROM pegawai WHERE username_pegawai = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Debugging: Tampilkan username yang diinput
        error_log("Username input: $username");
        
        // Verifikasi password yang di-hash
        if (password_verify($password, $user['password_pegawai'])) {
            $_SESSION['username'] = $user['username_pegawai'];
            $_SESSION['role'] = 'pegawai';
            header("Location: ../web/home.php");
            exit();
        } else {
            header("Location: ../login/../login/index.php?error=Password salah!");
            exit();
        }
    } else {
        header("Location: ../login/../login/index.php?error=Username tidak ditemukan!");
        exit();
    }
} else {
    header("Location: ../login/index.php");
    exit();
}
?>