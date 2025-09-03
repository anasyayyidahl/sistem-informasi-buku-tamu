<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
$tujuan         = $_POST['keperluan'] ?? '';

// Validasi sederhana
if (!$nama || !$instansi || !$nomor_telepon || !$tanggal || !$tujuan) {
    http_response_code(400);
    echo "Data tidak lengkap";
    exit();
}

// Siapkan statement SQL (cocok dengan nama tabel & kolom yang ada)
$stmt = $conn->prepare("INSERT INTO kunjungan_tamu (nama_lengkap, asal_instansi, nomor_telepon, tanggal_kunjungan, keperluan) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nama, $instansi, $nomor_telepon, $tanggal, $tujuan);

// Eksekusi dan cek hasil
if ($stmt->execute()) {
    http_response_code(200);
    echo "Data berhasil disimpan";
} else {
    http_response_code(500);
    echo "Gagal menyimpan data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
