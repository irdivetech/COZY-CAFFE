<!--  --># CozyCafe — Sistem Pemesanan Online & Reservasi Meja

CozyCafe adalah aplikasi berbasis web dengan arsitektur **Native PHP 8.2 (OOP)**, **MySQL 8.0**, dan **Apache** untuk pemesanan makanan/minuman online serta reservasi meja cafe secara real-time. Aplikasi ini dibangun tanpa framework PHP (seperti Laravel/CodeIgniter) dan tanpa Composer sesuai panduan PRD.

---

## Fitur Utama

- **Customer:**
  - Pendaftaran akun & login pelanggan.
  - Katalog menu dengan pencarian dan filter kategori (Coffee, Non-Coffee, Foods, Snacks, Desserts).
  - Keranjang belanja (Shopping Cart) dengan kalkulasi harga dan diskon promo di sisi server.
  - Alur Checkout untuk **Takeaway** (Bawa Pulang) dan **Dine-in** (Makan di Tempat dengan wajib memilih reservasi meja).
  - Reservasi meja mandiri dengan mesin **Anti Double-Booking** (durasi 2 jam, validasi kapasitas, pengecekan jam operasional, dan penutupan reservasi).
  - Riwayat pesanan & pelacakan status pesanan real-time (*Pending* → *Confirmed* → *Preparing* → *Ready* → *Completed* / *Cancelled*).
  - Riwayat reservasi meja.
  - Sistem notifikasi internal di dalam web.
  - Pengelolaan profil dan perubahan kata sandi.

- **Admin (Dashboard):**
  - Ringkasan statistik operasional: Total Pesanan, Total Reservasi, Total Pelanggan, dan Pendapatan Lunas (*Paid*).
  - Kelola Menu (CRUD, upload foto JPG/PNG/WEBP maks 2MB, atur harga, status ketersediaan).
  - Kelola Kategori (Coffee, Non-Coffee, Foods, Snacks, Desserts).
  - Kelola Pesanan (filter status, pembaruan status pesanan, dan konfirmasi pembayaran tunai/Cash).
  - Kelola Reservasi (daftar booking, ubah status ke *Completed* atau *Cancelled*).
  - Kelola Meja (tambah meja, kapasitas kursi, toggle aktif/nonaktif).
  - Kelola Pelanggan (daftar pelanggan beserta total transaksi dan booking).
  - Kelola Promosi (diskon persentase atau nominal tetap, periode promo, checklist menu promo).
  - Kelola Jam Operasional (jam buka, jam tutup, dan status libur tiap hari Minggu s.d Sabtu).
  - Kelola Penutupan Reservasi (tutup reservasi pada rentang tanggal tertentu untuk libur/renovasi/acara).
  - Laporan Penjualan & Operasional (filter tanggal, total omzet lunas, Top 5 Menu Terlaris, dan tabel transaksi ramah cetak/print-friendly).
  - Notifikasi Admin & Pengelolaan Profil Admin.

---

## Kebutuhan Sistem

- **Lokal (XAMPP):**
  - PHP 8.0 - 8.2+ dengan ekstensi `pdo` dan `pdo_mysql` aktif.
  - MySQL 8.0+ / MariaDB.
  - Web browser modern.
- **Container (Docker):**
  - Docker Desktop atau Docker Engine dengan plugin Docker Compose.

---

## Menjalankan Proyek Secara Lokal (XAMPP)

1. Salin folder proyek ini ke dalam direktori `htdocs` XAMPP, contoh: `C:\xampp\htdocs\cozycafe`.
2. Buka **XAMPP Control Panel**, lalu jalankan service **Apache** dan **MySQL**.
3. Buka **phpMyAdmin** (`http://localhost/phpmyadmin/`).
4. Import file database: `database/cozycafe.sql`. File ini otomatis membuat database `cozycafe`, seluruh 15 tabel, dan data seeder awal.
5. Konfigurasi database default di `config/database.php` menggunakan:
   - Host: `localhost`
   - Port: `3306`
   - Database: `cozycafe`
   - User: `root`
   - Password: `""` (kosong)
   *(Jika menggunakan kredensial berbeda, sesuaikan environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`).*
6. Buka aplikasi di browser:
   ```
   http://localhost/cozycafe/public/
   ```

### Akun Awal (Seeder Default)

- **Akun Admin:**
  - Email: `admin@cozycafe.com`
  - Password: `password123`
  - URL Akses: `http://localhost/cozycafe/public/login` (otomatis diarahkan ke `/admin/dashboard`)
- **Akun Pelanggan:**
  - Email: `customer@cozycafe.com`
  - Password: `password123`

> **Catatan Keamanan:** Segera ubah kata sandi akun Admin setelah berhasil login pertama kali melalui menu **Profil Admin**.

---

## Menjalankan Proyek dengan Docker Compose

1. Buka terminal pada root direktori proyek.
2. Buat file `.env` jika ingin mengganti kredensial database lokal:
   ```env
   DB_USER=cozycafe_user
   DB_PASS=cozycafe_local_password
   MYSQL_ROOT_PASSWORD=cozycafe_root_local_password
   APP_PORT=8080
   ```
3. Bangun (*build*) dan jalankan container:
   ```powershell
   docker compose up --build -d
   ```
4. Buka aplikasi di browser:
   ```
   http://localhost:8080/
   ```
5. Foto menu yang diunggah dan database MySQL akan tersimpan secara persisten pada Docker Volume (`uploads_data` dan `mysql_data`).
6. Untuk menghentikan container:
   ```powershell
   docker compose down
   ```

---

## Panduan Deployment ke Dokploy

Aplikasi CozyCafe telah dilengkapi dengan `Dockerfile` dan konfigurasi *cloud-ready* sehingga siap dideploy ke **Dokploy**.

### Langkah 1: Buat Project & Database MySQL di Dokploy
1. Masuk ke dashboard Dokploy, klik **Create Project** dan beri nama `CozyCafe`.
2. Klik **Create Service** → pilih **Database** → pilih **MySQL**.
3. Catat kredensial yang dibuat oleh Dokploy:
   - Internal Host: (misal: `cozycafe-db`)
   - Database: `cozycafe`
   - User: `cozycafe_user`
   - Password: `<password-database>`
4. Jalankan service database, lalu import file `database/cozycafe.sql` ke database tersebut.

### Langkah 2: Deploy Aplikasi CozyCafe Web
1. Klik **Create Service** → pilih **Application**.
2. Hubungkan repositori Git proyek CozyCafe (pilih branch `main`).
3. Pada opsi **Build Type**, pilih **Dockerfile** (path: `./Dockerfile`).
4. Masuk ke tab **Environment**, lalu tambahkan konfigurasi database:
   ```env
   DB_HOST=cozycafe-db
   DB_PORT=3306
   DB_NAME=cozycafe
   DB_USER=cozycafe_user
   DB_PASS=<password-database>
   APP_ENV=production
   ```
5. Masuk ke tab **Mounts / Volumes**, tambahkan volume persisten agar foto menu tidak hilang saat redeploy:
   - **Type:** Volume
   - **Name:** `cozycafe-uploads`
   - **Path (Container):** `/var/www/html/public/uploads`
6. Masuk ke tab **Domains**, tentukan domain/subdomain Anda (misal `cafe.domainanda.com`), arahkan Container Port ke `80`, dan aktifkan sertifikat SSL (Let's Encrypt).
7. Klik **Deploy** dan pantau log build hingga aplikasi berstatus *Running*.
