<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$title = 'Manajemen Barang';

// ===== LOGIKA SIMPAN / NONAKTIFKAN SEBELUM HEADER =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_barang'])) {
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    if (!empty($_POST['id'])) {
        // update barang yang ada
        $stmt = $pdo->prepare('UPDATE barang SET kode_barang=?, nama_barang=?, harga=?, stok=? WHERE id_barang=?');
        $stmt->execute([$kode, $nama, $harga, $stok, $_POST['id']]);
    } else {
        // cek apakah ada barang nonaktif dengan kode sama
        $cek = $pdo->prepare("SELECT id_barang FROM barang WHERE kode_barang = ? AND is_active = 0 LIMIT 1");
        $cek->execute([$kode]);
        $lama = $cek->fetch(PDO::FETCH_ASSOC);

        if ($lama) {
            // aktifkan ulang + update datanya
            $stmt = $pdo->prepare("UPDATE barang SET nama_barang=?, harga=?, stok=?, is_active=1 WHERE id_barang=?");
            $stmt->execute([$nama, $harga, $stok, $lama['id_barang']]);
        } else {
            // buat barang baru
            $stmt = $pdo->prepare("INSERT INTO barang (kode_barang, nama_barang, harga, stok, is_active) VALUES (?,?,?,?,1)");
            $stmt->execute([$kode, $nama, $harga, $stok]);
        }
    }

    header('Location: barang.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // nonaktifkan barang agar transaksi lama tetap aman
    $pdo->prepare('UPDATE barang SET is_active = 0 WHERE id_barang = ?')->execute([$id]);
    header('Location: barang.php');
    exit;
}

// ===== QUERY DATA HANYA BARANG AKTIF =====
$barang = $pdo->query('SELECT * FROM barang WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);

// ===== INCLUDE HEADER =====
require '../includes/header.php';
?>

<!-- CSS -->
<style>
    .btn-warning,
    .btn-primary {
        color: #000 !important;
        font-weight: 600;
    }

    /* Form Tambah Barang - diperbesar sedikit */
    form input.form-control {
        height: 46px; /* sedikit lebih tinggi */
        font-size: 16px;
        padding: 10px 12px;
    }

    form button.btn-primary {
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 8px;
    }

    form button.btn-primary:hover {
        background-color: #ffea00;
        color: #000;
        border-color: #ffea00;
    }
</style>

<h3>Barang</h3>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($barang as $b): ?>
            <tr>
                <td><?=$b['id_barang']?></td>
                <td><?=htmlspecialchars($b['kode_barang'])?></td>
                <td><?=htmlspecialchars($b['nama_barang'])?></td>
                <td><?=number_format($b['harga'], 2, ",", ".")?></td>
                <td><?=$b['stok']?></td>
                <td>
                    <a href="barang_edit.php?id=<?=$b['id_barang']?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete=<?=$b['id_barang']?>" class="btn btn-sm btn-danger" onclick="return confirm('Nonaktifkan barang ini?')">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<hr/>
<h4>Tambah Barang</h4>
<form method="post">
    <input type="hidden" name="save_barang" value="1"/>
    <div class="mb-3">
        <label>Kode</label>
        <input name="kode" class="form-control" required/>
    </div>
    <div class="mb-3">
        <label>Nama</label>
        <input name="nama" class="form-control" required/>
    </div>
    <div class="mb-3">
        <label>Harga</label>
        <input name="harga" type="number" step="0.01" class="form-control" required/>
    </div>
    <div class="mb-3">
        <label>Stok</label>
        <input name="stok" type="number" class="form-control" required/>
    </div>
    <button class="btn btn-primary">Simpan</button>
</form>

<?php require '../includes/footer.php'; ?>
