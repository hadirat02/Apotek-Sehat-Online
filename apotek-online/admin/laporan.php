<?php require '../config.php'; need('admin'); $judul = 'Laporan';
$dari = $_GET['dari'] ?? date('Y-m-01'); $sampai = $_GET['sampai'] ?? date('Y-m-d');
$s = $db->prepare("SELECT d.nama, SUM(d.jumlah) qty, SUM(d.jumlah*d.harga) omzet, SUM(d.jumlah*(d.harga-d.harga_beli)) laba
  FROM pesanan_detail d JOIN pesanan p ON p.id=d.pesanan_id WHERE p.status<>'batal' AND DATE(p.created_at) BETWEEN ? AND ? GROUP BY d.obat_id,d.nama ORDER BY omzet DESC");
$s->execute([$dari, $sampai]); $jual = $s->fetchAll();
$stok = $db->query('SELECT nama,stok,satuan,harga_beli,kadaluarsa FROM obat WHERE aktif=1 ORDER BY nama')->fetchAll();
include '../includes/header.php'; ?>
<h1>Laporan</h1>
<form class="bar noprint" method="get"><label style="margin:8px 0">Dari</label><input type="date" name="dari" value="<?= e($dari) ?>">
<label style="margin:8px 0">Sampai</label><input type="date" name="sampai" value="<?= e($sampai) ?>"><button>Tampilkan</button>
<button type="button" class="btn sec" onclick="window.print()">Cetak</button></form>
<h2>Penjualan <?= tgl($dari) ?> s.d. <?= tgl($sampai) ?></h2>
<div class="wrap"><table><tr><th>Obat</th><th>Jumlah terjual</th><th>Omzet</th><th>Laba kotor</th></tr>
<?php $t = [0, 0, 0]; foreach ($jual as $r) { $t[0] += $r['qty']; $t[1] += $r['omzet']; $t[2] += $r['laba']; ?>
<tr><td><?= e($r['nama']) ?></td><td><?= $r['qty'] ?></td><td><?= rp($r['omzet']) ?></td><td><?= rp($r['laba']) ?></td></tr><?php } ?>
<?php if (!$jual): ?><tr><td colspan="4">Tidak ada penjualan pada periode ini.</td></tr><?php endif; ?>
<tr><th>Total</th><th><?= $t[0] ?></th><th><?= rp($t[1]) ?></th><th><?= rp($t[2]) ?></th></tr></table></div>
<h2>Posisi stok saat ini</h2>
<div class="wrap"><table><tr><th>Obat</th><th>Stok</th><th>Nilai stok (harga beli)</th><th>Kadaluarsa</th></tr>
<?php $nv = 0; foreach ($stok as $r) { $nv += $r['stok'] * $r['harga_beli']; $k = kad($r['kadaluarsa']); ?>
<tr><td><?= e($r['nama']) ?></td><td><?= $r['stok'] ?> <?= e($r['satuan']) ?></td><td><?= rp($r['stok'] * $r['harga_beli']) ?></td><td><?= tgl($r['kadaluarsa']) ?> <span class="tag <?= $k[1] ?>"><?= $k[0] ?></span></td></tr><?php } ?>
<tr><th colspan="2">Total nilai persediaan</th><th colspan="2"><?= rp($nv) ?></th></tr></table></div>
<?php include '../includes/footer.php'; ?>
