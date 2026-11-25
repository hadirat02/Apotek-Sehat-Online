<?php require '../config.php'; need('admin'); $judul = 'Stok & Pemasok';
$a = $_POST['aksi'] ?? '';
if ($a === 'pemasok' && trim($_POST['nama'] ?? '') !== '') {
  $db->prepare('INSERT INTO pemasok(nama,telepon,alamat) VALUES(?,?,?)')->execute([trim($_POST['nama']), trim($_POST['telepon'] ?? ''), trim($_POST['alamat'] ?? '')]);
  logact('Tambah pemasok ' . trim($_POST['nama'])); flash('Pemasok ditambahkan.'); header('Location: stok.php'); exit;
}
if ($a === 'masuk') {
  $oid = (int)$_POST['obat_id']; $j = (int)$_POST['jumlah']; $hb = (int)$_POST['harga_beli'];
  if ($oid && $j > 0) {
    $kd = $_POST['kadaluarsa'] ?: null; $bt = trim($_POST['no_batch'] ?? '') ?: null; $ps = (int)$_POST['pemasok_id'] ?: null;
    $db->beginTransaction();
    $db->prepare('INSERT INTO stok_masuk(obat_id,pemasok_id,jumlah,harga_beli,no_batch,kadaluarsa) VALUES(?,?,?,?,?,?)')->execute([$oid, $ps, $j, $hb, $bt, $kd]);
    $db->prepare('UPDATE obat SET stok=stok+?, harga_beli=?, no_batch=COALESCE(?,no_batch), kadaluarsa=COALESCE(?,kadaluarsa) WHERE id=?')->execute([$j, $hb, $bt, $kd, $oid]);
    $db->commit(); logact("Stok masuk obat #$oid sebanyak $j"); flash('Stok masuk dicatat dan stok bertambah.');
  } else flash('Pilih obat dan isi jumlah lebih dari 0.', 'err');
  header('Location: stok.php'); exit;
}
$obat = $db->query('SELECT id,nama,satuan FROM obat WHERE aktif=1 ORDER BY nama')->fetchAll();
$ps = $db->query('SELECT * FROM pemasok ORDER BY nama')->fetchAll();
$riw = $db->query('SELECT s.*, o.nama obat, p.nama pemasok FROM stok_masuk s JOIN obat o ON o.id=s.obat_id LEFT JOIN pemasok p ON p.id=s.pemasok_id ORDER BY s.id DESC LIMIT 20')->fetchAll();
include '../includes/header.php'; ?>
<h1>Stok masuk &amp; pemasok</h1>
<form method="post" class="panel"><?= csrf() ?><input type="hidden" name="aksi" value="masuk"><h2 style="margin-top:0">Catat pembelian stok</h2>
<div class="cols">
  <div><label>Obat</label><select name="obat_id" required><option value="">Pilih obat</option><?php foreach ($obat as $o): ?><option value="<?= $o['id'] ?>"><?= e($o['nama']) ?></option><?php endforeach; ?></select></div>
  <div><label>Pemasok</label><select name="pemasok_id"><option value="">Tanpa pemasok</option><?php foreach ($ps as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?></select></div>
  <div><label>Jumlah masuk</label><input type="number" name="jumlah" min="1" required></div>
  <div><label>Harga beli per satuan (Rp)</label><input type="number" name="harga_beli" min="0" required></div>
  <div><label>Nomor batch</label><input name="no_batch"></div>
  <div><label>Tanggal kadaluarsa batch</label><input type="date" name="kadaluarsa"></div>
</div><p><button>Simpan stok masuk</button></p></form>
<h2>Riwayat stok masuk (20 terakhir)</h2>
<div class="wrap"><table><tr><th>Tanggal</th><th>Obat</th><th>Pemasok</th><th>Jumlah</th><th>Harga beli</th><th>Batch</th><th>Kadaluarsa</th></tr>
<?php foreach ($riw as $r): ?><tr><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['obat']) ?></td><td><?= e($r['pemasok'] ?: '-') ?></td><td><?= $r['jumlah'] ?></td><td><?= rp($r['harga_beli']) ?></td><td><?= e($r['no_batch'] ?: '-') ?></td><td><?= tgl($r['kadaluarsa']) ?></td></tr><?php endforeach; ?>
<?php if (!$riw): ?><tr><td colspan="7">Belum ada stok masuk.</td></tr><?php endif; ?></table></div>
<h2>Pemasok</h2>
<div class="wrap"><table><tr><th>Nama</th><th>Telepon</th><th>Alamat</th></tr>
<?php foreach ($ps as $p): ?><tr><td><?= e($p['nama']) ?></td><td><?= e($p['telepon']) ?></td><td><?= e($p['alamat']) ?></td></tr><?php endforeach; ?></table></div>
<form method="post" class="panel"><?= csrf() ?><input type="hidden" name="aksi" value="pemasok"><div class="cols">
  <div><label>Nama pemasok</label><input name="nama" required></div><div><label>Telepon</label><input name="telepon"></div><div><label>Alamat</label><input name="alamat"></div>
</div><p><button>Tambah pemasok</button></p></form>
<?php include '../includes/footer.php'; ?>
