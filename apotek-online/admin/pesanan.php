<?php require '../config.php'; need('admin'); $judul = 'Kelola Pesanan';
if (isset($_POST['id'])) {
  $id = (int)$_POST['id']; $st = $_POST['status'] ?? '';
  $s = $db->prepare('SELECT status,kode FROM pesanan WHERE id=?'); $s->execute([$id]); $p = $s->fetch();
  if ($p && in_array($st, ['menunggu', 'diproses', 'selesai', 'batal'])) {
    if (in_array($p['status'], ['batal'])) flash('Pesanan yang sudah dibatalkan tidak dapat diubah.', 'err');
    elseif ($st === 'batal') { batal_pesanan($id); logact("Batalkan pesanan {$p['kode']}"); flash('Pesanan dibatalkan dan stok dikembalikan.'); }
    else { $db->prepare('UPDATE pesanan SET status=? WHERE id=?')->execute([$st, $id]); logact("Status {$p['kode']} menjadi $st"); flash('Status pesanan diperbarui.'); }
  }
  header('Location: pesanan.php'); exit;
}
$rows = $db->query('SELECT p.*, u.nama pembeli FROM pesanan p JOIN users u ON u.id=p.user_id ORDER BY p.id DESC')->fetchAll();
include '../includes/header.php'; ?>
<h1>Kelola Pesanan</h1>
<div class="wrap"><table><tr><th>Kode</th><th>Tanggal</th><th>Pembeli</th><th>Total</th><th>Bayar</th><th>Resep</th><th>Status</th></tr>
<?php foreach ($rows as $r): ?><tr><td><a href="../pesanan.php?id=<?= $r['id'] ?>"><?= e($r['kode']) ?></a></td><td><?= tgl($r['created_at']) ?></td><td><?= e($r['pembeli']) ?></td><td><?= rp($r['total']) ?></td>
<td><?= $r['metode'] === 'cod' ? 'COD' : 'Transfer' ?></td><td><?= $r['resep'] ? '<a target="_blank" href="' . BASE . '/uploads/' . e($r['resep']) . '">Lihat</a>' : '-' ?></td>
<td><form method="post" class="bar" style="margin:0"><?= csrf() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
<select name="status"><?php foreach (['menunggu', 'diproses', 'selesai', 'batal'] as $s): ?><option <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
<button class="sm">Simpan</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="7">Belum ada pesanan.</td></tr><?php endif; ?></table></div>
<?php include '../includes/footer.php'; ?>
