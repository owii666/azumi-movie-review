# Azumi Movie Reviews 🎬

> Platform portal ulasan film independen dan sinema Asia berbasis **PHP Native** murni dengan arsitektur modular, **PDO MySQL**, sistem moderasi redaksi, pencarian live, dan tata kelola kredensial yang aman.

---

## Daftar Isi
1. [Deskripsi & Fitur Utama](#deskripsi--fitur-utama)
2. [Kebutuhan Sistem](#kebutuhan-sistem)
3. [Panduan Menjalankan di Laragon (Lokal)](#panduan-menjalankan-di-laragon-lokal)
4. [Sistem Konfigurasi Database Fleksibel](#sistem-konfigurasi-database-fleksibel)
5. [Struktur Direktori Proyek](#struktur-direktori-proyek)
6. [Panduan Deployment Manual ke InfinityFree](#panduan-deployment-manual-ke-infinityfree)
7. [Batasan Hosting InfinityFree & Solusinya](#batasan-hosting-infinityfree--solusinya)
8. [Prosedur Backup & Restore Data](#prosedur-backup--restore-data)
9. [Keamanan & Panduan GitHub Private](#keamanan--panduan-github-private)

---

## Deskripsi & Fitur Utama

Azumi Movie Reviews dirancang dengan fokus pada kesederhanaan, performa, dan keamanan tanpa dependensi library eksternal (zero-dependency).

- **Portal Publik**: Menampilkan spotlight ulasan terbaru (*Featured Review*), grid ulasan terkurasi, daftar pantau *Upcoming Films*, halaman *About Us*, dan artikel ulasan lengkap.
- **Pencarian Live (AJAX)**: Pencarian instan film dan ulasan berbasis kata kunci tanpa memuat ulang halaman (`api-search.php`).
- **Formulir Pengiriman Ulasan Komunitas**: Pengunjung dapat mengirimkan naskah ulasan dengan validasi paragraf (2-5 paragraf), proteksi CSRF, pola *Post-Redirect-Get* (PRG), dan sanitasi unggahan poster.
- **Dashboard Administrator**: Panel moderasi redaksi untuk meninjau naskah ulasan (*Approve / Reject*), manajemen film *Upcoming*, serta pengaturan kredensial dan kata sandi admin.
- **Keamanan Berlapis**:
  - Semua query SQL menggunakan *PDO Prepared Statements* untuk mencegah SQL Injection.
  - Hashing kata sandi menggunakan algoritma standar industri `password_hash()` (Bcrypt).
  - Proteksi CSRF (*Cross-Site Request Forgery*) pada seluruh formulir mutasi data.
  - Pembatasan eksekusi skrip berbahaya di folder unggahan melalui aturan `.htaccess`.
  - Pemisahan total antara kredensial hosting/produksi dengan source code di repository.

---

## Kebutuhan Sistem

- **PHP**: Versi `7.4` atau `8.0` / `8.1` / `8.2` / `8.3`
- **Database**: MySQL `5.7+` atau MariaDB `10.3+`
- **Ekstensi PHP Wajib**:
  - `pdo_mysql` (Koneksi database PDO)
  - `mbstring` (Manipulasi karakter UTF-8)
  - `fileinfo` (Deteksi MIME-type berkas gambar yang aman)
  - `gd` (Validasi berkas gambar nyata via `getimagesize`)
- **Web Server**: Apache dengan modul `mod_rewrite` dan `mod_headers` aktif.

---

## Panduan Menjalankan di Laragon (Lokal)

Proyek ini telah dikonfigurasi agar **langsung berjalan di Laragon tanpa memerlukan pengaturan rumit**:

1. **Letakkan Folder Proyek**:
   Pindahkan folder `azumi-rv-php` ke direktori root Laragon Anda:
   ```text
   C:\laragon\www\azumi-rv-php
   ```
   *(Atau direktori pengembangan Anda saat ini seperti `D:\web-rev\azumi-rv-php`)*.

2. **Jalankan Laragon**:
   - Buka aplikasi Laragon, lalu klik tombol **Start All** (Apache & MySQL aktif).

3. **Inisialisasi Database Otomatis**:
   - Skrip `config/database.php` menggunakan setelan bawaan Laragon (`127.0.0.1`, port `3306`, user `root`, password kosong).
   - Saat Anda pertama kali mengakses website di browser, sistem akan secara otomatis membuat database `azumi_reviews` dan memuat tabel serta data awal dari `database/schema.sql`.
   - *Alternatif CLI*: Anda juga dapat menjalankan migrasi manual melalui terminal:
     ```bash
     php database/seed.php
     ```

4. **Akses Website**:
   - Buka browser dan kunjungi:
     ```text
     http://localhost/azumi-rv-php/
     ```
     *(Atau `http://azumi-rv-php.test/` jika menggunakan Virtual Host bawaan Laragon)*.

5. **Inisialisasi Akun Administrator Lokal**:
   - Jika belum ada akun admin di database, buka halaman:
     ```text
     http://localhost/azumi-rv-php/admin/setup.php
     ```
   - Masukkan username dan password yang Anda kehendaki, lalu simpan.
   - Anda akan langsung diarahkan ke halaman login dashboard admin (`/admin`).

---

## Sistem Konfigurasi Database Fleksibel

Pengaturan database dikelola secara terpusat di file [`config/database.php`](file:///d:/web-rev/azumi-rv-php/config/database.php) dengan mekanisme fallback bertingkat:

```text
┌─────────────────────────────────────────────────────────────┐
│ 1. Cek apakah config/database.local.php ada di server?      │
│    -> JIKA YA: Gunakan kredensial khusus dari file tersebut.│
└──────────────────────────────┬──────────────────────────────┘
                               │ (Jika tidak ada)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Cek apakah ada Environment Variable sistem (getenv)?    │
│    -> JIKA YA: Gunakan variabel lingkungan tersebut.        │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Jika tidak ada)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Fallback Standar Laragon:                                │
│    DB_HOST = '127.0.0.1'       DB_PORT = '3306'             │
│    DB_USER = 'root'            DB_PASS = ''                 │
│    DB_NAME = 'azumi_reviews'                                │
└─────────────────────────────────────────────────────────────┘
```

### Menggunakan File Konfigurasi Khusus
Jika Anda menggunakan port MySQL berbeda di lokal atau ingin menyiapkan kredensial hosting:
1. Salin file template:
   ```bash
   cp config/database.local.example.php config/database.local.php
   ```
2. Buka `config/database.local.php` dan sesuaikan kredensial Anda.
3. **Penting**: File `database.local.php` sudah otomatis diabaikan oleh `.gitignore`, sehingga aman dan tidak akan pernah terunggah ke repository GitHub.

---

## Struktur Direktori Proyek

```text
azumi-rv-php/
├── admin/                     # Dashboard & Manajemen Redaksi
│   ├── index.php              # Daftar moderasi ulasan (Pending/Approved/Rejected)
│   ├── login.php              # Autentikasi aman administrator
│   ├── logout.php             # Pembersihan sesi login
│   ├── setup.php              # Inisialisasi akun admin awal (terkunci otomatis)
│   ├── account.php            # Pengaturan akun & ganti password admin
│   ├── submission-view.php    # Detail ulasan lengkap untuk ditinjau
│   ├── submission-edit.php    # Formulir edit ulasan oleh admin
│   ├── submission-action.php  # Pemroses aksi moderasi (Approve, Reject, Delete)
│   ├── upcoming.php           # Manajemen daftar film upcoming
│   ├── upcoming-edit.php      # Edit judul/poster film upcoming
│   └── upcoming-action.php    # Pemroses tambah/hapus film upcoming
│
├── assets/                    # Aset Statis Website (Wajib Dilacak Git)
│   ├── css/                   # Stylesheet (style.css, admin.css, responsive.css)
│   ├── images/                # Poster bawaan tema, avatar penulis, ilustrasi
│   └── js/                    # Skrip interaktif (script.js, search.js)
│
├── config/                    # Konfigurasi Aplikasi
│   ├── database.php           # Logika koneksi PDO & deteksi konfigurasi
│   ├── database.local.example.php # Template konfigurasi lokal/hosting
│   └── database.local.php     # [Git-Ignored] Kredensial aktif di server
│
├── database/                  # Skrip & Skema Basis Data
│   ├── schema.sql             # Skema DDL tabel dan data ulasan awal
│   ├── seed.php               # Skrip migrasi & verifikasi tabel (CLI)
│   ├── migrate_upcoming.php   # Migrasi penambahan tabel upcoming_films
│   └── reset_admin_password.php # Utilitas pemulihan kata sandi admin (CLI)
│
├── includes/                  # Komponen & Pustaka Helper PHP
│   ├── auth.php               # Logika sesi, otentikasi & proteksi akses admin
│   ├── functions.php          # Fungsi sanitasi, CSRF token, URL poster, formatting
│   ├── header.php             # Header & navigasi publik
│   └── footer.php             # Footer publik
│
├── uploads/                   # Folder Penyimpanan File Dinamis
│   ├── .htaccess              # Perlindungan eksekusi skrip berbahaya
│   └── posters/               # Direktori penyimpanan poster film hasil unggahan
│       └── .gitkeep           # Mempertahankan struktur folder di git
│
├── tests/                     # Skrip Pengujian Otomatis (Hanya untuk lokal/dev)
├── .gitignore                 # Aturan pengabaian file Git
├── .htaccess                  # Konfigurasi Apache, headers & rewrite routing /admin
├── index.php                  # Halaman Beranda Publik
├── reviews.php                # Katalog Ulasan Film
├── review-detail.php          # Halaman Baca Ulasan Lengkap
├── upcoming.php               # Halaman Watchlist Film Mendatang
├── about.php                  # Profil Redaksi & Tentang Website
├── submit.php                 # Formulir Kirim Ulasan oleh Pengunjung
├── search.php                 # Halaman Hasil Pencarian
├── api-search.php             # Endpoint JSON Pencarian Live
└── README.md                  # Dokumentasi Resmi Proyek
```

---

## Panduan Deployment Manual ke InfinityFree

InfinityFree menyediakan hosting gratis berbasis cPanel (*VistaPanel*) dengan web server Apache dan database MySQL. Berikut panduan langkah-demi-langkah deployment yang aman dan teruji:

### Langkah 1: Buat Database MySQL di VistaPanel InfinityFree
1. Masuk ke **Client Area** InfinityFree dan buka **Control Panel** (vPanel) akun Anda.
2. Cari dan klik menu **MySQL Databases**.
3. Pada bagian *Create a New Database*, masukkan nama database (misalnya: `azumi`), lalu klik **Create Database**.
4. Sistem akan menghasilkan nama database lengkap dengan prefix akun Anda, misalnya: `if0_12345678_azumi`.
5. Catat 4 informasi penting berikut dari halaman *MySQL Databases* / *Account Details*:
   - **MySQL Hostname**: contoh `sql123.infinityfree.com` *(JANGAN gunakan localhost)*
   - **MySQL Port**: `3306`
   - **MySQL Username**: contoh `if0_12345678`
   - **MySQL Password**: Password akun hosting vPanel Anda *(dapat dilihat di Client Area)*
   - **MySQL Database Name**: contoh `if0_12345678_azumi`

---

### Langkah 2: Impor Skema Database via phpMyAdmin
1. Kembali ke vPanel InfinityFree, lalu klik menu **phpMyAdmin**.
2. Klik tombol **Connect** di samping database Anda (`if0_12345678_azumi`).
3. **PENTING**: Di panel sebelah kiri phpMyAdmin, klik nama database Anda agar database tersebut aktif.
4. Klik tab **Import** pada menu navigasi atas.
5. Klik tombol **Choose File / Browse**, lalu pilih file [`database/schema.sql`](file:///d:/web-rev/azumi-rv-php/database/schema.sql) dari komputer lokal Anda.
6. Gulir ke bawah dan klik tombol **Go / Kirim**.
7. Pastikan muncul notifikasi hijau tanda berhasil. Verifikasi bahwa 3 tabel telah terbuat:
   - `admins`
   - `film_submissions`
   - `upcoming_films`

---

### Langkah 3: Siapkan File Konfigurasi Database Hosting
Di komputer lokal Anda, buat file bernama `database.local.php` di dalam folder `config/` (atau gunakan template dari `config/database.local.example.php`) dengan isi sesuai data dari Langkah 1:

```php
<?php
/**
 * Konfigurasi Database InfinityFree
 */
define('DB_HOST', 'sql123.infinityfree.com'); // Sesuaikan dengan MySQL Hostname vPanel Anda
define('DB_PORT', '3306');
define('DB_USER', 'if0_12345678');            // Sesuaikan dengan MySQL Username Anda
define('DB_PASS', 'PasswordVpanelAnda');       // Password akun hosting vPanel
define('DB_NAME', 'if0_12345678_azumi');      // Nama database lengkap berawalan prefix
```

---

### Langkah 4: Unggah Berkas Website ke Hosting
Anda dapat menggunakan **File Manager** bawaan vPanel atau aplikasi FTP seperti **FileZilla**:

#### Menggunakan FileZilla (Rekomendasi):
1. Dapatkan detail FTP dari Client Area:
   - **Host**: `ftpupload.net`
   - **Username**: `if0_12345678`
   - **Password**: Password vPanel Anda
   - **Port**: `21`
2. Buka folder tujuan website:
   ```text
   /htdocs/
   ```
   *(Hapus file bawaan hosting seperti `default2.html` jika ada)*.
3. Unggah seluruh isi proyek ke dalam folder `/htdocs/`:
   - Folder: `admin/`, `assets/`, `config/`, `database/`, `includes/`, `uploads/`
   - File root: `.htaccess`, `index.php`, `about.php`, `reviews.php`, `review-detail.php`, `upcoming.php`, `submit.php`, `search.php`, `api-search.php`, `features.php`, `festivals.php`
4. **File yang TIDAK PERLU diunggah**:
   - Folder `tests/` (hanya untuk pengujian lokal)
   - File `.git/` atau `.gitignore`
   - File dokumentasi internal lainnya jika tidak dibutuhkan di server publik.
5. Pastikan folder `uploads/posters/` telah terunggah dengan permission `755` agar dapat ditulisi oleh PHP.
6. Pastikan file `config/database.local.php` yang telah Anda isi pada Langkah 3 berada di dalam `/htdocs/config/`.

---

### Langkah 5: Inisialisasi Akun Administrator Pertama
1. Buka browser dan akses alamat setup website Anda:
   ```text
   http://nama-domain-anda.infinityfreeapp.com/admin/setup.php
   ```
2. Karena tabel `admins` masih kosong, halaman setup akan terbuka secara aman.
3. Masukkan:
   - **Username Admin**: *(contoh: `azumi_editorial`)*
   - **Nama Lengkap / Redaksi**: *(contoh: `Redaksi Azumi Sinema`)*
   - **Password**: *(minimal 6 karakter, buat kombinasi yang kuat)*
   - **Konfirmasi Password**
4. Klik tombol **BUAT AKUN ADMINISTRATOR**.
5. Sistem akan menyimpan password dalam bentuk hash aman `password_hash()` dan mengalihkan Anda ke halaman login `/admin/login.php`.
6. **Keamanan Otomatis**: Setelah akun pertama terbuat, halaman `setup.php` otomatis terkunci dan tidak dapat diakses lagi oleh publik.

---

### Langkah 6: Verifikasi & Uji Fitur Website di Hosting
- Buka beranda publik: pastikan ulasan bawaan tampil dengan gambar yang sesuai.
- Coba fitur pencarian live di bilah navigasi.
- Buka halaman **Submit Your Film** (`submit.php`): coba kirim satu ulasan uji coba beserta berkas gambar poster.
- Masuk ke dashboard admin (`/admin/login.php`), verifikasi bahwa ulasan yang baru dikirim masuk ke daftar moderasi dengan status *Pending*.
- Setujui ulasan tersebut (*Approve*), lalu periksa kembali apakah ulasan langsung tampil di halaman depan website publik.

---

## Batasan Hosting InfinityFree & Solusinya

| Batasan InfinityFree | Dampak Potensial | Solusi yang Sudah Diterapkan di Proyek |
| :--- | :--- | :--- |
| **MySQL Hostname** | Bukan `localhost` / `127.0.0.1` | Sistem membaca `DB_HOST` dari `config/database.local.php` (`sqlXXX.infinityfree.com`). |
| **Hak Akses User MySQL** | Tidak memiliki hak `CREATE DATABASE` | Skrip `schema.sql` sudah disesuaikan tanpa perintah `CREATE DATABASE / USE`. Database dibuat manual di vPanel. |
| **Batas Ukuran Upload** | Maksimal 10 MB per file | Validasi server-side pada `submit.php` membatasi berkas poster maksimal **2 MB**. |
| **Waktu Eksekusi Skrip** | Maksimal 30 detik (PHP timeout) | Query dioptimalkan dengan indexing database (`idx_status`, `idx_submitted_at`) sehingga dieksekusi dalam fraksi milidetik. |
| **Lingkungan PHP-FPM** | Penggunaan `php_flag` di `.htaccess` dapat menyebabkan error HTTP 500 | Berkas `uploads/.htaccess` dioptimalkan menggunakan modul Apache modern (`mod_authz_core`) tanpa perintah `php_flag` yang berisiko. |
| **Akses Terminal / SSH** | Tidak tersedia akses SSH/CLI | Inisialisasi akun administrator disediakan melalui antarmuka web yang terlindungi di `/admin/setup.php`. |
| **HTTPS / Reverse Proxy** | Deteksi SSL bawaan server bisa tertunda | Fungsi helper sesi di [`includes/functions.php`](file:///d:/web-rev/azumi-rv-php/includes/functions.php) mendeteksi SSL langsung maupun header `HTTP_X_FORWARDED_PROTO`. |

---

## Prosedur Backup & Restore Data

### 1. Backup Database
- **Melalui phpMyAdmin (Rekomendasi Hosting & Laragon)**:
  1. Buka phpMyAdmin.
  2. Pilih database `azumi_reviews` (lokal) atau `if0_12345678_azumi` (hosting).
  3. Klik tab **Export**.
  4. Pilih metode **Quick** dan format **SQL**, lalu klik **Export**.
  5. Simpan file cadangan tersebut di lokasi yang aman di luar folder web root.
- **Melalui CLI (Lokal Laragon)**:
  ```bash
  mysqldump -u root azumi_reviews > database/backups/backup_local.sql
  ```

### 2. Backup Berkas Unggahan Gambar
- Unduh salinan direktori:
  ```text
  uploads/posters/
  ```
  Folder ini berisi seluruh poster film yang diunggah oleh pengguna dan disetujui oleh redaksi.

### 3. Prosedur Restore Data
- Untuk memulihkan database: Buat database baru di phpMyAdmin, lalu gunakan tab **Import** untuk memasukkan file `.sql` cadangan Anda.
- Untuk memulihkan gambar: Unggah kembali file poster ke direktori `uploads/posters/`.

---

## Keamanan & Panduan GitHub Private

Proyek ini telah disiapkan agar **100% aman untuk disimpan di repository GitHub berstatus Private**:

1. **Inisialisasi Git Lokal**:
   Jika Anda hendak menyimpan proyek ini ke Git secara mandiri:
   ```bash
   git init
   git add .
   git commit -m "feat: initial commit of Azumi Movie Reviews"
   ```

2. **Perlindungan Data Sensitif**:
   Aturan [`.gitignore`](file:///d:/web-rev/azumi-rv-php/.gitignore) yang telah disiapkan memastikan hal-hal berikut **TIDAK AKAN PERNAH** terdorong (*pushed*) ke GitHub:
   - Kredensial aktif di server (`config/database.local.php` dan file `.env`).
   - File upload dinamis hasil pengujian di `uploads/posters/*`.
   - File dump database cadangan (`*.sql.gz`, `database/backups/`).
   - File log server dan file sementara sistem operasi.

3. **Status Repository**:
   - Selalu atur visibilitas repository GitHub Anda sebagai **Private**.
   - Jangan pernah menyertakan kredensial nyata cPanel, password FTP, atau password database di dalam commit pesan atau file publik.

---

*Hak Cipta &copy; 2026 Azumi Movie Reviews. Dibangun untuk memajukan apresiasi sinema independen Asia.*
