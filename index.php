<?php
require 'config.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$u]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $ok = false;
        if (function_exists('password_verify') &&
            (strpos($user['password'], '$2y$') === 0 || strpos($user['password'], '$argon2') === 0)) {
            $ok = password_verify($p, $user['password']);
        } else {
            $ok = ($p === $user['password']);
        }

        if ($ok) {
            unset($user['password']);
            $_SESSION['user'] = $user;
            header('Location: dashboard.php');
            exit;
        }
    }

    $err = 'Login gagal. Periksa username / password.';
}

$title = 'Login - Kasir';
require 'includes/header.php';
?>

<style>
  :root {
    --blue: #0077ff;
    --red: #e63946;
    --yellow: #FFD700;
    --white: #fff;
    --black: #000;
  }

  html, body {
    height: 100%;
    margin: 0;
    font-family: 'Inter', Arial, sans-serif;
    background: linear-gradient(-45deg, #fffbe6, #fff, #fffaaf, #fffbe6);
    background-size: 300% 300%;
    animation: gradientShift 8s ease infinite;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  .login-box {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(6px);
    border: 4px solid var(--black);
    border-radius: 14px;
    padding: 40px 36px;
    width: 370px;
    text-align: center;
    box-shadow: 10px 10px 0 var(--yellow);
    transition: all 0.25s ease;
  }

  .login-box:hover {
    transform: translate(-3px, -3px);
    box-shadow: 12px 12px 0 var(--black);
  }

  .login-box h2 {
    font-weight: 900;
    text-transform: uppercase;
    font-size: 28px;
    color: var(--black);
    text-shadow: 2px 2px 0 var(--yellow);
    margin-bottom: 25px;
  }

  .form-group {
    margin-bottom: 18px;
    text-align: left;
  }

  label {
    display: block;
    font-weight: 700;
    margin-bottom: 6px;
    color: var(--black);
  }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: 3px solid var(--black);
    border-radius: 10px;
    font-size: 14px;
    color: var(--black);
    background: var(--white);
    box-shadow: 3px 3px 0 var(--yellow);
    transition: all 0.2s ease;
  }

  input[type="text"]:focus,
  input[type="password"]:focus {
    outline: none;
    transform: translate(-2px, -2px);
    box-shadow: 5px 5px 0 var(--black);
  }

  .btn {
    width: 100%;
    padding: 12px 16px;
    border: 3px solid var(--black);
    border-radius: 10px;
    background: var(--yellow);
    color: var(--black);
    font-weight: 900;
    text-transform: uppercase;
    font-size: 15px;
    letter-spacing: 0.5px;
    cursor: pointer;
    box-shadow: 4px 4px 0 var(--black);
    transition: all 0.2s ease;
  }

  .btn:hover {
    background: #ffec80;
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0 var(--red);
  }

  .alert {
    background: #fff4cc;
    color: var(--red);
    border: 3px solid var(--red);
    padding: 10px;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 4px 4px 0 var(--yellow);
    margin-bottom: 18px;
  }

  footer { display: none !important; }
</style>

<div class="login-box">
  <h2>Login</h2>

  <?php if ($err): ?>
      <div class="alert"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="post">
      <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" required placeholder="Masukkan username">
      </div>
      <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required placeholder="Masukkan password">
      </div>
      <button type="submit" class="btn">Login</button>
  </form>
</div>

<?php require 'includes/footer.php'; ?>
