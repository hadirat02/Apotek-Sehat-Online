<?php require 'config.php'; need(); $judul = 'Pesanan'; $adm = user()['role'] === 'admin';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
  $s = $db->prepare('SELECT p.*, u.nama pembeli, u.telepon FROM pesanan p JOIN users u ON u.id=p.user_id WHERE p.id=?'); $s->execute([$id]); $p = $s->fetch();
  if (!$p || (!$adm && $p['user_id'] != user()['id'])) { http_response_code(404); exit('Pesanan tidak ditemukan.'); }
  if (($_POST['aksi'] ?? '') === 'batal' && !$adm && $p['status'] === 'menunggu') {
    batal_pesanan($id); logact("Batal pesanan {$p['kode']}"); flash('Pesanan dibatalkan dan stok dikembalikan.'); header("Location: pesanan.php?id=$id"); exit;
  }
  $d = $db->prepare('SELECT * FROM pesanan_detail WHERE pesanan_id=?'); $d->execute([$id]); $det = $d->fetchAll();
  $judul = 'Nota ' . $p['kode'];
  include 'includes/header.php'; ?>
<h1>Nota <?= e($p['kode']) ?></h1>
<div class="panel">
  <p>Tanggal: <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?><br>Pembeli: <?= e($p['pembeli']) ?> (<?= e($p['telepon']) ?>)<br>
  Alamat: <?= e($p['alamat']) ?><br>Pembayaran: <?= $p['metode'] === 'cod' ? 'Bayar di tempat (COD)' : 'Transfer bank' ?><br>
  Status: <b><?= ucfirst($p['status']) ?></b><?= $p['resep'] ? ' &middot; <a href="' . BASE . '/uploads/' . e($p['resep']) . '" target="_blank">Lihat resep</a>' : '' ?></p>
  <div class="wrap"><table><tr><th>Obat</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr>
  <?php foreach ($det as $r): ?><tr><td><?= e($r['nama']) ?></td><td><?= rp($r['harga']) ?></td><td><?= $r['jumlah'] ?></td><td><?= rp($r['harga'] * $r['jumlah']) ?></td></tr><?php endforeach; ?>
  <tr><th colspan="3">Total</th><th><?= rp($p['total']) ?></th></tr></table></div>
  <?php if ($p['metode'] === 'transfer' && $p['status'] === 'menunggu'): ?><p class="msg ok">Silakan transfer <?= rp($p['total']) ?> ke <?= e(REKENING) ?>. Pesanan diproses setelah pembayaran dikonfirmasi apotek.</p><?php endif; ?>
  <div class="bar noprint"><button class="btn sec" onclick="window.print()">Cetak nota</button>
  <?php if (!$adm && $p['status'] === 'menunggu'): ?><form method="post"><?= csrf() ?><input type="hidden" name="aksi" value="batal"><button class="btn bad" onclick="return confirm('Batalkan pesanan ini?')">Batalkan pesanan</button></form><?php endif; ?></div>
</div>
<?php } else {
  $s = $db->prepare('SELECT * FROM pesanan WHERE user_id=? ORDER BY id DESC'); $s->execute([user()['id']]); $rows = $s->fetchAll();
  include 'includes/header.php'; ?>
<h1>Pesanan saya</h1>
<?php if (!$rows): ?><p>Belum ada pesanan. <a href="<?= BASE ?>/">Mulai belanja</a>.</p><?php else: ?>
<div class="wrap"><table><tr><th>Kode</th><th>Tanggal</th><th>Total</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['kode']) ?></td><td><?= tgl($r['created_at']) ?></td><td><?= rp($r['total']) ?></td><td><?= ucfirst($r['status']) ?></td><td><a href="?id=<?= $r['id'] ?>">Lihat nota</a></td></tr><?php endforeach; ?>
</table></div><?php endif; } include 'includes/footer.php'; ?>
