<?php
require_once __DIR__ . '/../config.php';
require_role('kasir');

// Ambil data barang & pelanggan
$barang_list = $pdo->query('SELECT * FROM barang ORDER BY nama_barang ASC')->fetchAll(PDO::FETCH_ASSOC);
$pelanggan_list = $pdo->query('SELECT * FROM pelanggan ORDER BY nama ASC')->fetchAll(PDO::FETCH_ASSOC);

// Inisialisasi keranjang
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Tambah item ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $idb = (int)$_POST['id_barang'];
    $qty = max(1, (int)$_POST['qty']);
    $st = $pdo->prepare('SELECT * FROM barang WHERE id_barang = ?');
    $st->execute([$idb]);
    $it = $st->fetch(PDO::FETCH_ASSOC);

    if ($it && $qty <= $it['stok']) {
        if (isset($_SESSION['cart'][$idb])) {
            $_SESSION['cart'][$idb]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$idb] = [
                'id'    => $idb,
                'nama'  => $it['nama_barang'],
                'harga' => $it['harga'],
                'qty'   => $qty
            ];
        }
        header('Location: transaksi.php');
        exit;
    } else {
        $msg = "Stok barang '" . htmlspecialchars($it['nama_barang'] ?? 'Unknown') . "' tidak mencukupi.";
    }
}

// Hapus item
if (isset($_GET['remove'])) {
    $r = (int)$_GET['remove'];
    unset($_SESSION['cart'][$r]);
    header('Location: transaksi.php');
    exit;
}

// Checkout
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['cart'])) {
        $msg = 'Keranjang kosong.';
    } else {
        $mode = $_POST['tipe_pelanggan'] ?? 'non_member';
        if ($mode === 'member' && !empty($_POST['id_pelanggan'])) {
            $stmt = $pdo->prepare("SELECT nama FROM pelanggan WHERE id_pelanggan = ?");
            $stmt->execute([$_POST['id_pelanggan']]);
            $pelanggan = $stmt->fetchColumn() ?: 'Member Tidak Dikenal';
        } else {
            $pelanggan = trim($_POST['pelanggan'] ?? '');
            if ($pelanggan === '') $pelanggan = 'Umum';
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO transaksi (tanggal,id_user,pelanggan) VALUES (NOW(),?,?)');
            $stmt->execute([$_SESSION['user']['id_user'], $pelanggan]);
            $idtr = $pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO transaksi_detail (id_transaksi,id_barang,qty,subtotal) VALUES (?,?,?,?)');
            $up  = $pdo->prepare('UPDATE barang SET stok = stok - ? WHERE id_barang = ?');
            foreach ($_SESSION['cart'] as $c) {
                $sub = $c['harga'] * $c['qty'];
                $ins->execute([$idtr, $c['id'], $c['qty'], $sub]);
                $up->execute([$c['qty'], $c['id']]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];

            header("Location: nota.php?id=" . $idtr);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal menyimpan transaksi: ' . $e->getMessage();
        }
    }
}

$title = 'Transaksi';
require '../includes/header.php';
?>

<style>
:root {
  --primary: #facc15;
  --primary-dark: #eab308;
  --text-dark: #000;
  --danger: #e74c3c;
  --bg-card: #fff8d0;
}

body {
  font-family: "Inter", Arial, sans-serif;
}

.card {
  background: var(--bg-card);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 2px 2px 10px rgba(0,0,0,0.08);
  margin-bottom: 16px;
}

h2,h3 { margin-bottom: 12px; }

input, select {
  width: 100%;
  padding: 10px 12px;
  font-size: 15px;
  border: 1.5px solid var(--primary);
  border-radius: 8px;
  margin-bottom: 8px;
  outline: none;
  transition: 0.2s;
}

input:focus, select:focus {
  border-color: var(--primary-dark);
  box-shadow: 0 0 0 0.15rem rgba(234, 179, 8, 0.25);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  font-size: 14px;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px 10px;
  text-align: left;
}

th {
  background: var(--primary);
  color: var(--text-dark);
  font-weight: 700;
}

