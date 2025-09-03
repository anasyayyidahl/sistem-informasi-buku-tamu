<?php
session_start();

// Timeout dalam detik
$timeout = 600;

// Cek sesi login admin
if (
    !isset($_SESSION["admin_logged_in"]) || 
    $_SESSION["admin_logged_in"] !== true ||
    $_SESSION["user_agent"] !== $_SERVER["HTTP_USER_AGENT"]
) {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit;
}

// Cek apakah sesi sudah kedaluwarsa
if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive > $timeout) {
        session_unset();
        session_destroy();
        header("Location: login.html?expired=1");
        exit;
    }
}

// Perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();

include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Buku Tamu</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Header -->
  <header class="bg-[#67AE6E] text-white px-6 py-4 shadow sticky top-0 z-50">
    <div class="container mx-auto flex justify-between items-center">
      <h1 class="text-xl font-bold">Dashboard Admin</h1>
      <a href="logout.php" class="bg-white text-[#67AE6E] px-4 py-1 rounded hover:bg-gray-100 text-sm">Logout</a>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-semibold mb-6">Rekap Kunjungan</h2>

    <!-- Filter -->
    <div class="flex flex-wrap items-end gap-4 mb-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Filter Waktu</label>
        <select id="filterWaktu" class="border px-3 py-2 rounded text-sm">
          <option value="all">Semua Waktu</option>
          <option value="today">Hari Ini</option>
          <option value="month">Bulan Ini</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>
        <input id="filterTanggal" type="date" class="border px-3 py-2 rounded text-sm" />
      </div>
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table id="tabelKunjungan" class="min-w-full text-sm text-gray-700">
        <thead class="bg-[#A0C8A6] text-white uppercase text-xs tracking-wider">
          <tr>
            <th class="px-6 py-3 text-left">No.</th>
            <th class="px-6 py-3 text-left">Nama</th>
            <th class="px-6 py-3 text-left">Asal Instansi/Lembaga</th>
            <th class="px-6 py-3 text-left">Nomor Telepon</th>
            <th class="px-6 py-3 text-left">Tanggal</th>
            <th class="px-6 py-3 text-left">Keperluan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php
          $no = 1;
          $where = "";
          if (isset($_GET['tanggal']) && $_GET['tanggal'] !== '') {
              $tanggal = $conn->real_escape_string(htmlspecialchars($_GET['tanggal']));
              $where = "WHERE DATE(tanggal_kunjungan) = '$tanggal'";
          } elseif (isset($_GET['waktu']) && $_GET['waktu'] === 'today') {
              $where = "WHERE DATE(tanggal_kunjungan) = CURDATE()";
          } elseif (isset($_GET['waktu']) && $_GET['waktu'] === 'month') {
              $where = "WHERE MONTH(tanggal_kunjungan) = MONTH(CURDATE()) AND YEAR(tanggal_kunjungan) = YEAR(CURDATE())";
          }

          $sql = "SELECT nama_lengkap, asal_instansi, nomor_telepon, tanggal_kunjungan, keperluan 
                  FROM kunjungan_tamu $where 
                  ORDER BY tanggal_kunjungan DESC";

          $result = $conn->query($sql);
          if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                          <td class='px-6 py-3'>{$no}</td>
                          <td class='px-6 py-3'>{$row['nama_lengkap']}</td>
                          <td class='px-6 py-3'>{$row['asal_instansi']}</td>
                          <td class='px-6 py-3'>{$row['nomor_telepon']}</td>
                          <td class='px-6 py-3'>" . date('d-m-Y', strtotime($row['tanggal_kunjungan'])) . "</td>
                          <td class='px-6 py-3'>{$row['keperluan']}</td>
                        </tr>";
                  $no++;
              }
          } else {
              echo "<tr><td colspan='6' class='text-center px-6 py-3 text-gray-500'>Belum ada data kunjungan.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Tombol Ekspor -->
    <div class="mt-6 flex flex-wrap gap-4">
      <button onclick="exportPDF()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">Export PDF</button>
    </div>
  </main>

  <!-- JavaScript untuk filter -->
  <script>
    document.getElementById('filterWaktu').addEventListener('change', function () {
      const waktu = this.value;
      const url = new URL(window.location.href);
      url.searchParams.delete('tanggal');
      if (waktu !== 'all') {
        url.searchParams.set('waktu', waktu);
      } else {
        url.searchParams.delete('waktu');
      }
      window.location.href = url.toString();
    });

    document.getElementById('filterTanggal').addEventListener('change', function () {
      const tanggal = this.value;
      const url = new URL(window.location.href);
      url.searchParams.delete('waktu');
      if (tanggal !== '') {
        url.searchParams.set('tanggal', tanggal);
      } else {
        url.searchParams.delete('tanggal');
      }
      window.location.href = url.toString();
    });

    // Tampilkan pilihan yang aktif saat reload
    window.addEventListener('DOMContentLoaded', function () {
      const url = new URL(window.location.href);
      const waktu = url.searchParams.get('waktu');
      const tanggal = url.searchParams.get('tanggal');
      if (waktu) document.getElementById('filterWaktu').value = waktu;
      if (tanggal) document.getElementById('filterTanggal').value = tanggal;
    });

    // Ekspor PDF
    async function exportPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      doc.setFontSize(13);
      doc.text("Rekapitulasi Kunjungan Tamu", 14, 15);

      const headers = [["No.", "Nama", "Instansi", "Nomor Telepon", "Tanggal", "Keperluan"]];
      const rows = [];

      document.querySelectorAll("#tabelKunjungan tbody tr").forEach(tr => {
        const row = [];
        tr.querySelectorAll("td").forEach(td => row.push(td.innerText));
        rows.push(row);
      });

      doc.autoTable({
        head: headers,
        body: rows,
        startY: 25,
        styles: { fontSize: 10 },
        margin: { left: 14, right: 14 }
      });

      const date = new Date().toLocaleDateString("id-ID", {
        day: "numeric", month: "long", year: "numeric"
      });

      const pageWidth = doc.internal.pageSize.getWidth();
      const x = pageWidth - 20;
      const y = doc.lastAutoTable.finalY + 20;
      const lineSpacing = 5;
      let offset = 0;

      doc.setFontSize(10);
      doc.text(`Surabaya, ${date}`, x, y + offset, { align: "right" }); offset += lineSpacing * 2;
      doc.text("Mengetahui,", x, y + offset, { align: "right" }); offset += lineSpacing * 4;
      doc.text("Admin Buku Tamu", x, y + offset, { align: "right" });

      doc.save("rekap_kunjungan.pdf");
    }
  </script>
</body>
</html>
