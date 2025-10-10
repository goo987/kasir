<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$title = 'Data Pelanggan';
require '../includes/header.php';

// ===== Hapus pelanggan =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?")->execute([$id]);
    echo "<script>
        alert('Pelanggan berhasil dihapus!');
        window.location.href = 'pelanggan.php';
    </script>";
    exit;
}

// ===== Ambil data pelanggan =====
$stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
$pelanggan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
:root {
  --primary: #facc15;
  --primary-dark: #eab308;
  --danger: #d64545;
  --text-dark: #000;
  --text-light: #fff;
}

/* Tombol */
.btn {
  font-weight: 600;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  display: inline-block;
  transition: all 0.2s ease;
}
.btn-black {
  background: var(--text-light);
  color: var(--text-dark);
  border: 2px solid var(--text-dark);
  box-shadow: 2px 2px 0 rgba(0,0,0,0.3);
}
.btn-black:hover {
  background: var(--text-dark);
  color: var(--text-light);
  transform: translate(-2px,-2px);
}
.btn-danger {
  background: var(--danger);
  color: var(--text-light);
  border: none;
  box-shadow: 2px 2px 0 rgba(0,0,0,0.3);
}
.btn-danger:hover {
  background: #b03a3a;
  transform: translate(-2px,-2px);
}

/* Tabel card-like modern */
.table-wrapper {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.card-row {
  display: grid;
  grid-template-columns: 50px 1fr 1fr 1fr 150px;
  gap: 12px;
  padding: 12px;
  background: #fff8d0;
  border-radius: 12px;
  box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
  align-items: center;
  transition: transform 0.2s, box-shadow 0.2s;
}
.card-row:hover {
  transform: translateY(-2px);
  box-shadow: 4px 4px 10px rgba(0,0,0,0.15);
}
.card-header {
  font-weight: 700;
  background: linear-gradient(90deg, var(--primary), var(--primary-dark));
  padding: 12px;
  border-radius: 12px;
  color: var(--text-dark);
  display: grid;
  grid-template-columns: 50px 1fr 1fr 1fr 150px;
}
.card-header span {
  font-weight: 700;
}

/* Responsive */
@media (max-width: 768px) {
  .card-row, .card-header {
    grid-template-columns: 1fr;
    gap: 6px;
    padding: 10px;
  }
  .card-row div, .card-header span {
    display: flex;
    justify-content: space-between;
  }
}
</style>

<h2>Daftar Pelanggan Member</h2>
<div class="mb-3">
  <a href="tambah_pelanggan.php" class="btn btn-black">+ Tambah Pelanggan</a>
</div>

<?php if (empty($pelanggan)): ?>
  <p class="small">Belum ada data pelanggan.</p>
<?php else: ?>
  <div class="table-wrapper">
    <div class="card-header">
      <span>ID</span>
      <span>Nama</span>
      <span>Alamat</span>
      <span>Telepon</span>
      <span>Aksi</span>
    </div>
    <?php foreach ($pelanggan as $p): ?>
      <div class="card-row">
        <div><?= $p['id_pelanggan'] ?></div>
        <div><?= htmlspecialchars($p['nama']) ?></div>
        <div><?= htmlspecialchars($p['alamat']) ?></div>
        <div><?= htmlspecialchars($p['telepon']) ?></div>
        <div>
          <a href="?delete=<?= $p['id_pelanggan'] ?>" 
             class="btn btn-danger" 
             onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">
             Hapus
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
