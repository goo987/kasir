<?php
require 'config.php';
require_login();
$title = 'Dashboard';
require 'includes/header.php';
?>

<style>
body {
  background: linear-gradient(135deg, #fff, #fffef0, #fff9c4);
  background-size: 200% 200%;
  animation: gradmove 10s ease-in-out infinite alternate;
  color: #222;
  font-family: "Poppins", sans-serif;
  margin: 0;
  padding: 0;
}

@keyframes gradmove {
  0% { background-position: 0% 50%; }
  100% { background-position: 100% 50%; }
}

.dashboard-header {
  text-align: center;
  margin: 40px 0 30px;
  animation: fadeInDown 0.8s ease;
}
.dashboard-header h2 {
  font-size: 30px;
  color: #000;
  font-weight: 800;
}
.dashboard-header p {
  color: #444;
  font-weight: 500;
}

/* GRID BOXES */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  justify-content: center;
  max-width: 950px;
  margin: 0 auto 60px;
  padding: 0 20px;
  animation: fadeIn 1s ease;
}
.card-stat {
  background: linear-gradient(145deg, #ffffff, #fff6d0);
  border-radius: 16px;
  padding: 28px 20px;
  box-shadow: 0 6px 14px rgba(0,0,0,0.12);
  text-align: center;
  border: 2px solid #facc15;
  transition: all 0.3s ease;
}
.card-stat:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}
.card-stat h5 {
  font-size: 17px;
  color: #111;
  margin-bottom: 12px;
  font-weight: 700;
  text-transform: uppercase;
}
.card-stat .number {
  font-size: 40px;
  color: #eab308;
  font-weight: 900;
}

/* Statistik Section */
.stats-section {
  max-width: 850px;
  margin: 0 auto 80px;
  text-align: center;
  background: #fffef8;
  border-radius: 18px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  padding: 25px 20px 40px;
  border: 2px solid #facc15;
  animation: fadeIn 1s ease;
}
.stats-section h3 {
  color: #000;
  font-weight: 700;
  font-size: 22px;
  margin-bottom: 18px;
}

canvas {
  width: 100%;
  max-width: 760px;
  height: 350px !important;
  margin: 0 auto;
  display: block;
}

@keyframes fadeInDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="dashboard-header">
  <h2>Dashboard Utama</h2>
  <p>Selamat datang kembali, 
    <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong> 
    (Peran: <?= htmlspecialchars($_SESSION['user']['role']) ?>)
  </p>
</div>

<?php
$totalTransaksi = $pdo->query('SELECT COUNT(*) as c FROM transaksi')->fetch(PDO::FETCH_ASSOC)['c'];
$totalBarang = $pdo->query('SELECT COUNT(*) as c FROM barang')->fetch(PDO::FETCH_ASSOC)['c'];
$totalUser = $pdo->query('SELECT COUNT(*) as c FROM users')->fetch(PDO::FETCH_ASSOC)['c'];
$totalPendapatan = $pdo->query('SELECT IFNULL(SUM(subtotal),0) as total FROM transaksi_detail')->fetch(PDO::FETCH_ASSOC)['total'];

$pelangganMember = $pdo->query('SELECT COUNT(*) as c FROM pelanggan')->fetch(PDO::FETCH_ASSOC)['c'];
$pelangganNonMember = $pdo->query('SELECT COUNT(DISTINCT pelanggan) as c FROM transaksi WHERE pelanggan IS NOT NULL AND pelanggan != ""')->fetch(PDO::FETCH_ASSOC)['c'];
$totalPelanggan = $pelangganMember + $pelangganNonMember;

$stmt = $pdo->query("
  SELECT DATE_FORMAT(tanggal, '%b') AS bulan, COUNT(*) AS total
  FROM transaksi
  WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY MONTH(tanggal)
  ORDER BY MONTH(tanggal)
");
$dataBulan = [];
$dataTotal = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $dataBulan[] = $row['bulan'];
  $dataTotal[] = (int)$row['total'];
}
?>

<!-- 3 box atas -->
<div class="dashboard-grid">
  <div class="card-stat">
    <h5>Total Transaksi</h5>
    <div class="number"><?= $totalTransaksi; ?></div>
  </div>
  <div class="card-stat">
    <h5>Jumlah Barang</h5>
    <div class="number"><?= $totalBarang; ?></div>
  </div>
  <div class="card-stat">
    <h5>Total Pengguna</h5>
    <div class="number"><?= $totalUser; ?></div>
  </div>
</div>

<!-- 2 box tengah -->
<div class="dashboard-grid" style="max-width:650px;">
  <div class="card-stat">
    <h5>Total Pendapatan</h5>
    <div class="number"><?= "Rp " . number_format($totalPendapatan, 0, ',', '.'); ?></div>
  </div>
  <div class="card-stat">
    <h5>Jumlah Pelanggan</h5>
    <div class="number"><?= $totalPelanggan; ?></div>
  </div>
</div>

<!-- Grafik Statistik -->
<div class="stats-section">
  <h3>📊 Statistik Transaksi 6 Bulan Terakhir</h3>
  <canvas id="salesChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($dataBulan) ?>,
    datasets: [{
      label: 'Jumlah Transaksi',
      data: <?= json_encode($dataTotal) ?>,
      borderColor: '#facc15',
      backgroundColor: 'rgba(250, 204, 21, 0.25)',
      borderWidth: 3,
      tension: 0.4,
      pointBackgroundColor: '#facc15',
      pointBorderColor: '#000',
      pointRadius: 6,
      pointHoverRadius: 8,
      fill: true,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    layout: { padding: 10 },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { color: '#000', font: { size: 13 } },
        grid: { color: '#fce570' }
      },
      x: {
        ticks: { color: '#000', font: { size: 13 } },
        grid: { display: false }
      }
    },
    animation: {
      duration: 1400,
      easing: 'easeOutQuart'
    }
  }
});
</script>

<?php require 'includes/footer.php'; ?>
