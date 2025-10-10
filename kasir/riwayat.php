<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

$title = 'Riwayat Transaksi';
require '../includes/header.php';

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

$stmt = $pdo->prepare("
    SELECT t.*, u.username 
    FROM transaksi t 
    JOIN users u ON u.id_user = t.id_user
    $where
    ORDER BY t.tanggal DESC
");
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ringkasan per barang
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

<style>
.card {
    background: #fff8d0;
    border: 1.5px solid #facc15;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
h2,h3 { color:#000; font-weight:700; }

form input[type="date"] {
    height:40px;
    padding:6px 10px;
    border:1.5px solid #facc15;
    border-radius:6px;
}

form button.btn-text-black, .btn {
    background-color:#facc15;
    border:none;
    color:#000;
    font-weight:700;
    padding:8px 16px;
    border-radius:6px;
    text-decoration:none;
    cursor:pointer;
    transition:0.2s;
}
form button.btn-text-black:hover, .btn:hover { background-color:#eab308; }

form a.btn-ghost {
    border:1.5px solid #facc15;
    color:#000;
    background:#fff;
    padding:8px 16px;
    border-radius:6px;
    text-decoration:none;
    margin-left:8px;
    transition:0.2s;
}
form a.btn-ghost:hover { background:#facc15; color:#000; }

table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    font-size:14px;
}
table th, table td {
    padding:10px 12px;
    border:1px solid #f0f0f0;
    text-align:left;
}
table th { background:#facc15; color:#000; }
table tr:nth-child(even) { background:#fffbe6; }

.total-row td { background:#fef3c7; font-weight:700; }
</style>

<h2>Riwayat Transaksi</h2>

<!-- Filter tanggal -->
<div class="card">
  <form method="get" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
    <div>
      <label>Dari</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($start) ?>">
    </div>
    <div>
      <label>Sampai</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($end) ?>">
    </div>
    <div>
      <button type="submit" class="btn-text-black">Filter</button>
      <a href="riwayat.php" class="btn-ghost">Reset</a>
    </div>
  </form>
</div>

<!-- Daftar transaksi -->
<div class="card">
  <h3>Daftar Transaksi</h3>
  <?php if(empty($transaksi)): ?>
    <p class="small">Belum ada transaksi pada rentang waktu tersebut.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Pelanggan</th>
          <th>Status</th>
          <th>Kasir</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($transaksi as $t): 
          $nama_pelanggan = trim($t['pelanggan'] ?? 'Umum');
          $status_member = in_array($nama_pelanggan,$daftar_member)?'Member':'Bukan Member';
        ?>
          <tr>
            <td><?= $t['id_transaksi'] ?></td>
            <td><?= $t['tanggal'] ?></td>
            <td><?= htmlspecialchars($nama_pelanggan) ?></td>
            <td><?= $status_member ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td>
              <a href="/kasir/kasir/nota.php?id=<?= $t['id_transaksi'] ?>" class="btn-ghost">Cetak Nota</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Ringkasan penjualan -->
<div class="card">
  <h3>Ringkasan Penjualan</h3>
  <?php if(empty($agg)): ?>
    <p class="small">Tidak ada penjualan pada rentang waktu tersebut.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Barang</th><th>Jumlah Terjual</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach($agg as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['nama_barang']) ?></td>
            <td><?= $r['total_qty'] ?></td>
            <td>Rp <?= number_format($r['total_amount'],0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row">
          <td><strong>Grand Total</strong></td>
          <td></td>
          <td><strong>Rp <?= number_format($grandAll,0,",",".") ?></strong></td>
        </tr>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Tombol Cetak Ringkasan -->
<div style="margin-top:16px;">
  <?php $q = http_build_query(['start_date'=>$start,'end_date'=>$end]); ?>
  <a href="/kasir/kasir/riwayat_cetak.php?<?= $q ?>" class="btn">Cetak Ringkasan</a>
</div>

<?php require '../includes/footer.php'; ?>
