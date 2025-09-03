<?php
// Konfigurasi koneksi database
$host = "localhost";
$user = "root";
$password = "";
$database = "buku_tamu_bdk";

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    http_response_code(500);
    echo "Koneksi gagal: " . $conn->connect_error;
    exit();
}

// Ambil data dari POST
$nama           = $_POST['nama'] ?? '';
$instansi       = $_POST['instansi'] ?? '';
$nomor_telepon  = $_POST['nomor_telepon'] ?? '';
$tanggal        = $_POST['tanggal'] ?? '';
$keperluan        = $_POST['keperluan'] ?? '';

// Validasi sederhana
if (!$nama || !$instansi || !$nomor_telepon || !$tanggal || !$keperluan) {
    http_response_code(400);
    echo "Data tidak lengkap";
    exit();
}

// Siapkan statement SQL
$stmt = $conn->prepare("INSERT INTO tamu (nama, instansi, nomor_telepon, tanggal, keperluan) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nama, $instansi, $nomor_telepon, $tanggal, $keperluan);

// Eksekusi dan cek hasil
if ($stmt->execute()) {
    echo "Data berhasil disimpan.";
} else {
    echo "Gagal menyimpan data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
