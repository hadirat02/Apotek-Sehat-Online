<?php
// Pengaturan Apotek Online. Sesuaikan bila perlu.
session_start();
define('APP', 'Apotek Sehat Online');
define('REKENING', 'Bank BRI 0000-00-000000-00-0 a.n. Apotek Sehat'); // ganti dengan rekening apotek
$root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$dir  = str_replace('\\', '/', __DIR__);
define('BASE', stripos($dir, $root) === 0 ? substr($dir, strlen($root)) : '/apotek-online');
try {
  $db = new PDO('mysql:host=localhost;dbname=apotek_online;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $x) {
  exit('Database belum siap. Impor database.sql lewat phpMyAdmin, lalu cek pengaturan koneksi di config.php.');
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rp($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function tgl($d) { return $d ? date('d/m/Y', strtotime($d)) : '-'; }
function user() { return $_SESSION['user'] ?? null; }
function need($role = null) {
  if (!user()) { header('Location: ' . BASE . '/login.php'); exit; }
  if ($role && user()['role'] !== $role) { http_response_code(403); exit('Akses ditolak untuk peran Anda.'); }
}
function flash($m = null, $t = 'ok') {
  if ($m) { $_SESSION['f'] = [$m, $t]; return null; }
  $f = $_SESSION['f'] ?? null; unset($_SESSION['f']); return $f;
}
function csrf() { return '<input type="hidden" name="t" value="' . ($_SESSION['t'] ??= bin2hex(random_bytes(16))) . '">'; }
function logact($a) {
  global $db;
  $db->prepare('INSERT INTO log_aktivitas(user_id,aksi) VALUES(?,?)')->execute([user()['id'] ?? null, $a]);
}
function img($g) { return BASE . ($g ? '/uploads/' . $g : '/assets/no-image.svg'); }
function upload($field, $sub) {
  if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== 0) return null;
  $f = $_FILES[$field];
  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) || $f['size'] > 2 * 1024 * 1024 || !@getimagesize($f['tmp_name'])) {
    flash('Gambar harus JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.', 'err'); return null;
  }
  $n = bin2hex(random_bytes(8)) . '.' . $ext;
  @mkdir(__DIR__ . "/uploads/$sub", 0775, true);
  move_uploaded_file($f['tmp_name'], __DIR__ . "/uploads/$sub/$n");
  return "$sub/$n";
}
function gol($g) {
  $m = ['bebas' => 'Obat Bebas', 'bebas_terbatas' => 'Obat Bebas Terbatas', 'keras' => 'Obat Keras (wajib resep)'];
  return '<span class="gol gol-' . e($g) . '">' . $m[$g] . '</span>';
}
// Status kadaluarsa: [label, kelas, sudah_kadaluarsa]
function kad($d) {
  if (!$d) return ['Tanpa tanggal', 'ok', false];
  $h = (strtotime($d) - strtotime(date('Y-m-d'))) / 86400;
  if ($h < 0) return ['Kadaluarsa', 'bad', true];
  if ($h <= 90) return ['Hampir kadaluarsa', 'warn', false];
  return ['Aman', 'ok', false];
}
function batal_pesanan($pid) {
  global $db;
  $db->beginTransaction();
  $s = $db->prepare('SELECT status FROM pesanan WHERE id=? FOR UPDATE'); $s->execute([$pid]);
  $st = $s->fetchColumn();
  if ($st && !in_array($st, ['batal', 'selesai'])) {
    $db->prepare('UPDATE obat o JOIN pesanan_detail d ON d.obat_id=o.id SET o.stok=o.stok+d.jumlah WHERE d.pesanan_id=?')->execute([$pid]);
    $db->prepare("UPDATE pesanan SET status='batal' WHERE id=?")->execute([$pid]);
  }
  $db->commit();
}
// Semua form POST wajib membawa token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['t'] ?? '', $_POST['t'] ?? '')) {
  exit('Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.');
}
