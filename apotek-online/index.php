<?php require 'config.php'; $judul = 'Katalog Obat';
$q = trim($_GET['q'] ?? ''); $k = (int)($_GET['k'] ?? 0);
$sql = 'SELECT o.*, k.nama kat FROM obat o LEFT JOIN kategori k ON k.id=o.kategori_id WHERE o.aktif=1'; $p = [];
if ($q !== '') { $sql .= ' AND (o.nama LIKE ? OR o.kegunaan LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
if ($k) { $sql .= ' AND o.kategori_id=?'; $p[] = $k; }
$st = $db->prepare($sql . ' ORDER BY o.nama'); $st->execute($p); $rows = $st->fetchAll();
$kats = $db->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();
include 'includes/header.php'; ?>
<h1>Katalog Obat</h1>
<form class="bar" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama obat atau kegunaan" size="32">
  <select name="k"><option value="0">Semua kategori</option>
    <?php foreach ($kats as $c): ?><option value="<?= $c['id'] ?>" <?= $k == $c['id'] ? 'selected' : '' ?>><?= e($c['nama']) ?></option><?php endforeach; ?></select>
  <button>Cari</button>
</form>
<?php if (!$rows): ?><p>Tidak ada obat yang cocok. Coba kata kunci atau kategori lain.</p><?php endif; ?>
<div class="grid">
<?php foreach ($rows as $o): $ke = kad($o['kadaluarsa']); $ada = $o['stok'] > 0 && !$ke[2]; ?>
  <div class="card">
    <a href="obat.php?id=<?= $o['id'] ?>"><img src="<?= img($o['gambar']) ?>" alt="<?= e($o['nama']) ?>" loading="lazy"></a>
    <div class="b">
      <span class="mut"><?= e($o['kat'] ?: 'Umum') ?></span>
      <h3><a href="obat.php?id=<?= $o['id'] ?>"><?= e($o['nama']) ?></a></h3>
      <?= gol($o['golongan']) ?>
      <span class="price"><?= rp($o['harga_jual']) ?> <span class="mut">/ <?= e($o['satuan']) ?></span></span>
      <?php if ($ke[2]): ?><span class="tag bad">Tidak dijual (kadaluarsa)</span>
      <?php elseif ($o['stok'] <= 0): ?><span class="tag bad">Stok habis</span>
      <?php else: ?><span class="mut">Stok: <?= $o['stok'] ?></span><?php endif; ?>
      <?php if ($ada): ?>
      <form method="post" action="keranjang.php" style="margin-top:auto"><?= csrf() ?>
        <input type="hidden" name="aksi" value="tambah"><input type="hidden" name="id" value="<?= $o['id'] ?>">
        <button class="sm">Tambah ke keranjang</button></form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
