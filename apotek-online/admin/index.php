<?php require '../config.php'; need('admin'); $judul = 'Dashboard';
$q1 = fn($sql) => $db->query($sql)->fetchColumn();
$jenis = $q1('SELECT COUNT(*) FROM obat WHERE aktif=1'); $stok = $q1('SELECT COALESCE(SUM(stok),0) FROM obat WHERE aktif=1');
$terjual = $q1("SELECT COALESCE(SUM(d.jumlah),0) FROM pesanan_detail d JOIN pesanan p ON p.id=d.pesanan_id WHERE p.status<>'batal'");
$omzet = $q1("SELECT COALESCE(SUM(total),0) FROM pesanan WHERE status='selesai'");
$tunggu = $q1("SELECT COUNT(*) FROM pesanan WHERE status='menunggu'");
$menipis = $db->query('SELECT nama,stok,stok_min,satuan FROM obat WHERE aktif=1 AND stok<=stok_min ORDER BY stok')->fetchAll();
$kadal = $db->query('SELECT nama,kadaluarsa,stok FROM obat WHERE aktif=1 AND kadaluarsa IS NOT NULL AND kadaluarsa<=DATE_ADD(CURDATE(),INTERVAL 90 DAY) ORDER BY kadaluarsa')->fetchAll();
$top = $db->query("SELECT d.nama, SUM(d.jumlah) qty FROM pesanan_detail d JOIN pesanan p ON p.id=d.pesanan_id WHERE p.status<>'batal' GROUP BY d.obat_id,d.nama ORDER BY qty DESC LIMIT 5")->fetchAll();
$qs = array_column($top, 'qty'); $max = $qs ? max($qs) : 1;
include '../includes/header.php'; ?>
<h1>Dashboard</h1>
<div class="stats">
  <div class="stat"><span class="mut">Jenis obat aktif</span><b><?= $jenis ?></b></div>
  <div class="stat"><span class="mut">Total stok</span><b><?= $stok ?></b></div>
  <div class="stat"><span class="mut">Total terjual</span><b><?= $terjual ?></b></div>
  <div class="stat"><span class="mut">Pendapatan (pesanan selesai)</span><b><?= rp($omzet) ?></b></div>
  <div class="stat"><span class="mut">Pesanan menunggu</span><b><?= $tunggu ?></b></div>
</div>
<div class="two">
  <div><h2>Stok menipis</h2><div class="wrap"><table><tr><th>Obat</th><th>Stok</th><th>Minimum</th></tr>
  <?php foreach ($menipis as $r): ?><tr><td><?= e($r['nama']) ?></td><td><span class="tag bad"><?= $r['stok'] ?> <?= e($r['satuan']) ?></span></td><td><?= $r['stok_min'] ?></td></tr><?php endforeach; ?>
  <?php if (!$menipis): ?><tr><td colspan="3">Semua stok di atas batas minimum.</td></tr><?php endif; ?></table></div></div>
  <div><h2>Kadaluarsa dalam 90 hari</h2><div class="wrap"><table><tr><th>Obat</th><th>Tanggal</th><th>Status</th></tr>
  <?php foreach ($kadal as $r): $k = kad($r['kadaluarsa']); ?><tr><td><?= e($r['nama']) ?></td><td><?= tgl($r['kadaluarsa']) ?></td><td><span class="tag <?= $k[1] ?>"><?= $k[0] ?></span></td></tr><?php endforeach; ?>
  <?php if (!$kadal): ?><tr><td colspan="3">Tidak ada obat yang mendekati kadaluarsa.</td></tr><?php endif; ?></table></div></div>
</div>
<h2>Obat terlaris</h2>
<div class="panel"><?php foreach ($top as $r): ?>
  <div style="display:grid;grid-template-columns:200px 1fr 50px;gap:10px;align-items:center;margin:6px 0"><span><?= e($r['nama']) ?></span>
  <div class="meter"><i style="width:<?= round($r['qty'] / $max * 100) ?>%"></i></div><b><?= $r['qty'] ?></b></div>
<?php endforeach; if (!$top) echo 'Belum ada penjualan.'; ?></div>
<?php include '../includes/footer.php'; ?>
