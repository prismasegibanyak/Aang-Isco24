<?php
include '../db.php'; // Menyertakan file db.php untuk koneksi database

$nip = $_POST['nip'];
$nama = $_POST['nama'];
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$no_hp = $_POST['no_hp'];

// Memeriksa apakah username atau NIP sudah ada
$sql = "SELECT * FROM pegawai WHERE username_pegawai = ? OR nip_pegawai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Username atau NIP sudah ada
    header("Location: register.php?error=Username%20atau%20NIP%20sudah%20digunakan!");
} else {
    // Mengamankan password dengan hash
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Melakukan query untuk menyimpan data pengguna baru
    $sql = "INSERT INTO pegawai (nip_pegawai, nama_pegawai, username_pegawai, password_pegawai, email_pegawai, no_hp_pegawai, role) VALUES (?, ?, ?, ?, ?, ?, 'Pegawai')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nip, $nama, $username, $hashed_password, $email, $no_hp);

    if ($stmt->execute() === TRUE) {
        header("Location: ../login/index.php?success=Registrasi%20berhasil!%20Silakan%20login.");
    } else {
        header("Location: register.php?error=Terjadi%20kesalahan.%20Silakan%20coba%20lagi.");
    }
}

$stmt->close();
$conn->close();
?>