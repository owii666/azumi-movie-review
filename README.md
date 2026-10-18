# Azumi Movie Reviews

**Live Demo:** https://azumi-movie-review.gt.tc/

---

## Project Overview

Azumi Movie Reviews adalah platform web berbasis PHP Native yang menyajikan katalog ulasan film independen dan sinema Asia. Website ini dirancang untuk memudahkan pengunjung membaca ulasan terkurasi, mencari ulasan film secara cepat, mengirimkan ulasan mereka sendiri, serta memantau film-film yang akan datang (Upcoming). Dilengkapi dengan panel dashboard admin untuk proses moderasi konten dan kurasi ulasan sebelum dipublikasikan ke publik.

---

## Features

- **Katalog & Detail Ulasan**: Menampilkan ulasan film pilihan (featured review), daftar ulasan terkurasi, dan halaman artikel ulasan lengkap.
- **Pencarian Cepat (Live Search)**: Fitur pencarian instan berbasis AJAX yang mempermudah pencarian judul ulasan tanpa harus memuat ulang halaman.
- **Pengiriman Ulasan Komunitas (Submit Review)**: Formulir bagi pengunjung untuk mengirimkan naskah ulasan lengkap beserta unggahan poster film.
- **Upcoming Watchlist**: Halaman khusus untuk menampilkan daftar film mendatang yang sedang dinantikan.
- **Admin Dashboard**: Panel moderasi bagi redaksi/admin untuk meninjau, menyetujui (approve), menolak (reject), atau menyunting ulasan kiriman pengguna, serta mengelola daftar film Upcoming.
- **Keamanan Aplikasi**: Dilengkapi dengan PDO prepared statements untuk pencegahan SQL Injection, enkripsi kata sandi menggunakan hashing standar industri, proteksi CSRF pada formulir, serta validasi dan sanitasi unggahan berkas.

---

## Tech Stack

- **Backend**: PHP Native (PDO MySQL)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla / AJAX)
- **Web Server**: Apache

---

## Project Structure

Ringkasan struktur direktori utama pada proyek ini:

```text
azumi-rv-php/
├── admin/            # Panel dashboard, autentikasi, dan moderasi ulasan
├── assets/           # Aset statis (CSS, JavaScript, dan gambar antarmuka)
├── config/           # Konfigurasi koneksi database dan environment
├── database/         # Skema SQL (schema.sql) dan skrip utilitas database
├── includes/         # Komponen bersama (header, footer, helper, autentikasi)
├── uploads/          # Direktori penyimpanan berkas poster film hasil unggahan
├── index.php         # Halaman utama (beranda)
├── reviews.php       # Halaman katalog ulasan film
├── review-detail.php # Halaman ulasan lengkap
├── upcoming.php      # Halaman daftar film mendatang
├── about.php         # Halaman tentang dan profil redaksi
├── submit.php        # Formulir pengiriman ulasan film
├── search.php        # Halaman hasil pencarian ulasan
└── api-search.php    # Endpoint pencarian live (JSON)
```

---

## Requirements

Untuk menjalankan proyek ini di lingkungan lokal, pastikan perangkat Anda memenuhi kebutuhan berikut:

- PHP versi 7.4 atau lebih baru (PHP 8.x didukung)
- MySQL versi 5.7+ atau MariaDB 10.3+
- Web server Apache (disarankan menggunakan Laragon atau stack lokal sejenis)
- Ekstensi PHP aktif: `pdo_mysql`, `mbstring`, `fileinfo`, dan `gd`

---

## Local Installation

Berikut langkah-langkah untuk menjalankan proyek di komputer lokal menggunakan Laragon:

1. Unduh atau salin folder proyek `azumi-rv-php` ke direktori web root Laragon Anda (biasanya di `C:\laragon\www\azumi-rv-php`).
2. Buka aplikasi Laragon dan klik tombol **Start All** untuk menyalakan Apache dan MySQL.
3. Lakukan konfigurasi database dengan mengimpor skema database (lihat bagian **Database Setup** di bawah).
4. Pastikan file konfigurasi database lokal telah disesuaikan (lihat bagian **Configuration** di bawah).
5. Buka web browser, lalu akses alamat proyek:
   ```text
   http://localhost/azumi-rv-php/
   ```
   *(atau `http://azumi-rv-php.test/` jika menggunakan fitur Pretty URL Laragon)*.

---

## Database Setup

1. Buka pengelola database lokal Anda (misalnya phpMyAdmin atau HeidiSQL melalui Laragon).
2. Buat sebuah database baru, misalnya dengan nama `azumi_reviews`.
3. Buka tab **Import** pada database tersebut, lalu pilih berkas `database/schema.sql` dari folder proyek.
4. Jalankan proses impor untuk membuat seluruh tabel dan data ulasan awal yang diperlukan.

---

## Configuration

Pengaturan koneksi database dikelola secara terpusat di dalam folder `config/`:

- Konfigurasi default pada `config/database.php` telah disesuaikan dengan pengaturan standar lingkungan lokal Laragon (host lokal, port default MySQL, user default, dan tanpa password).
- Jika Anda menggunakan kredensial atau port yang berbeda pada lingkungan pengembangan Anda, Anda dapat membuat file `config/database.local.php` (dapat menyalin dari template `config/database.local.example.php`).
- Atur parameter host, port, nama database, username, dan password sesuai dengan konfigurasi lokal Anda. File `database.local.php` sudah terdaftar di `.gitignore` sehingga tidak akan terunggah ke repositori publik.

---

## Admin Access

- Halaman login panel admin dapat diakses melalui URL:
  ```text
  http://localhost/azumi-rv-php/admin/
  ```
- Jika database baru saja diimpor dan belum memiliki akun administrator, sistem menyediakan halaman inisialisasi akun pertama di `/admin/setup.php` yang dapat dibuka melalui browser.
- Daftarkan akun administrator baru Anda melalui formulir yang tersedia. Setelah akun berhasil dibuat, halaman inisialisasi otomatis terkunci demi alasan keamanan dan akses pengelolaan selanjutnya dilakukan melalui halaman login admin.

---

## License

Proyek ini dibuat dan dikembangkan untuk keperluan pengembangan keterampilan, pembelajaran web development, serta portofolio pribadi. Bebas digunakan dan dipelajari untuk keperluan edukatif dan non-komersial.
