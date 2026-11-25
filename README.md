# Dedy Jaya Farm

Website Smart Livestock Traceability untuk katalog sapi, profil publik per individu, histori bobot dan kesehatan, serta pengelolaan QR code.

## Fitur

- Katalog publik sapi berstatus tersedia dengan pencarian dan harga.
- Profil publik per sapi melalui URL unik yang bisa dibuka dari QR.
- Histori penimbangan, pertumbuhan bobot, kesehatan, vaksinasi, pengobatan, dan perawatan.
- Video perawatan ditampilkan melalui embed YouTube; unggah video langsung tidak tersedia.
- Dashboard admin untuk mendaftarkan sapi baru, memperbarui data, merekam histori, dan menandai sapi terjual. Profil serta histori sapi terjual tetap tersimpan.
- QR tag fisik memiliki URL stabil dan dapat dipindah ke sapi baru tanpa dicetak ulang; histori penggunaan ulang tag tetap tercatat.
- Pembuatan/unduhan QR hanya tersedia untuk admin, dalam format SVG dan PNG.
- Pengaturan domain publik, nama dan profil peternakan, konten beranda, serta informasi kontak dan WhatsApp.

## Menjalankan aplikasi

Persyaratan: PHP 8.3+, Composer, Node.js, dan npm. Aktifkan ekstensi PHP GD untuk unduhan QR PNG.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan serve
```

Atur `APP_URL` di `.env` ke domain yang digunakan, lalu buat akun admin pertama:

```bash
php artisan farm:admin
```

Buka `/admin/login` untuk masuk. Pada lingkungan lokal (`APP_ENV=local`), `php artisan db:seed` membuat akun admin demo berikut:

- Email: `admin@dedijayafarm.test`
- Password: `DedyJayaDemo!2026`

Akun demo hanya dibuat pada lingkungan lokal dan dapat diubah melalui `DEMO_ADMIN_NAME`, `DEMO_ADMIN_EMAIL`, serta `DEMO_ADMIN_PASSWORD` di `.env`. Jangan gunakan kredensial demo untuk situs produksi; buat akun produksi dengan `php artisan farm:admin`. Seeder juga membuat data contoh dan pengaturan profil awal, serta aman dijalankan ulang tanpa membuat duplikat. Admin dapat mengubah konten beranda dan kontak melalui menu **Pengaturan**.

## Pengelolaan sapi

Setiap sapi dibuat sebagai rekaman individu baru dengan nomor tag yang unik. Setelah penjualan, tandai sebagai terjual; jangan menggunakan ulang data individu tersebut untuk sapi berikutnya. URL profil sapi lama tetap menyimpan status terjual dan seluruh histori.

Buat QR melalui menu admin **QR ternak**. Setiap tag memperoleh URL pendek permanen seperti `/sapi/djf-1001`, lalu dapat dipasangkan dengan sapi dari formulir sapi baru atau halaman kelola sapi. Katalog dan QR aktif menggunakan URL pendek tag yang sama.

Saat sapi ditandai terjual, slug arsip individunya menjadi `/sapi/djf-1001-sold1`, lalu meningkat berdasarkan riwayat penggunaan QR (`-sold2`, dan seterusnya). Slug arsip dan histori sapi tetap tersimpan, sementara URL pendek QR kembali menampilkan status tag belum dipasangkan hingga QR ditautkan ke sapi berikutnya. QR fisik yang sama dapat dicetak dan dipakai ulang tanpa perubahan URL. Rute lama `/qr/{token}` tetap mengarah ke URL pendek QR.

Pada menu **QR ternak**, admin dapat mengunduh QR dalam format SVG (vektor) atau PNG dan memeriksa sapi yang sedang ditautkan. Berkas tersebut disiapkan untuk alur cetak name tag; sistem tidak membuat berkas model 3D STL. URL yang sudah dicetak tidak dapat berubah ketika domain berubah: pertahankan domain lama dengan pengalihan ke domain baru, atau cetak ulang hanya jika domain lama tidak dapat dipertahankan.

Panduan operator dan prosedur penggunaan tersedia di [SOP-Penggunaan.md](./docs/SOP-Penggunaan.md).

## Pengujian

```bash
php artisan test
```
