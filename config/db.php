<?php
$servername = "localhost";
$username = "root";  // Ganti dengan username database Anda
$password = "";      // Ganti dengan password database Anda
$dbname = "sipak1";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>