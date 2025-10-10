<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

if (!isset($_GET['id'])) die("ID transaksi tidak ditemukan.");
$id = (int)$_GET['id'];

// Ambil data transaksi
$stmt = $pdo->prepare("
    SELECT t.*, u.username
    FROM transaksi t
    JOIN users u ON u.id_user = t.id_user
    WHERE t.id_transaksi = ?
");
$stmt->execute([$id]);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$trx) die("Transaksi tidak ditemukan.");

// Ambil detail transaksi
$detail = $pdo->prepare("
    SELECT td.*, b.nama_barang
    FROM transaksi_detail td
    JOIN barang b ON b.id_barang = td.id_barang
    WHERE td.id_transaksi = ?
");
$detail->execute([$id]);
$items = $detail->fetchAll(PDO::FETCH_ASSOC);

$title = "Nota #{$id}";
require '../includes/header.php';
?>

<h2>Nota Transaksi</h2>

<div class="card">
  <p><strong>ID:</strong> <?= $trx['id_transaksi'] ?></p>
  <p><strong>Tanggal:</strong> <?= $trx['tanggal'] ?></p>
  <p><strong>Kasir:</strong> <?= htmlspecialchars($trx['username']) ?></p>
  <p><strong>Pelanggan:</strong> <?= htmlspecialchars($trx['pelanggan'] ?? 'Umum') ?></p>

  <table>
    <thead>
      <tr>
        <th>Barang</th>
        <th>Qty</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php $grand = 0; foreach ($items as $it): $grand += $it['subtotal']; ?>
        <tr>
          <td><?= htmlspecialchars($it['nama_barang']) ?></td>
          <td><?= $it['qty'] ?></td>
          <td>Rp <?= number_format($it['subtotal'],0,",",".") ?></td>
        </tr>
      <?php endforeach; ?>
      <tr class="total-row">
        <td colspan="2"><strong>Total</strong></td>
        <td><strong>Rp <?= number_format($grand,0,",",".") ?></strong></td>
      </tr>
    </tbody>
  </table>

  <div class="actions">
    <button class="btn" onclick="window.print()">Cetak Nota</button>
    <a class="btn-ghost" href="/kasir/dashboard.php">← Kembali ke Dashboard</a>
  </div>
</div>

<style>
.card {
  background: #fff8d0;
  padding: 16px;
  border-radius: 12px;
  box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
  max-width: 480px;
  margin: 0 auto 20px;
}

h2 {
  text-align: center;
  margin-bottom: 16px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 12px;
  font-size: 14px;
}

th, td {
  padding: 6px 8px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background: #facc15;
  color: #000;
  font-weight: 700;
}

tr.total-row td {
  background: #fef3c7;
  font-weight: 700;
}

.actions {
  margin-top: 16px;
  display: flex;
  gap: 8px;
}

/* tombol */
.btn {
  display: inline-block;
  background: #facc15;
  color: #000;
  font-weight: 700;
  border: 2px solid #000;
  border-radius: 8px;
  padding: 8px 16px;
  text-decoration: none;
  box-shadow: 3px 3px 0 #000;
  cursor: pointer;
  transition: all 0.2s;
}

.btn:hover { 
  background: #eab308; 
  transform: translate(-2px,-2px);
}

.btn-ghost {
  display: inline-block;
  background: #fff;
  color: #000;
  font-weight: 700;
  border: 2px solid #000;
  border-radius: 8px;
  padding: 8px 16px;
  text-decoration: none;
  box-shadow: 3px 3px 0 #000;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-ghost:hover {
  background: #facc15;
  transform: translate(-2px,-2px);
}

/* styling print */
@media print {
  body {
    font-family: monospace;
    font-size: 14px;
    width: 280px;
    margin: 0 auto;
    background: #fff;
  }
  .card { box-shadow:none; border:none; padding:0; margin:0; }
  h2 { font-size:16px; text-align:center; border-bottom:1px dashed #000; margin-bottom:8px; padding-bottom:4px; }
  table, th, td { border:none; padding:2px 0; }
  .actions, button, .btn-ghost { display:none; }
}
</style>

<?php require '../includes/footer.php'; ?>
