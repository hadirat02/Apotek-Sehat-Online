<?php require 'config.php';
$id = (int)($_GET['id'] ?? 0);

/* ===== UPLOAD GAMBAR (khusus admin) ===== */
$admin = (($_SESSION['user']['role'] ?? '') === 'admin');
$pesan = '';
if ($admin && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'gambar') {
  csrf_check(); // <- ganti dengan nama fungsi cek CSRF di config.php
  $f = $_FILES['gambar'] ?? null;
  if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
    $pesan = 'Upload gagal (kode ' . ($f['error'] ?? '?') . '). Pastikan file dipilih dan tidak terlalu besar.';
  } elseif ($f['size'] > 2 * 1024 * 1024) {
    $pesan = 'Ukuran gambar maksimal 2 MB.';
  } else {
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) {
      $pesan = 'Format harus JPG, PNG, atau WEBP.';
    } else {
      $dir = __DIR__ . '/uploads/obat/';
      if (!is_dir($dir)) mkdir($dir, 0775, true);
      $nama = 'obat-' . $id . '-' . time() . '.' . $ext;
      if (move_uploaded_file($f['tmp_name'], $dir . $nama)) {
        $lama = $db->prepare('SELECT gambar FROM obat WHERE id=?');
        $lama->execute([$id]); $lama = $lama->fetchColumn();
        $db->prepare('UPDATE obat SET gambar=? WHERE id=?')->execute(['obat/' . $nama, $id]);
        // hapus file lama hanya jika hasil upload sebelumnya (bukan sample)
        if ($lama && strpos($lama, 'obat/') === 0 && is_file(__DIR__ . '/uploads/' . $lama)) {
          @unlink(__DIR__ . '/uploads/' . $lama);
        }
        header('Location: obat.php?id=' . $id . '&ok=1'); exit;
      } else {
        $pesan = 'Gagal menyimpan file. Cek izin folder uploads/obat.';
      }
    }
  }
}
/* ======================================== */

$s = $db->prepare('SELECT o.*, k.nama kat, (SELECT COALESCE(SUM(d.jumlah),0) FROM pesanan_detail d JOIN pesanan p ON p.id=d.pesanan_id WHERE d.obat_id=o.id AND p.status<>"batal") terjual
  FROM obat o LEFT JOIN kategori k ON k.id=o.kategori_id WHERE o.id=? AND o.aktif=1');
$s->execute([$id]); $o = $s->fetch();
if (!$o) { http_response_code(404); exit('Obat tidak ditemukan.'); }
$judul = $o['nama']; $ke = kad($o['kadaluarsa']); $ada = $o['stok'] > 0 && !$ke[2];
include 'includes/header.php'; ?>
<p><a href="<?= BASE ?>/">&larr; Kembali ke katalog</a></p>
<div class="detail">
  <div>
    <img src="<?= img($o['gambar']) ?>" alt="<?= e($o['nama']) ?>">
    <?php if ($admin): ?>
      <?php if (isset($_GET['ok'])): ?><p class="msg ok">Gambar berhasil diperbarui.</p><?php endif; ?>
      <?php if ($pesan): ?><p class="msg err"><?= e($pesan) ?></p><?php endif; ?>
      <form method="post" enctype="multipart/form-data" class="bar"><?= csrf() ?>
        <input type="hidden" name="aksi" value="gambar">
        <input type="file" name="gambar" accept="image/jpeg,image/png,image/webp" required>
        <button>Ganti gambar</button>
      </form>
    <?php endif; ?>
  </div>
  <div>
    <h1><?= e($o['nama']) ?></h1>
    <!-- bagian ini sama seperti kode aslimu, tidak diubah -->