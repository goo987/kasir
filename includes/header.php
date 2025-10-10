<?php
if (!isset($title)) $title = 'Kasir App';
$currentPage = basename($_SERVER['PHP_SELF']); // ambil nama file aktif
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    /* -- Global -- */
    :root{
      --bg:#f4f7fb;
      --card:#ffffff;
      --muted:#6b7280;
      --accent:#FFD700;
      --accent-2:#FFC107;
      --success:#2ea44f;
      --danger:#d64545;
      --radius:10px;
    }
    *{box-sizing:border-box}
    body{
      margin:0; font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background:var(--bg); color:#222;
    }
    .site-header{
      background: linear-gradient(90deg,var(--accent),var(--accent-2));
      padding:12px 18px;
      box-shadow:0 2px 6px rgba(0,0,0,0.06);
    }
    .site-header .container{
      max-width:1100px;margin:0 auto;
      display:flex;align-items:center;justify-content:space-between;
    }
    .brand{
      font-weight:900;
      font-style:italic;
      text-transform:uppercase;
      letter-spacing:0.5px;
      font-size:22px;
      color:#000 !important;
      text-shadow:3px 3px 0 #FFD700;
    }
    .nav{ display:flex; gap:14px; align-items:center; }
    .nav a{
      display:inline-block;
      background:#fff;
      color:#000;
      border:2px solid #000;
      border-radius:10px;
      padding:8px 14px;
      font-weight:700;
      font-size:14px;
      text-decoration:none;
      box-shadow:3px 3px 0 #000;
      transition: all 0.2s ease;
    }
    .nav a:hover{
      background:#ffe680;
      color:#000;
      box-shadow:5px 5px 0 #000;
      transform:translate(-3px,-3px);
    }
    .nav a:active{
      transform:translate(2px,2px);
      box-shadow:1px 1px 0 #000;
    }
    .container-main{
      max-width:1100px; margin:22px auto; padding:18px;
      background:var(--card); border-radius:var(--radius);
      box-shadow:0 6px 18px rgba(16,24,40,0.04);
    }
    .card{ background:var(--card); border-radius:8px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); }
    h1,h2,h3{ margin:0 0 12px 0; color:#1f2937; }
    label{ display:block; font-weight:600; margin-bottom:6px; color:#374151; }
    input[type="text"], input[type="number"], input[type="date"], input[type="password"], select{
      width:100%; padding:10px 12px; border:1px solid #e6eef4; border-radius:8px; background:#fff;
      font-size:14px; color:#111827;
    }
    button, .btn {
      display:inline-block; padding:10px 14px; border-radius:8px; cursor:pointer; border:none; font-weight:600;
      background:var(--accent); color:white; box-shadow:0 2px 6px rgba(16,24,40,0.08);
    }
    .btn-danger{ background:var(--danger); }
    .muted{ color:var(--muted); font-size:13px; }
    table{ width:100%; border-collapse:collapse; margin-top:12px; }
    th,td{ padding:10px 12px; border-bottom:1px solid #eef3f6; text-align:left; }
    th{ background:#fbfdff; font-weight:700; color:#0f1724; }
    tr:hover td{ background:#fbfeff; }
    .row{ display:flex; gap:18px; align-items:flex-start; }
    .col{ flex:1; }
    .col-6{ flex:0 0 48%; }
    .text-right{ text-align:right; }
    .mb-2{ margin-bottom:8px; }
    .mb-3{ margin-bottom:12px; }
    .mt-3{ margin-top:12px; }
    .small{ font-size:13px; color:var(--muted); }
    @media (max-width:900px){
      .row{ flex-direction:column; }
      .col-6{ flex:1; }
      .site-header .container{ padding:8px }
    }
  </style>
</head>
<body>

<?php 
if (!in_array($currentPage, ['nota.php','riwayat_cetak.php','laporan_cetak.php'])): ?> 
<header class="site-header">
  <div class="container">
    <div class="brand">
      <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['role']) : "Website Kasir" ?>
    </div>
    <nav class="nav">
      <?php if (isset($_SESSION['user'])): ?>
        <a href="/kasir/dashboard.php">Dashboard</a>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
          <a href="/kasir/admin/barang.php">Barang</a>
          <a href="/kasir/admin/users.php">Users</a>
          <a href="/kasir/admin/laporan.php">Laporan</a>
          <a href="/kasir/admin/pelanggan.php">Pelanggan</a>
        <?php else: ?>
          <a href="/kasir/kasir/transaksi.php">Transaksi</a>
          <a href="/kasir/kasir/riwayat.php">Riwayat</a>
        <?php endif; ?>
        <a href="/kasir/logout.php">Logout (<?=htmlspecialchars($_SESSION['user']['username'])?>)</a>
      <?php else: ?>
        <?php if ($currentPage !== 'index.php'): ?>
          <a href="/kasir/index.php">Login</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php endif; ?>

<?php if ($currentPage !== 'index.php'): ?>
<main class="container-main">
<?php endif; ?>
