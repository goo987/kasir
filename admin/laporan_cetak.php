<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date'] ?? '';

// Ambil semua nama pelanggan dari tabel pelanggan untuk cek member
$pelanggan_stmt = $pdo->query("SELECT nama FROM pelanggan");
$daftar_member = array_column($pelanggan_stmt->fetchAll(PDO::FETCH_ASSOC), 'nama');

$where = "";
$params = [];
if ($start && $end) {
    $where = "WHERE t.tanggal BETWEEN ? AND ?";
    $params = [$start . " 00:00:00", $end . " 23:59:59"];
}

// Ambil transaksi lengkap
$sql = "SELECT t.*, u.username 
        FROM transaksi t 
        JOIN users u ON u.id_user = t.id_user
        $where
        ORDER BY t.tanggal DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ringkasan per barang
$sqlBarang = "SELECT b.nama_barang, SUM(td.qty) as total_qty, SUM(td.subtotal) as total_amount
              FROM transaksi_detail td
              JOIN barang b ON b.id_barang = td.id_barang
              JOIN transaksi t ON t.id_transaksi = td.id_transaksi
              $where
              GROUP BY td.id_barang
              ORDER BY total_amount DESC";
$stBarang = $pdo->prepare($sqlBarang);
$stBarang->execute($params);
$barang = $stBarang->fetchAll(PDO::FETCH_ASSOC);

// Ringkasan per pelanggan
$sqlPelanggan = "SELECT COALESCE(t.pelanggan,'Umum') as nama_pelanggan, SUM(td.subtotal) as total_belanja
                 FROM transaksi t
                 JOIN transaksi_detail td ON td.id_transaksi = t.id_transaksi
                 $where
                 GROUP BY nama_pelanggan
                 ORDER BY total_belanja DESC";
$stPelanggan = $pdo->prepare($sqlPelanggan);
$stPelanggan->execute($params);
$pelanggan = $stPelanggan->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Laporan Penjualan</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 30px;
    color: #000;
  }
  h2, h3 {
    text-align: center;
    margin-bottom: 10px;
  }
  h3 {
    margin-top: 25px;
    text-align: left;
  }
  .periode {
    text-align: center;
    margin-bottom: 20px;
    font-size: 14px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    margin-bottom: 20px;
  }
  th, td {
    border: 1px solid #000;
    padding: 8px 10px;
    font-size: 14px;
  }
  th {
    background: #facc15;
  }
  tr:nth-child(even) {
    background: #fffbe6;
  }
  .right {
    text-align: right;
  }
  .center {
    text-align: center;
  }
  .btn {
    display: inline-block;
    padding: 10px 20px;
    margin-right: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #000;
    background: #facc15;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s ease;
    text-decoration: none;
  }
  .btn:hover {
    background: #eab308;
    transform: translateY(-1px);
  }
  @media print {
    .no-print {
      display: none;
    }
    @page {
      size: A4 portrait;
      margin: 1cm;
    }
  }
</style>
</head>
<body>

<h2>LAPORAN PENJUALAN</h2>
<div class="periode">
  Periode: 
  <?= $start ? htmlspecialchars($start) : '-' ?> 
  s/d 
  <?= $end ? htmlspecialchars($end) : '-' ?>
</div>

<!-- Tombol Cetak & Kembali -->
<div class="no-print" style="margin-bottom: 20px; text-align:center;">
  <button class="btn" onclick="window.print()">Cetak</button>
  <a href="laporan.php" class="btn">Kembali</a>
</div>

<!-- Transaksi -->
<h3>Transaksi</h3>
<?php if(empty($transaksi)): ?>
  <p>Tidak ada transaksi pada periode ini.</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Tanggal</th>
        <th>Pelanggan</th>
        <th>Kasir</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $grand=0;
      foreach($transaksi as $t): 
        $q = $pdo->prepare("SELECT SUM(subtotal) FROM transaksi_detail WHERE id_transaksi=?");
        $q->execute([$t['id_transaksi']]);
        $tot = $q->fetchColumn();
        $grand += $tot;
        $nama_pelanggan = trim($t['pelanggan'] ?? 'Umum');
      ?>
      <tr>
        <td class="center"><?= $t['id_transaksi'] ?></td>
        <td><?= $t['tanggal'] ?></td>
        <td><?= htmlspecialchars($nama_pelanggan) ?></td>
        <td><?= htmlspecialchars($t['username']) ?></td>
        <td class="right">Rp <?= number_format($tot,0,",",".") ?></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="4" class="right"><strong>Grand Total</strong></td>
        <td class="right"><strong>Rp <?= number_format($grand,0,",",".") ?></strong></td>
      </tr>
    </tbody>
  </table>
<?php endif; ?>

<!-- Ringkasan Barang -->
<h3>Ringkasan Per Barang</h3>
<?php if(empty($barang)): ?>
  <p>Tidak ada data barang terjual.</p>
<?php else: ?>
  <table>
    <thead><tr><th>Barang</th><th>Jumlah Terjual</th><th>Total</th></tr></thead>
    <tbody>
      <?php foreach($barang as $b): ?>
        <tr>
          <td><?= htmlspecialchars($b['nama_barang']) ?></td>
          <td class="center"><?= $b['total_qty'] ?></td>
          <td class="right">Rp <?= number_format($b['total_amount'],0,",",".") ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<!-- Ringkasan Pelanggan -->
<h3>Ringkasan Per Pelanggan</h3>
<?php if(empty($pelanggan)): ?>
  <p>Tidak ada data pelanggan.</p>
<?php else: ?>
  <table>
    <thead><tr><th>Pelanggan</th><th>Status</th><th>Total Belanja</th></tr></thead>
    <tbody>
      <?php foreach($pelanggan as $p): 
        $nama = trim($p['nama_pelanggan']);
        $status = in_array($nama, $daftar_member) ? 'Member' : 'Bukan Member';
      ?>
        <tr>
          <td><?= htmlspecialchars($nama) ?></td>
          <td class="center"><?= $status ?></td>
          <td class="right">Rp <?= number_format($p['total_belanja'],0,",",".") ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<br><br>
<table style="border:none; width:100%;">
  <tr>
    <td style="border:none; text-align:right;">
      <div style="text-align:center;">
        <p>Mengetahui,</p>
        <p><strong>Admin</strong></p>
        <br><br><br>
        <p>(_________________)</p>
      </div>
    </td>
  </tr>
</table>

</body>
</html>
