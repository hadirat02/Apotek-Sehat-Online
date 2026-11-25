<?php require 'config.php'; need('pembeli'); $judul = 'Keranjang';
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$a = $_POST['aksi'] ?? '';
if ($a === 'tambah') {
  $id = (int)$_POST['id']; $q = max(1, (int)($_POST['qty'] ?? 1));
  $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $q;
  flash('Obat ditambahkan ke keranjang.'); header('Location: keranjang.php'); exit;
}
if (isset($_POST['hapus'])) { unset($_SESSION['cart'][(int)$_POST['hapus']]); header('Location: keranjang.php'); exit; }
if ($a === 'ubah') {
  foreach (($_POST['qty'] ?? []) as $id => $q) { if ((int)$q > 0) $_SESSION['cart'][(int)$id] = (int)$q; else unset($_SESSION['cart'][(int)$id]); }
  flash('Keranjang diperbarui.'); header('Location: keranjang.php'); exit;
}
function muat_keranjang($db) {
  $items = []; $total = 0;
  if ($_SESSION['cart']) {
    $in = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $s = $db->prepare("SELECT * FROM obat WHERE aktif=1 AND id IN ($in)"); $s->execute(array_keys($_SESSION['cart']));
    foreach ($s as $o) { $o['qty'] = $_SESSION['cart'][$o['id']]; $items[] = $o; $total += $o['qty'] * $o['harga_jual']; }
  }
  return [$items, $total];
}
[$items, $total] = muat_keranjang($db);
if ($a === 'checkout' && $items) {
  $alamat = trim($_POST['alamat'] ?? ''); $metode = $_POST['metode'] ?? ''; $err = null;
  $perlu = false; foreach ($items as $o) if ($o['golongan'] === 'keras') $perlu = true;
  $resep = upload('resep', 'resep');
  if ($alamat === '') $err = 'Alamat pengiriman wajib diisi.';
  elseif (!in_array($metode, ['transfer', 'cod'])) $err = 'Pilih metode pembayaran.';
  elseif ($perlu && !$resep) $err = 'Pesanan berisi obat keras. Unggah foto resep dokter (JPG/PNG/WEBP, maks. 2 MB).';
  if (!$err) {
    $db->beginTransaction();
    try {
      $kode = 'INV' . date('ymd') . strtoupper(bin2hex(random_bytes(2)));
      $db->prepare('INSERT INTO pesanan(kode,user_id,total,metode,alamat,catatan,resep) VALUES(?,?,0,?,?,?,?)')
         ->execute([$kode, user()['id'], $metode, $alamat, trim($_POST['catatan'] ?? ''), $resep]);
      $pid = $db->lastInsertId(); $tot = 0;
      foreach ($items as $o) {
        $s = $db->prepare('SELECT stok,kadaluarsa FROM obat WHERE id=? FOR UPDATE'); $s->execute([$o['id']]); $r = $s->fetch();
        if ($r['stok'] < $o['qty']) throw new Exception("Stok {$o['nama']} tidak cukup (sisa {$r['stok']}).");
        if ($r['kadaluarsa'] && $r['kadaluarsa'] < date('Y-m-d')) throw new Exception("{$o['nama']} sudah kadaluarsa dan tidak dapat dibeli.");
        $db->prepare('UPDATE obat SET stok=stok-? WHERE id=?')->execute([$o['qty'], $o['id']]);
        $db->prepare('INSERT INTO pesanan_detail(pesanan_id,obat_id,nama,harga,harga_beli,jumlah) VALUES(?,?,?,?,?,?)')
           ->execute([$pid, $o['id'], $o['nama'], $o['harga_jual'], $o['harga_beli'], $o['qty']]);
        $tot += $o['harga_jual'] * $o['qty'];
      }
      $db->prepare('UPDATE pesanan SET total=? WHERE id=?')->execute([$tot, $pid]);
      $db->commit(); $_SESSION['cart'] = []; logact("Checkout $kode");
      flash("Pesanan $kode berhasil dibuat."); header("Location: pesanan.php?id=$pid"); exit;
    } catch (Exception $x) { $db->rollBack(); $err = $x->getMessage(); }
  }
  flash($err, 'err');
}
include 'includes/header.php'; ?>
<h1>Keranjang belanja</h1>
<?php if (!$items): ?><p>Keranjang kosong. <a href="<?= BASE ?>/">Cari obat</a>.</p>
<?php else: $keras = false; ?>
<form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="ubah">
<div class="wrap"><table><tr><th>Obat</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th></th></tr>
<?php foreach ($items as $o): if ($o['golongan'] === 'keras') $keras = true; ?>
<tr><td><?= e($o['nama']) ?><br><?= gol($o['golongan']) ?></td><td><?= rp($o['harga_jual']) ?></td>
<td><input type="number" name="qty[<?= $o['id'] ?>]" value="<?= $o['qty'] ?>" min="0" max="<?= $o['stok'] ?>" style="width:80px"></td>
<td><?= rp($o['qty'] * $o['harga_jual']) ?></td>
<td><button class="btn bad sm" name="hapus" value="<?= $o['id'] ?>">Hapus</button></td></tr>
<?php endforeach; ?>
<tr><th colspan="3">Total</th><th colspan="2"><?= rp($total) ?></th></tr></table></div>
<button class="btn sec">Perbarui jumlah</button>
</form>
<h2>Data pengiriman</h2>
<form method="post" enctype="multipart/form-data" class="panel"><?= csrf() ?><input type="hidden" name="aksi" value="checkout">
  <label>Alamat pengiriman</label><textarea name="alamat" rows="2" required style="width:100%"><?= e($_POST['alamat'] ?? '') ?></textarea>
  <label>Metode pembayaran</label>
  <select name="metode" required><option value="">Pilih</option><option value="transfer">Transfer bank</option><option value="cod">Bayar di tempat (COD)</option></select>
  <label>Catatan (opsional)</label><input name="catatan" style="width:100%">
  <?php if ($keras): ?><label>Foto resep dokter (wajib, karena ada obat keras)</label><input type="file" name="resep" accept="image/*"><?php endif; ?>
  <p><button>Buat pesanan</button></p>
</form>
<?php endif; include 'includes/footer.php'; ?>
