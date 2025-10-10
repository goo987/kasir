<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$title = 'Manajemen Users';
require '../includes/header.php';

// ==== Pastikan kolom is_active ada ====
$pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1");

// ==== Tambah user ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    $role = $_POST['role'];

    if ($u && $p) {
        $stmt = $pdo->prepare('INSERT INTO users (username,password,role,is_active) VALUES (?,?,?,1)');
        $stmt->execute([$u, $p, $role]);
        echo "<script>alert('User $u berhasil ditambahkan!');window.location='users.php';</script>";
        exit;
    }
}

// ==== Nonaktifkan / Hapus / Aktifkan ====
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    // ambil username untuk pesan alert
    $getUser = $pdo->prepare("SELECT username FROM users WHERE id_user=?");
    $getUser->execute([$id]);
    $username = $getUser->fetchColumn() ?? 'User';

    if ($action === 'delete') {
        // Cek apakah user punya transaksi
        $cek = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE id_user = ?");
        $cek->execute([$id]);
        $hasTransaksi = $cek->fetchColumn() > 0;

        if ($hasTransaksi) {
            // Nonaktifkan user
            $pdo->prepare("UPDATE users SET is_active = 0 WHERE id_user = ?")->execute([$id]);
            echo "<script>alert('User \"$username\" sudah pernah bertransaksi, jadi dinonaktifkan.');window.location='users.php';</script>";
            exit;
        } else {
            // Hapus user
            $pdo->prepare('DELETE FROM users WHERE id_user = ?')->execute([$id]);
            echo "<script>alert('User \"$username\" berhasil dihapus.');window.location='users.php';</script>";
            exit;
        }
    }

    if ($action === 'activate') {
        $pdo->prepare("UPDATE users SET is_active = 1 WHERE id_user = ?")->execute([$id]);
        echo "<script>alert('User \"$username\" berhasil diaktifkan kembali.');window.location='users.php';</script>";
        exit;
    }
}

// ==== Ambil semua user ====
$users = $pdo->query('SELECT id_user, username, role, is_active FROM users ORDER BY id_user ASC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- CSS -->
<style>
    .btn-text-black { color: black !important; }
    .inactive { color: #999; font-style: italic; }
    .status { font-weight: 600; }

    /* Form Tambah User - diperbesar seperti Tambah Barang */
    form input.form-control,
    form select.form-control {
        height: 46px;
        font-size: 16px;
        padding: 10px 12px;
    }

    form button.btn-primary {
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 8px;
        color: #000; /* tulisan hitam */
    }

    form button.btn-primary:hover {
        background-color: #ffea00;
        color: #000;
        border-color: #ffea00;
    }
</style>

<h3>Users</h3>
<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Role</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $row): ?>
      <tr class="<?= $row['is_active'] ? '' : 'inactive' ?>">
        <td><?= $row['id_user'] ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td class="status"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></td>
        <td>
          <?php if ($row['id_user'] != $_SESSION['user']['id_user']): ?>
            <?php if ($row['is_active']): ?>
              <?php
                $cek = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE id_user = ?");
                $cek->execute([$row['id_user']]);
                $hasTransaksi = $cek->fetchColumn() > 0;
              ?>
              <a href="?action=delete&id=<?= $row['id_user'] ?>"
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('<?= $hasTransaksi 
                   ? 'User ini sudah pernah bertransaksi, apakah ingin dinonaktifkan saja?' 
                   : 'Apakah ingin menghapus user ini?' ?>');">
                 Hapus
              </a>
            <?php else: ?>
              <a href="?action=activate&id=<?= $row['id_user'] ?>"
                 class="btn btn-sm btn-primary btn-text-black"
                 onclick="return confirm('Aktifkan kembali user <?= htmlspecialchars($row['username']) ?>?');">
                 Aktifkan
              </a>
            <?php endif; ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<hr/>
<h4>Tambah User</h4>
<form method="post">
  <input type="hidden" name="add_user" value="1"/>
  <div class="mb-3">
    <label>Username</label>
    <input name="username" class="form-control" required/>
  </div>
  <div class="mb-3">
    <label>Password</label>
    <input name="password" class="form-control" required/>
  </div>
  <div class="mb-3">
    <label>Role</label>
    <select name="role" class="form-control">
      <option value="kasir">Kasir</option>
      <option value="admin">Admin</option>
    </select>
  </div>
  <button class="btn btn-primary">Tambah</button>
</form>

<?php require '../includes/footer.php'; ?>
