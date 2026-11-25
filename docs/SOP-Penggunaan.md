# SOP Penggunaan Sistem Dedy Jaya Farm

## 1. Tujuan dan pengguna

SOP ini digunakan oleh admin dan operator Dedy Jaya Farm untuk mengelola identitas, pertumbuhan, dan histori pemeliharaan sapi melalui sistem Smart Livestock Traceability.

## 2. Masuk ke panel

1. Kunjungi `/admin/login`.
2. Masuk menggunakan akun admin yang telah dibuat oleh pengelola sistem.
3. Jika akun admin pertama belum tersedia, pengelola server membuatnya melalui `php artisan farm:admin`.

## 3. Mendaftarkan sapi baru

1. Dari dashboard, pilih **Tambah sapi baru** atau siapkan QR lebih dulu dari menu **QR ternak**.
2. Isi nomor tag yang belum pernah digunakan, ras, jenis kelamin, dan tanggal masuk kandang.
3. Lengkapi harga, umur, bobot awal, foto, dan catatan jika tersedia.
4. Pilih QR tersedia jika ingin langsung memasangkannya, atau biarkan kosong dan pasangkan dari halaman kelola sapi nanti.
5. Pilih **Simpan & buat profil**. Sistem membuat URL profil dan histori khusus untuk individu ini.
6. Setelah sapi tercatat, buka halaman kelolanya dan periksa data profil. Jika QR belum terpasang, pilih QR dari bagian pasang tag.

Jangan menggunakan ulang rekaman atau nomor tag sapi lama untuk sapi yang berbeda. Setiap sapi baru harus didaftarkan sebagai individu baru.

## 4. Membuat, memasang, dan menggunakan kembali QR

1. Buka menu **QR ternak**, masukkan kode tag, lalu pilih **Buat QR**. URL pendek dibuat otomatis dari kode, misalnya `DJF-1001` menjadi `/sapi/djf-1001`.
2. Pada halaman kelola sapi, pilih QR yang belum dipasangkan untuk mengaitkan tag ke sapi.
3. Dari menu **QR ternak**, pilih **Unduh SVG** untuk berkas vektor atau **Unduh PNG** untuk berkas gambar.
4. Uji pindai QR dengan ponsel sebelum mencetak tag.
5. Pasang QR pada name tag fisik dan pastikan kode tag cocok.
6. Pelanggan memindai QR untuk membuka profil sapi yang sedang ditugaskan ke tag itu.

QR hanya dapat dibuat dan diunduh oleh admin. URL pendek QR adalah identitas fisik tag yang tetap; katalog memakai URL yang sama untuk sapi aktif. QR yang sudah dicetak dapat dipakai ulang tanpa cetak ulang.

## 5. Mencatat penimbangan

1. Buka profil sapi dari menu **Data sapi**.
2. Pada bagian **Catat penimbangan**, masukkan tanggal dan bobot.
3. Tambahkan catatan dan foto penimbangan bila diperlukan.
4. Pilih **Simpan bobot**. Riwayat yang baru akan muncul pada halaman profil publik.

## 6. Mencatat kesehatan dan perawatan

1. Pada halaman kelola sapi, pilih kategori kesehatan, vaksinasi, pengobatan, atau perawatan.
2. Isi tanggal, judul, dan keterangan tindakan atau kondisi.
3. Jika ada video, masukkan URL video YouTube. Video akan ditampilkan dengan embed YouTube; unggah video langsung tidak digunakan.
4. Pilih **Simpan riwayat** dan pastikan catatan tampil di profil sapi yang benar.

## 7. Menandai sapi terjual

1. Buka halaman kelola individu yang telah terjual.
2. Pilih **Tandai terjual** dan konfirmasi tindakan.
3. Sapi hilang dari katalog penjualan. Profil dan histori tetap tersimpan dengan status **Sudah terjual** pada slug arsip berurutan, misalnya `/sapi/djf-1001-sold1`. QR fisiknya otomatis dilepas; URL pendek menampilkan status tag sedang disiapkan.
4. Saat sapi pengganti datang, buat rekaman individu baru dan pasangkan tag QR yang tersedia.
5. Setelah dipasangkan ulang, URL fisik QR yang sama membuka profil sapi baru. Slug arsip sapi lama tetap menampilkan profil dan riwayat individu yang terjual.

## 8. Mengubah pengaturan situs

1. Buka menu **Pengaturan**.
2. Atur nama peternakan, domain publik lengkap dengan `https://`, dan nomor WhatsApp dengan kode negara (angka saja).
3. Simpan perubahan dan uji katalog, tautan kontak, serta URL QR.
4. Setiap QR yang telah dicetak masih mengarah ke domain lama. Pertahankan domain lama dan atur pengalihan (redirect) ke domain baru agar QR tetap berfungsi; jika domain lama tidak bisa dipertahankan, QR perlu dicetak ulang.

## 9. Pemeriksaan rutin

- Cocokkan nomor tag fisik dengan nomor pada profil sebelum menyimpan data.
- Pastikan tanggal, bobot, dan kategori perawatan telah benar.
- Pindai QR secara berkala untuk memastikan label terbaca dan domain masih aktif.
- Jangan menghapus atau menggunakan ulang identitas sapi terjual.
