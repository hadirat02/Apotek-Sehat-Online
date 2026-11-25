<?php require 'config.php'; $judul = 'Daftar';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = trim($_POST['nama'] ?? ''); $em = trim($_POST['email'] ?? ''); $pw = $_POST['password'] ?? '';
  if ($nama === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) flash('Isi nama dan email yang valid.', 'err');
  elseif (strlen($pw) < 6) flash('Kata sandi minimal 6 karakter.', 'err');
  else {
    try {
      $db->prepare('INSERT INTO users(nama,email,password,role,telepon,alamat) VALUES(?,?,?,"pembeli",?,?)')
         ->execute([$nama, $em, password_hash($pw, PASSWORD_DEFAULT), trim($_POST['telepon'] ?? ''), trim($_POST['alamat'] ?? '')]);
      flash('Akun dibuat. Silakan masuk.'); header('Location: login.php'); exit;
    } catch (PDOException $x) { flash('Email sudah terdaftar. Gunakan email lain atau masuk.', 'err'); }
  }
}
include 'includes/header.php'; ?>
<div class="auth panel"><h1>Daftar pembeli</h1>
<form method="post"><?= csrf() ?>
  <label>Nama lengkap</label><input name="nama" required style="width:100%" value="<?= e($_POST['nama'] ?? '') ?>">
  <label>Email</label><input type="email" name="email" required style="width:100%" value="<?= e($_POST['email'] ?? '') ?>">
  <label>Telepon</label><input name="telepon" style="width:100%" value="<?= e($_POST['telepon'] ?? '') ?>">
  <label>Alamat</label><textarea name="alamat" rows="2" style="width:100%"><?= e($_POST['alamat'] ?? '') ?></textarea>
  <label>Kata sandi (min. 6 karakter)</label><input type="password" name="password" required style="width:100%">
  <p><button>Buat akun</button></p>
</form></div>
<?php include 'includes/footer.php'; ?>
