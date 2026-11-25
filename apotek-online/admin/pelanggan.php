<?php require '../config.php'; need('admin'); $judul = 'Pelanggan';
$rows = $db->query("SELECT u.*, COUNT(p.id) jml, COALESCE(SUM(CASE WHEN p.status<>'batal' THEN p.total END),0) belanja
  FROM users u LEFT JOIN pesanan p ON p.user_id=u.id WHERE u.role='pembeli' GROUP BY u.id ORDER BY belanja DESC")->fetchAll();
include '../includes/header.php'; ?>
<h1>Data Pelanggan</h1>
<div class="wrap"><table><tr><th>Nama</th><th>Email</th><th>Telepon</th><th>Alamat</th><th>Jumlah pesanan</th><th>Total belanja</th><th>Terdaftar</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['nama']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['telepon']) ?></td><td><?= e($r['alamat']) ?></td><td><?= $r['jml'] ?></td><td><?= rp($r['belanja']) ?></td><td><?= tgl($r['created_at']) ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="7">Belum ada pelanggan terdaftar.</td></tr><?php endif; ?></table></div>
<?php include '../includes/footer.php'; ?>
