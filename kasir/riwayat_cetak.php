<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date'] ?? '';

$pelanggan_stmt = $pdo->query("SELECT nama FROM pelanggan");
$daftar_member = array_column($pelanggan_stmt->fetchAll(PDO::FETCH_ASSOC), 'nama');

$where  = "";
$params = [];
if ($start && $end) {
    $where = "WHERE t.tanggal BETWEEN ? AND ?";
    $params[] = $start . " 00:00:00";
    $params[] = $end . " 23:59:59";
}

// Ambil transaksi
$stmt = $pdo->prepare("
    SELECT t.*, u.username 
    FROM transaksi t 
    JOIN users u ON u.id_user = t.id_user
    $where
    ORDER BY t.tanggal DESC
");
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ringkasan penjualan
$sqlAgg = "
    SELECT b.nama_barang, SUM(td.qty) AS total_qty, SUM(td.subtotal) AS total_amount
    FROM transaksi_detail td
    JOIN transaksi t ON t.id_transaksi = td.id_transaksi
    JOIN barang b ON b.id_barang = td.id_barang
";
if ($where) $sqlAgg .= " $where ";
$sqlAgg .= " GROUP BY td.id_barang ORDER BY total_amount DESC";

$stAgg = $pdo->prepare($sqlAgg);
$stAgg->execute($params);
$agg = $stAgg->fetchAll(PDO::FETCH_ASSOC);
$grandAll = 0;
foreach ($agg as $r) $grandAll += $r['total_amount'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ringkasan Transaksi</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 30px;
    color: #000;
}
h2,h3 { color:#000; font-weight:700; text-align:center; }
h3 { text-align:left; margin-top:25px; }
.periode { text-align:center; margin-bottom:20px; font-size:14px; font-style:italic; }
table {
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
    margin-bottom:20px;
    font-size:14px;
}
th, td {
    border:1px solid #f0f0f0;
    padding:10px 12px;
    text-align:left;
}
th { background:#facc15; color:#000; }
tr:nth-child(even) td { background:#fffbe6; }
.total-row td { background:#fef3c7; font-weight:700; }
.right { text-align:right; }
.center { text-align:center; }
.btn {
    display:inline-block;
    background:#facc15;
    color:#000;
    font-weight:700;
    padding:8px 16px;
    border-radius:6px;
    text-decoration:none;
    cursor:pointer;
    transition:0.2s;
}
.btn:hover { background:#eab308; }
.no-print { text-align:center; margin-bottom:16px; }
@media print { .no-print { display:none; } }
</style>
</head>
<body>

<h2>RIWAYAT TRANSAKSI</h2>
<div class="periode">
  Periode: <?= $start ? htmlspecialchars($start) : '-' ?> s/d <?= $end ? htmlspecialchars($end) : '-' ?>
</div>

<div class="no-print">
  <button class="btn" onclick="window.print()">Cetak</button>
  <a href="/kasir/kasir/riwayat.php" class="btn">Kembali</a>
</div>

<h3>Daftar Transaksi</h3>
<?php if(empty($transaksi)): ?>
  <p>Belum ada transaksi pada rentang waktu tersebut.</p>
<?php else: ?>
<table>
<thead>
<tr>
  <th>ID</th>
  <th>Tanggal</th>
  <th>Pelanggan</th>
  <th>Status</th>
  <th>Kasir</th>
</tr>
</thead>
<tbody>
<?php foreach($transaksi as $t): 
  $nama_pelanggan = trim($t['pelanggan'] ?? 'Umum');
  $status_member = in_array($nama_pelanggan,$daftar_member)?'Member':'Bukan Member';
  $q = $pdo->prepare("SELECT SUM(subtotal) FROM transaksi_detail WHERE id_transaksi=?");
  $q->execute([$t['id_transaksi']]);
  $tot = $q->fetchColumn();
?>
<tr>
  <td><?= $t['id_transaksi'] ?></td>
  <td><?= $t['tanggal'] ?></td>
  <td><?= htmlspecialchars($nama_pelanggan) ?></td>
  <td class="center"><?= $status_member ?></td>
  <td><?= htmlspecialchars($t['username']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<h3>Ringkasan Penjualan</h3>
<?php if(empty($agg)): ?>
  <p>Tidak ada penjualan pada rentang waktu tersebut.</p>
<?php else: ?>
<table>
<thead>
<tr><th>Barang</th><th>Jumlah Terjual</th><th>Total</th></tr>
</thead>
<tbody>
<?php foreach($agg as $r): ?>
<tr>
  <td><?= htmlspecialchars($r['nama_barang']) ?></td>
  <td class="center"><?= $r['total_qty'] ?></td>
  <td class="right">Rp <?= number_format($r['total_amount'],0,",",".") ?></td>
</tr>
<?php endforeach; ?>
<tr class="total-row">
  <td><strong>Grand Total</strong></td>
  <td></td>
  <td class="right"><strong>Rp <?= number_format($grandAll,0,",",".") ?></strong></td>
</tr>
</tbody>
</table>
<?php endif; ?>

</body>
</html>
