<?php $u = user(); $adm = $u && $u['role'] === 'admin'; $n = array_sum($_SESSION['cart'] ?? []); $B = BASE; ?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($judul ?? '') ?> | <?= APP ?></title><link rel="stylesheet" href="<?= $B ?>/assets/style.css"></head>
<body>
<header class="top">
  <a class="brand" href="<?= $B ?>/"><span class="plus">+</span><?= APP ?></a>
  <nav>
  <?php if ($adm): ?>
    <a href="<?= $B ?>/admin/">Dashboard</a><a href="<?= $B ?>/admin/obat.php">Obat</a><a href="<?= $B ?>/admin/stok.php">Stok &amp; Pemasok</a>
    <a href="<?= $B ?>/admin/pesanan.php">Pesanan</a><a href="<?= $B ?>/admin/pelanggan.php">Pelanggan</a>
    <a href="<?= $B ?>/admin/laporan.php">Laporan</a><a href="<?= $B ?>/admin/log.php">Log</a><a href="<?= $B ?>/">Lihat toko</a>
  <?php else: ?>
    <a href="<?= $B ?>/">Obat</a><a href="<?= $B ?>/keranjang.php">Keranjang<?= $n ? " ($n)" : '' ?></a>
    <?php if ($u): ?><a href="<?= $B ?>/pesanan.php">Pesanan saya</a><?php endif; ?>
  <?php endif; ?>
  <?php if ($u): ?><a href="<?= $B ?>/logout.php">Keluar (<?= e($u['nama']) ?>)</a>
  <?php else: ?><a href="<?= $B ?>/login.php">Masuk</a><a href="<?= $B ?>/register.php">Daftar</a><?php endif; ?>
  </nav>
</header>
<main>
<?php if ($f = flash()): ?><div class="msg <?= $f[1] ?>"><?= e($f[0]) ?></div><?php endif; ?>
