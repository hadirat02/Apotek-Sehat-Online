<?php require 'config.php'; $judul = 'Masuk';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $s = $db->prepare('SELECT * FROM users WHERE email=?'); $s->execute([trim($_POST['email'] ?? '')]); $u = $s->fetch();
  if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama'], 'role' => $u['role']];
    logact('Login'); header('Location: ' . BASE . ($u['role'] === 'admin' ? '/admin/' : '/')); exit;
  }
  flash('Email atau kata sandi salah. Periksa lagi lalu coba masuk.', 'err');
}
include 'includes/header.php'; ?>
<div class="auth panel"><h1>Masuk</h1>
<form method="post"><?= csrf() ?>
  <label>Email</label><input type="email" name="email" required style="width:100%">
  <label>Kata sandi</label><input type="password" name="password" required style="width:100%">
  <p><button>Masuk</button></p>
</form>
<p class="mut">Belum punya akun? <a href="register.php">Daftar sebagai pembeli</a>.</p></div>
<?php include 'includes/footer.php'; ?>
