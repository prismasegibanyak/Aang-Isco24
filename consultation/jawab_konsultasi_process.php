<?php
session_start();
include '../db.php';

// Pastikan pengguna sudah login sebagai petugas
if (!isset($_SESSION['username'])) {
    header("Location: ../login/index.php");
    exit();
}

// Validasi role
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'petugas') {
    die("Akses ditolak. Hanya untuk admin/petugas.");
}

// Validasi input
if (empty($_POST['id_usulan'])) {
    header("Location: jawab_konsultasi.php?error=ID Usulan tidak valid");
    exit();
}

// Ambil dan sanitasi data dari form
$id_usulan = (int)$_POST['id_usulan'];
$isi_tanggapan = $conn->real_escape_string(trim($_POST['isi_tanggapan']));
$level_resiko = $conn->real_escape_string(trim($_POST['level_resiko']));
$status = $conn->real_escape_string(trim($_POST['status']));
$nip_petugas = $_SESSION['username'];

// Validasi data wajib
if (empty($isi_tanggapan)) {
    header("Location: jawab_konsultasi.php?id=$id_usulan&error=Isi tanggapan wajib diisi");
    exit();
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // Cari nama petugas
    $sql_petugas = "SELECT nama_petugas FROM petugas WHERE username_petugas = ?";
    $stmt_petugas = $conn->prepare($sql_petugas);
    $stmt_petugas->bind_param("s", $nip_petugas);
    $stmt_petugas->execute();
    $result_petugas = $stmt_petugas->get_result();
    
    $nama_petugas = $result_petugas->num_rows > 0 
        ? $result_petugas->fetch_assoc()['nama_petugas'] 
        : 'Tidak Diketahui';

    // Proses upload file
    $file_tanggapan = null;
    if (!empty($_FILES['file_jawaban']['name'])) {
        $target_dir = "../uploads/file_jawaban/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = basename($_FILES['file_jawaban']['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        
        // Validasi ekstensi file
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Hanya file PDF, JPG, JPEG, PNG, dan GIF yang diperbolehkan.");
        }
        
        $new_file_name = uniqid("jawaban_", true) . "." . $file_extension;
        $target_file = $target_dir . $new_file_name;

        if (!move_uploaded_file($_FILES['file_jawaban']['tmp_name'], $target_file)) {
            throw new Exception("Gagal mengunggah file.");
        }

        $file_tanggapan = $new_file_name;
    }

    // Simpan tanggapan ke database
    $sql = "INSERT INTO jwb_usulan (id_usulan, isi_tanggapan, dokumen_tanggapan, tgl_tanggapan, nip_petugas, nama_petugas, level_resiko, status) 
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $id_usulan, $isi_tanggapan, $file_tanggapan, $nip_petugas, $nama_petugas, $level_resiko, $status);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan tanggapan: " . $stmt->error);
    }

    // Update status usulan di tabel usulan
    $sql_update = "UPDATE usulan SET status = ? WHERE Id_usulan = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $status, $id_usulan);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Gagal mengupdate status usulan: " . $stmt_update->error);
    }

    // Commit transaksi jika semua berhasil
    $conn->commit();
    
    header("Location: view_konsultasi_petugas.php?success=Tanggapan berhasil dikirim&updated=1");
    exit();

} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $conn->rollback();
    
    // Hapus file yang sudah terupload jika ada error
    if (isset($target_file)) {
        @unlink($target_file);
    }
    
    header("Location: jawab_konsultasi.php?id=$id_usulan&error=" . urlencode($e->getMessage()));
    exit();
} finally {
    // Tutup statement
    if (isset($stmt)) $stmt->close();
    if (isset($stmt_update)) $stmt_update->close();
    if (isset($stmt_petugas)) $stmt_petugas->close();
    $conn->close();
}
?>