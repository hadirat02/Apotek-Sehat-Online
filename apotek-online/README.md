# Apotek Online (PHP + MySQL)

## Cara menjalankan (XAMPP)
1. Salin folder `apotek-online` ke `C:\xampp\htdocs\`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, tab **Import**, pilih `database.sql`, klik **Import** (database `apotek_online` dibuat otomatis).
4. Buka `http://localhost/apotek-online/`.

## Akun contoh
- Penjual/admin: `admin@apotek.test` / `admin123`
- Pembeli: `budi@contoh.test` / `pembeli123`

Ganti kata sandi akun contoh dan nomor rekening (`REKENING` di `config.php`) sebelum dipakai sungguhan.
Pengaturan koneksi database ada di `config.php` (bawaan XAMPP: user `root`, tanpa password).

## Struktur
`config.php` fungsi bersama | `index.php` katalog | `keranjang.php` checkout | `pesanan.php` nota
`admin/` dashboard, obat, stok & pemasok, pesanan, pelanggan, laporan, log | `uploads/` gambar obat dan resep
