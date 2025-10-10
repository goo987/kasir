<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$title = 'Laporan Penjualan';
require '../includes/header.php';

// Ambil filter tanggal
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

<style>
/* Umum */
.card {
    background: #fff;
    border: 1.5px solid #facc15;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
h2, h3 {
    color: #000;
    font-weight: 700;
}

/* Form Filter */
form input[type="date"] {
    height: 40px;
    padding: 6px 10px;
    border: 1.5px solid #facc15;
    border-radius: 6px;
}
form button, form a.btn-ghost {
    padding: 8px 16px;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
}
form button.btn-text-black {
    background-color: #facc15;
    border: none;
    color: #000;
    transition: 0.2s;
}
form button.btn-text-black:hover {
    background-color: #eab308;
}
form a.btn-ghost {
    border: 1.5px solid #facc15;
    color: #000;
    background: #fff;
    margin-left: 8px;
    transition: 0.2s;
}
form a.btn-ghost:hover {
    background: #facc15;
    color: #000;
}

/* Tabel */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
table th, table td {
    padding: 10px 12px;
    border: 1px solid #f0f0f0;
    text-align: left;
}
table th {
    background: #facc15;
    color: #000;
}
table tr:nth-child(even) {
    background: #fffbe6;
}

/* Tombol Cetak */
.btn {
    background-color: #facc15;
    color: #000;
    font-weight: 700;
    padding: 10px 18px;
    border-radius: 8px;
    display: inline-block;
    text-decoration: none;
    transition: 0.2s;
}
.btn:hover {
    background-color: #eab308;
}
</style>

<h2>Laporan Penjualan</h2>

<!-- Filter tanggal -->
<div class="card">
  <form method="get" style="display:flex; gap:12px; align-items:flex-end; flex-wrap: wrap;">
    <div>
      <label>Dari</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($start) ?>">
    </div>
    <div>
      <label>Sampai</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($end) ?>">
    </div>
    <div>
      <button type="submit" class="btn btn-text-black">Filter</button>
      <a href="laporan.php" class="btn-ghost">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel Transaksi -->
<div class="card">
  <h3>Transaksi</h3>
  <?php if(empty($transaksi)): ?>
    <p class="small">Tidak ada transaksi pada periode ini.</p>
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
            <td><?= $t['id_transaksi'] ?></td>
            <td><?= $t['tanggal'] ?></td>
            <td><?= htmlspecialchars($nama_pelanggan) ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td>Rp <?= number_format($tot,0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="4"><strong>Grand Total</strong></td>
          <td><strong>Rp <?= number_format($grand,0,",",".") ?></strong></td>
        </tr>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Ringkasan Barang -->
<div class="card">
  <h3>Ringkasan Per Barang</h3>
  <?php if(empty($barang)): ?>
    <p class="small">Tidak ada data barang terjual.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Barang</th><th>Jumlah Terjual</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach($barang as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['nama_barang']) ?></td>
            <td><?= $b['total_qty'] ?></td>
            <td>Rp <?= number_format($b['total_amount'],0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Ringkasan Pelanggan -->
<div class="card">
  <h3>Ringkasan Per Pelanggan</h3>
  <?php if(empty($pelanggan)): ?>
    <p class="small">Tidak ada data pelanggan.</p>
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
            <td><?= $status ?></td>
            <td>Rp <?= number_format($p['total_belanja'],0,",",".") ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Tombol Cetak -->
<div style="margin-top:16px;">
  <?php
    $q = http_build_query(['start_date'=>$start,'end_date'=>$end]);
    $urlCetak = "laporan_cetak.php?$q";
  ?>
  <a href="<?= $urlCetak ?>" class="btn">Cetak Laporan</a>
</div>

<?php require '../includes/footer.php'; ?>
