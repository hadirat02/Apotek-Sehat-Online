<?php require '../config.php'; need('admin'); $judul = 'Log Aktivitas';
$rows = $db->query('SELECT l.*, u.nama FROM log_aktivitas l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT 100')->fetchAll();
include '../includes/header.php'; ?>
<h1>Log Aktivitas (100 terakhir)</h1>
<div class="wrap"><table><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td><td><?= e($r['nama'] ?: '-') ?></td><td><?= e($r['aksi']) ?></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="3">Belum ada aktivitas tercatat.</td></tr><?php endif; ?></table></div>
<?php include '../includes/footer.php'; ?>
