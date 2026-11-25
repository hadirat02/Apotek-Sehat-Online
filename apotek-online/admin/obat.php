<?php require '../config.php'; need('admin'); $judul = 'Kelola Obat';
if (isset($_POST['toggle'])) {
  $db->prepare('UPDATE obat SET aktif=1-aktif WHERE id=?')->execute([(int)$_POST['toggle']]);
  logact('Ubah status aktif obat #' . (int)$_POST['toggle']); flash('Status obat diubah.'); header('Location: obat.php'); exit;
}
$rows = $db->query('SELECT o.*, k.nama kat, (SELECT COALESCE(SUM(d.jumlah),0) FROM pesanan_detail d JOIN pesanan p ON p.id=d.pesanan_id WHERE d.obat_id=o.id AND p.status<>"batal") terjual
  FROM obat o LEFT JOIN kategori k ON k.id=o.kategori_id ORDER BY o.aktif DESC, o.nama')->fetchAll();
include '../includes/header.php'; ?>
<h1>Kelola Obat</h1>
<p><a class="btn" href="obat_form.php">Tambah obat</a></p>
<div class="wrap"><table><tr><th></th><th>Obat</th><th>Golongan</th><th>Harga beli / jual</th><th>Stok</th><th>Terjual</th><th>Kadaluarsa</th><th></th></tr>
<?php foreach ($rows as $o): $k = kad($o['kadaluarsa']); ?>
<tr style="<?= $o['aktif'] ? '' : 'opacity:.5' ?>"><td><img src="<?= img($o['gambar']) ?>" alt=""></td>
<td><b><?= e($o['nama']) ?></b><br><span class="mut"><?= e($o['kode']) ?> &middot; <?= e($o['kat'] ?: 'Umum') ?></span></td>
<td><?= gol($o['golongan']) ?></td><td><?= rp($o['harga_beli']) ?><br><?= rp($o['harga_jual']) ?></td>
<td><span class="tag <?= $o['stok'] <= $o['stok_min'] ? 'bad' : 'ok' ?>"><?= $o['stok'] ?></span></td><td><?= $o['terjual'] ?></td>
<td><?= tgl($o['kadaluarsa']) ?><br><span class="tag <?= $k[1] ?>"><?= $k[0] ?></span></td>
<td><a class="btn sec sm" href="obat_form.php?id=<?= $o['id'] ?>">Ubah</a>
<form method="post" style="display:inline"><?= csrf() ?><button class="btn sm <?= $o['aktif'] ? 'bad' : '' ?>" name="toggle" value="<?= $o['id'] ?>"><?= $o['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form></td></tr>
<?php endforeach; ?></table></div>
<?php include '../includes/footer.php'; ?>
