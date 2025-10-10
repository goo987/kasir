<?php
require_once __DIR__ . '/../config.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM barang WHERE id_barang = ?');
$stmt->execute([$id]);
$b = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$b) {
    header('Location: barang.php');
    exit;
}

$title = 'Edit Barang';
require '../includes/header.php';
?>

<style>
    .edit-box {
        max-width: 550px;
        margin: 0 auto;
        background: linear-gradient(145deg, #ffffff, #fff8d0);
        padding: 25px 30px;
        border-radius: 16px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
        border: 2px solid #facc15;
        animation: fadeIn 0.5s ease;
    }

    .edit-box h3 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 25px;
        color: #000;
    }

    .edit-box label {
        font-weight: 600;
        color: #222;
        margin-bottom: 5px;
    }

    .edit-box input.form-control {
        height: 46px;
        font-size: 16px;
        border: 1.8px solid #facc15;
        box-shadow: none;
        transition: all 0.2s ease;
    }

    .edit-box input.form-control:focus {
        border-color: #eab308;
        box-shadow: 0 0 0 0.15rem rgba(234, 179, 8, 0.25);
    }

    .edit-box .btn-group {
        display: flex;
        gap: 12px;
        margin-top: 20px; /* 🔹 Jarak tombol dari kolom stok */
    }

    .edit-box button.btn-update {
        background-color: #facc15;
        border: none;
        color: #000;
        font-weight: 700;
        flex: 1;
        padding: 12px;
        font-size: 16px;
        border-radius: 10px;
        transition: 0.3s ease;
    }

    .edit-box button.btn-update:hover {
        background-color: #eab308;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .edit-box a.btn-cancel {
        background-color: #fff;
        border: 2px solid #facc15;
        color: #000;
        font-weight: 700;
        flex: 1;
        text-align: center;
        padding: 12px;
        font-size: 16px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-block;
        transition: 0.3s ease;
    }

    .edit-box a.btn-cancel:hover {
        background-color: #fef3c7;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="edit-box mt-4">
    <h3>Edit Barang</h3>
    <form method="post" action="barang.php">
        <input type="hidden" name="save_barang" value="1"/>
        <input type="hidden" name="id" value="<?= $b['id_barang'] ?>"/>

        <div class="mb-3">
            <label>Kode Barang</label>
            <input name="kode" class="form-control" value="<?= htmlspecialchars($b['kode_barang']) ?>" required/>
        </div>

        <div class="mb-3">
            <label>Nama Barang</label>
            <input name="nama" class="form-control" value="<?= htmlspecialchars($b['nama_barang']) ?>" required/>
        </div>

        <div class="mb-3">
            <label>Harga</label>
            <input name="harga" type="number" step="0.01" class="form-control" value="<?= $b['harga'] ?>" required/>
        </div>

        <div class="mb-4">
            <label>Stok</label>
            <input name="stok" type="number" class="form-control" value="<?= $b['stok'] ?>" required/>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-update">Update Barang</button>
            <a href="barang.php" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

<?php require '../includes/footer.php'; ?>