tr:nth-child(even) td { background: #fff3b0; }

tr:hover td { background: #fff1a8; }

.btn {
  display: inline-block;
  background: var(--primary);
  color: var(--text-dark);
  font-weight: 700;
  font-size: 15px;
  border: 2px solid var(--text-dark);
  border-radius: 8px;
  padding: 10px 18px;
  text-decoration: none;
  box-shadow: 3px 3px 0 #000;
  transition: all 0.2s ease;
  cursor: pointer;
}

.btn:hover {
  background: var(--primary-dark);
  transform: translate(-2px,-2px);
}

.btn-ghost {
  display: inline-block;
  background: transparent;
  border: 2px solid var(--text-dark);
  color: var(--text-dark);
  font-weight: 700;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  box-shadow: 3px 3px 0 #000;
  transition: all 0.2s ease;
}

.btn-ghost:hover {
  background: #facc15;
  color: #000;
  transform: translate(-2px,-2px);
}

.hidden { display: none; }

#search_member { margin-bottom:6px; }

.msg-box {
  border-left:4px solid var(--primary);
  background:#fff7c0;
  padding:10px 12px;
  margin-bottom:12px;
  border-radius:6px;
}
</style>

<h2>Transaksi Penjualan</h2>

<?php if (isset($msg)): ?>
  <div class="msg-box"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div style="display:flex; gap:16px; flex-wrap:wrap;">
  <!-- Tambah Barang -->
  <div style="flex:1 1 45%;">
    <div class="card">
      <h3>Tambah Barang</h3>
      <form method="post">
        <label>Pilih Barang</label>
        <select name="id_barang">
          <?php foreach ($barang_list as $b): ?>
            <option value="<?= $b['id_barang'] ?>" <?= $b['stok'] <= 0 ? 'disabled' : '' ?>>
              <?= htmlspecialchars($b['kode_barang'].' - '.$b['nama_barang'].' (Stok: '.$b['stok'].')') ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Qty</label>
        <input type="number" name="qty" value="1" min="1" />

        <button type="submit" name="add_item" class="btn">Tambah ke Keranjang</button>
      </form>
    </div>
  </div>

  <!-- Keranjang -->
  <div style="flex:1 1 50%;">
    <div class="card">
      <h3>Keranjang</h3>
      <?php if (empty($_SESSION['cart'])): ?>
        <div class="small">Keranjang kosong.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>Nama</th><th>Qty</th><th>Harga</th><th>Subtotal</th><th></th></tr>
          </thead>
          <tbody>
            <?php $total = 0; foreach ($_SESSION['cart'] as $c): 
              $subtotal = $c['qty'] * $c['harga'];
              $total += $subtotal; ?>
              <tr>
                <td><?= htmlspecialchars($c['nama']) ?></td>
                <td><?= $c['qty'] ?></td>
                <td>Rp <?= number_format($c['harga'],0,",",".") ?></td>
                <td>Rp <?= number_format($subtotal,0,",",".") ?></td>
                <td><a href="?remove=<?= $c['id'] ?>" class="btn-ghost">Hapus</a></td>
              </tr>
            <?php endforeach; ?>
            <tr>
              <td colspan="3"><strong>Total</strong></td>
              <td colspan="2"><strong>Rp <?= number_format($total,0,",",".") ?></strong></td>
            </tr>
          </tbody>
        </table>

        <!-- Form Pelanggan -->
        <form method="post" style="margin-top:12px;">
          <label>Pelanggan</label>
          <select name="tipe_pelanggan" id="tipe_pelanggan" onchange="togglePelangganInput()">
            <option value="non_member">Bukan Member</option>
            <option value="member">Member</option>
          </select>

          <div id="input_non_member">
            <input type="text" name="pelanggan" placeholder="Isi nama pelanggan" />
          </div>

          <div id="input_member" class="hidden">
            <input type="text" id="search_member" placeholder="Cari nama member..." onkeyup="filterMember()">
            <select name="id_pelanggan" id="member_select">
              <option value="">Pilih Nama Member</option>
              <?php foreach ($pelanggan_list as $p): ?>
                <option value="<?= $p['id_pelanggan'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" name="checkout" class="btn" style="margin-top:8px;">Checkout</button>
        </form>

        <script>
          function togglePelangganInput() {
            const tipe = document.getElementById('tipe_pelanggan').value;
            document.getElementById('input_member').classList.toggle('hidden', tipe!=='member');
            document.getElementById('input_non_member').classList.toggle('hidden', tipe==='member');
          }

          function filterMember() {
            const filter = document.getElementById('search_member').value.toLowerCase();
            const options = document.getElementById('member_select').options;
            for (let i=0;i<options.length;i++){
              options[i].style.display = options[i].text.toLowerCase().includes(filter)?'':'none';
            }
          }
        </script>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require '../includes/footer.php'; ?>
