<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "buku_tamu_bdk"; // Ganti jika nama database berbeda

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
