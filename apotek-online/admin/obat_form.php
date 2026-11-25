<?php require '../config.php'; need('admin');
$id = (int)($_GET['id'] ?? 0); $o = [];
if ($id) { $s = $db->prepare('SELECT * FROM obat WHERE id=?'); $s->execute([$id]); $o = $s->fetch() ?: exit('Data tidak ditemukan.'); }
$judul = $id ? 'Ubah Obat' : 'Tambah Obat';
$f = ['kode','nama','kategori_id','golongan','kualitas','satuan','kegunaan','dosis','aturan_pakai','efek_samping','produsen','harga_beli','harga_jual','stok_min','no_batch','kadaluarsa'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $v = []; foreach ($f as $k) { $x = trim($_POST[$k] ?? ''); $v[$k] = $x === '' ? null : $x; }
  foreach (['harga_beli','harga_jual','stok_min'] as $k) $v[$k] = (int)$v[$k];
  if (!$v['nama'] || !$v['kode'] || $v['harga_jual'] <= 0) flash('Kode, nama, dan harga jual wajib diisi.', 'err');
  else {
    $g = upload('gambar', 'obat');
    try {
      if ($id) {
        $set = implode(',', array_map(fn($k) => "$k=?", $f)); $p = array_values($v);
        if ($g) { $set .= ',gambar=?'; $p[] = $g; }
        $p[] = $id; $db->prepare("UPDATE obat SET $set WHERE id=?")->execute($p);
      } else {
        $cols = array_merge($f, ['stok', 'gambar']); $p = array_values($v); $p[] = max(0, (int)($_POST['stok'] ?? 0)); $p[] = $g;
        $db->prepare('INSERT INTO obat(' . implode(',', $cols) . ') VALUES(' . implode(',', array_fill(0, count($cols), '?')) . ')')->execute($p);
      }
      logact(($id ? 'Ubah' : 'Tambah') . ' obat ' . $v['nama']); flash('Data obat disimpan.'); header('Location: obat.php'); exit;
    } catch (PDOException $x) { flash('Gagal menyimpan. Kode obat mungkin sudah dipakai.', 'err'); }
  }
  $o = array_merge($o, $_POST);
}
$kats = $db->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();
$val = fn($k) => e($o[$k] ?? '');
include '../includes/header.php'; ?>
<h1><?= $judul ?></h1>
<form method="post" enctype="multipart/form-data" class="panel"><?= csrf() ?>
<div class="cols">
  <div><label>Kode obat</label><input name="kode" value="<?= $val('kode') ?>" required></div>
  <div><label>Nama obat</label><input name="nama" value="<?= $val('nama') ?>" required></div>
  <div><label>Kategori</label><select name="kategori_id"><option value="">Tanpa kategori</option>
    <?php foreach ($kats as $c): ?><option value="<?= $c['id'] ?>" <?= ($o['kategori_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nama']) ?></option><?php endforeach; ?></select></div>
  <div><label>Golongan</label><select name="golongan">
    <?php foreach (['bebas' => 'Obat Bebas', 'bebas_terbatas' => 'Obat Bebas Terbatas', 'keras' => 'Obat Keras'] as $k => $l): ?><option value="<?= $k ?>" <?= ($o['golongan'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
  <div><label>Kualitas (Original, Generik, dll.)</label><input name="kualitas" value="<?= $val('kualitas') ?>"></div>
  <div><label>Satuan (strip, botol, box)</label><input name="satuan" value="<?= $val('satuan') ?: 'strip' ?>"></div>
  <div><label>Produsen</label><input name="produsen" value="<?= $val('produsen') ?>"></div>
  <div><label>Harga beli (Rp)</label><input type="number" name="harga_beli" min="0" value="<?= $val('harga_beli') ?>"></div>
  <div><label>Harga jual (Rp)</label><input type="number" name="harga_jual" min="1" value="<?= $val('harga_jual') ?>" required></div>
  <?php if (!$id): ?><div><label>Stok awal</label><input type="number" name="stok" min="0" value="<?= $val('stok') ?: 0 ?>"></div><?php endif; ?>
  <div><label>Stok minimum (peringatan)</label><input type="number" name="stok_min" min="0" value="<?= $val('stok_min') ?: 10 ?>"></div>
  <div><label>Nomor batch</label><input name="no_batch" value="<?= $val('no_batch') ?>"></div>
  <div><label>Tanggal kadaluarsa</label><input type="date" name="kadaluarsa" value="<?= $val('kadaluarsa') ?>"></div>
  <div><label>Gambar (JPG/PNG/WEBP, maks. 2 MB)</label><input type="file" name="gambar" accept="image/*"></div>
</div>
<label>Kegunaan</label><textarea name="kegunaan" rows="2" style="width:100%"><?= $val('kegunaan') ?></textarea>
<label>Dosis</label><input name="dosis" style="width:100%" value="<?= $val('dosis') ?>">
<label>Aturan pakai</label><textarea name="aturan_pakai" rows="2" style="width:100%"><?= $val('aturan_pakai') ?></textarea>
<label>Efek samping</label><textarea name="efek_samping" rows="2" style="width:100%"><?= $val('efek_samping') ?></textarea>
<?php if ($id): ?><p class="mut">Untuk menambah stok, gunakan menu Stok &amp; Pemasok agar tercatat sebagai pembelian.</p><?php endif; ?>
<p><button>Simpan</button> <a class="btn sec" href="obat.php">Batal</a></p>
</form>
<?php include '../includes/footer.php'; ?>
