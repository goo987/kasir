<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$title = 'Tambah Pelanggan';
require '../includes/header.php';

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);

    if ($nama !== '') {
        $stmt = $pdo->prepare("INSERT INTO pelanggan (nama, alamat, telepon) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $alamat, $telepon]);

        echo "<script>
            alert('Pelanggan berhasil ditambahkan!');
            window.location.href = 'pelanggan.php';
        </script>";
        exit;
    } else {
        $error = 'Nama pelanggan wajib diisi.';
    }
}
?>

<style>
:root {
  --primary: #facc15;      /* kuning utama */
  --primary-dark: #eab308; 
  --text-dark: #000;
  --danger: #e74c3c;
}

.card {
  background: #fff8d0;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
  margin-bottom: 16px;
}

form .mb-3 {
  margin-bottom: 14px;
}

form label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}

form input {
  width: 100%;
  padding: 10px 12px;
  font-size: 16px;
  border: 1.5px solid var(--primary);
  border-radius: 8px;
  outline: none;
  transition: all 0.2s;
}

form input:focus {
  border-color: var(--primary-dark);
  box-shadow: 0 0 0 0.15rem rgba(234, 179, 8, 0.25);
}

/* Tombol Simpan & Batal */
.btn {
  display: inline-block;
  background: var(--primary);
  color: var(--text-dark);
  font-weight: 700;
  font-size: 16px;
  border: 2px solid var(--text-dark);
  border-radius: 8px;
  padding: 10px 20px;
  text-decoration: none;
  box-shadow: 3px 3px 0 #000;
  transition: all 0.2s ease;
  cursor: pointer;
  text-align: center;
}

.btn:hover {
  background: var(--primary-dark);
  transform: translate(-2px,-2px);
}

/* Error box */
.error-box {
  border-left: 4px solid var(--danger);
  background: #ffe6e1;
  padding: 10px 12px;
  border-radius: 8px;
  margin-bottom: 12px;
}
</style>

<h2>Tambah Pelanggan Baru</h2>

<?php if (!empty($error)): ?>
  <div class="error-box">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<div class="card">
  <form method="post">
    <div class="mb-3">
      <label>Nama</label>
      <input type="text" name="nama" required>
    </div>
    <div class="mb-3">
      <label>Alamat</label>
      <input type="text" name="alamat">
    </div>
    <div class="mb-3">
      <label>Telepon</label>
      <input type="text" name="telepon" 
             oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
             maxlength="15" />
    </div>
    <div style="display:flex; gap:10px;">
      <button type="submit" class="btn">Simpan</button>
      <a href="pelanggan.php" class="btn">Batal</a>
    </div>
  </form>
</div>

<?php require '../includes/footer.php'; ?>
