# AGENTS.md — CozyCafe Development & Deployment Guide

Dokumen ini adalah panduan instruksi bertahap (*step-by-step prompts*) bagi AI Coding Agent untuk membangun aplikasi **CozyCafe — Online Ordering & Table Reservation System** secara utuh dari nol hingga *deployment* menggunakan **Dokploy**, berdasarkan spesifikasi pada `PRD.md`.

---

## Context & System Rules (Wajib Dipatuhi Agent)

Sebelum mengeksekusi langkah mana pun, pahami aturan arsitektur dan batasan berikut:
1. **Tech Stack:** Native PHP 8.x (OOP), MySQL 8.0, HTML5, CSS3, Vanilla JavaScript, dan Bootstrap 5 (via CDN). **Dilarang** menggunakan framework PHP (seperti Laravel/CodeIgniter) atau Composer.
2. **Keamanan:**
   - Gunakan **PDO** dengan *prepared statements* untuk semua interaksi database.
   - Gunakan `password_hash()` dan `password_verify()` untuk autentikasi.
   - Terapkan proteksi **CSRF Token** pada semua form mutasi data (`POST`).
   - Escape seluruh output HTML menggunakan `htmlspecialchars()` untuk mencegah XSS.
   - Terapkan *Role-Based Access Control* (RBAC) berbasis `$_SESSION` untuk memisahkan akses `customer` dan `admin`.
3. **Design System ("Modern Cozy"):**
   - Font: `Poppins` (Google Fonts).
   - Palette: Coffee Brown (`#6F4E37`), Cream (`#F5EFE6`), Warm White (`#FFFCF7`), Caramel (`#C69C6D`), Dark Text (`#29231F`).
4. **Deployment Target (Dokploy):**
   - Aplikasi akan di-deploy menggunakan **Docker** (Apache + PHP + PDO MySQL) dan database MySQL terpisah di dalam jaringan Dokploy.
   - Konfigurasi database harus mendukung *Environment Variables* (`getenv()`) dengan *fallback* ke pengaturan lokal XAMPP agar berjalan mulus di lokal maupun produksi.

---

## Step-by-Step Execution Prompts

Jalankan prompt berikut secara berurutan. Pastikan setiap tahap selesai dan terverifikasi sebelum melanjutkan ke tahap berikutnya.

---

### Tahap 1: Inisialisasi Struktur Proyek & Konfigurasi Environment

> **Prompt 1:**
> "Buat struktur direktori proyek `cozycafe` sesuai spesifikasi berikut:
> ```text
> cozycafe/
> ├── app/
> │   ├── Controllers/
> │   ├── Models/
> │   ├── Core/
> │   └── Helpers/
> ├── config/
> │   └── database.php
> ├── public/
> │   ├── assets/
> │   │   ├── css/
> │   │   ├── js/
> │   │   └── images/
> │   ├── uploads/
> │   ├── .htaccess
> │   └── index.php
> ├── views/
> │   ├── layouts/
> │   ├── auth/
> │   ├── customer/
> │   └── admin/
> ├── database/
> │   └── cozycafe.sql
> ├── Dockerfile
> ├── docker-compose.yml
> └── README.md
> ```
> Buat juga file `config/database.php` menggunakan class `Database` berbasis PDO singleton yang membaca konfigurasi dari environment variables (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) dengan nilai default untuk XAMPP lokal (`localhost`, `3306`, `cozycafe`, `root`, `""`). Pastikan folder `public/uploads/` memiliki file `.gitkeep`."

---

### Tahap 2: Perancangan Schema Database & Seeder

> **Prompt 2:**
> "Buat file `database/cozycafe.sql` yang berisi skema lengkap untuk 15 tabel berikut dengan tipe data yang presisi, Primary Key, Foreign Key dengan constraint yang tepat, serta index untuk optimasi query:
> 1. `users` (`id`, `name`, `email` UNIQUE, `phone`, `password`, `role` ENUM('customer','admin'), `created_at`, `updated_at`)
> 2. `categories` (`id`, `name`, `description`, `created_at`)
> 3. `menus` (`id`, `category_id`, `name`, `description`, `price` DECIMAL(10,2), `image`, `is_available` TINYINT(1), `created_at`, `updated_at`)
> 4. `tables` (`id`, `table_number` UNIQUE, `capacity` INT, `is_active` TINYINT(1), `created_at`)
> 5. `operating_hours` (`id`, `day_of_week` TINYINT [0=Minggu..6=Sabtu], `open_time` TIME, `close_time` TIME, `is_closed` TINYINT(1))
> 6. `reservation_closures` (`id`, `start_date` DATE, `end_date` DATE, `reason`, `created_at`)
> 7. `bookings` (`id`, `user_id`, `table_id`, `booking_date` DATE, `start_time` TIME, `end_time` TIME, `guest_count` INT, `notes` TEXT, `status` ENUM('Confirmed','Completed','Cancelled'), `created_at`)
> 8. `carts` (`id`, `user_id` UNIQUE, `created_at`, `updated_at`)
> 9. `cart_items` (`id`, `cart_id`, `menu_id`, `quantity` INT, `notes` VARCHAR(255))
> 10. `orders` (`id`, `order_code` UNIQUE, `user_id`, `booking_id` NULLABLE, `order_type` ENUM('Dine-in','Takeaway'), `subtotal` DECIMAL(10,2), `discount_amount` DECIMAL(10,2), `total_amount` DECIMAL(10,2), `status` ENUM('Pending','Confirmed','Preparing','Ready','Completed','Cancelled'), `created_at`, `updated_at`)
> 11. `order_items` (`id`, `order_id`, `menu_id`, `menu_name_snapshot`, `price_snapshot` DECIMAL(10,2), `quantity` INT, `notes` VARCHAR(255), `subtotal` DECIMAL(10,2))
> 12. `payments` (`id`, `order_id` UNIQUE, `payment_method` VARCHAR(50) DEFAULT 'Cash', `amount` DECIMAL(10,2), `status` ENUM('Unpaid','Paid'), `paid_at` DATETIME NULLABLE)
> 13. `promotions` (`id`, `name`, `description`, `discount_type` ENUM('percentage','fixed'), `discount_value` DECIMAL(10,2), `start_date` DATE, `end_date` DATE, `is_active` TINYINT(1), `created_at`)
> 14. `promotion_menus` (`id`, `promotion_id`, `menu_id`)
> 15. `notifications` (`id`, `user_id`, `title`, `message`, `is_read` TINYINT(1) DEFAULT 0, `link` VARCHAR(255) NULLABLE, `created_at`)
>
> Sertakan data awal (seeder) di dalam `cozycafe.sql`:
> - 1 akun Admin default (`admin@cozycafe.com` / password: `password123` yang sudah di-hash).
> - 1 akun Customer contoh (`customer@cozycafe.com` / password: `password123`).
> - 5 kategori wajib: `Coffee`, `Non-Coffee`, `Foods`, `Snacks`, `Desserts`.
> - Jam operasional default untuk 7 hari (08:00 - 22:00).
> - 6 data meja contoh (T01 - T06 dengan variasi kapasitas 2, 4, dan 6 orang)."

---

### Tahap 3: Core Framework OOP, Routing, & Security Helpers

> **Prompt 3:**
> "Bangun fondasi aplikasi Native PHP OOP di folder `app/Core/` dan `app/Helpers/`:
> 1. `app/Core/Router.php`: Sistem routing bersih (Clean URLs) yang memetakan request `GET` dan `POST` ke Controller dan Method yang sesuai, didukung oleh `public/.htaccess`.
> 2. `app/Core/Controller.php`: Base controller dengan helper `view($viewPath, $data = [])`, `redirect($url)`, dan `jsonResponse($data, $statusCode)`.
> 3. `app/Core/Model.php`: Base model yang menginisialisasi koneksi PDO dari `Database::getInstance()`.
> 4. `app/Helpers/SecurityHelper.php`: Fungsi statis untuk generate & validasi CSRF Token (`generateCsrfToken()`, `verifyCsrfToken()`), sanitasi output (`e($string)`), serta validasi upload gambar (hanya JPG/PNG/WEBP, maksimal 2MB).
> 5. `app/Helpers/AuthHelper.php`: Middleware/helper untuk memeriksa status login (`requireLogin()`, `requireAdmin()`, `requireCustomer()`, `currentUser()`).
> 6. `app/Helpers/FlashHelper.php`: Sistem flash message berbasis session untuk menampilkan alert sukses/error di UI.
> 7. Konfigurasikan `public/index.php` sebagai Front Controller tunggal yang memuat autoloader SPL sederhana (`spl_autoload_register`), memulai session aman, dan menjalankan Router."

---

### Tahap 4: UI Design System & Base Layouts

> **Prompt 4:**
> "Buat sistem tampilan yang responsif dan konsisten dengan konsep **Modern Cozy** di `views/layouts/` dan `public/assets/`:
> 1. `public/assets/css/style.css`: Definisikan CSS variables (`--coffee-brown: #6F4E37`, `--cream: #F5EFE6`, `--warm-white: #FFFCF7`, `--caramel: #C69C6D`, `--dark-text: #29231F`), terapkan font `Poppins`, styling custom untuk card, tombol tema kopi, badge status pesanan, dan sidebar admin.
> 2. `views/layouts/customer_layout.php`: Layout utama untuk pelanggan dengan Navbar responsif (link ke Home, Menu, Reservation, My Orders, My Bookings, ikon Cart dengan badge jumlah item, ikon Notifikasi dengan badge belum dibaca, dan dropdown Profile/Logout) serta Footer bertema CozyCafe (*'A Cozy Place, A Better Taste'*).
> 3. `views/layouts/admin_layout.php`: Layout dashboard Admin dengan Sidebar navigasi untuk 12 menu wajib (Dashboard, Manage Menu, Manage Categories, Manage Orders, Manage Bookings, Manage Tables, Manage Customers, Manage Promotions, Operating Hours, Reservation Closures, Reports, Profile) serta Topbar dengan lonceng notifikasi Admin."

---

### Tahap 5: Sistem Autentikasi & Manajemen Profil

> **Prompt 5:**
> "Implementasikan fitur autentikasi dan manajemen profil:
> 1. Buat `app/Models/User.php` dan `app/Controllers/AuthController.php`.
> 2. Buat view `views/auth/login.php` dan `views/auth/register.php`.
> 3. Alur Login: Cek email & verifikasi password. Jika role `admin`, arahkan ke `/admin/dashboard`; jika `customer`, arahkan ke `/`.
> 4. Alur Register: Hanya membuat akun dengan role `customer`. Validasi email unik dan konfirmasi password.
> 5. Buat `views/customer/profile.php` dan `views/admin/profile.php` agar kedua role dapat memperbarui nama, nomor telepon, dan mengganti password mereka (wajib verifikasi password lama)."

---

### Tahap 6: Katalog Menu & Admin Management (Menu, Kategori, Meja)

> **Prompt 6:**
> "Bangun fitur pengelolaan master data dan katalog publik:
> 1. **Admin CRUD Categories (`AdminCategoryController`):** Tambah, edit, hapus kategori (cegah hapus jika masih memiliki menu).
> 2. **Admin CRUD Menus (`AdminMenuController`):** Tambah, edit, hapus/nonaktifkan menu, pilih kategori, atur harga, deskripsi, status ketersediaan, serta upload foto menu ke `public/uploads/` dengan penamaan unik.
> 3. **Admin CRUD Tables (`AdminTableController`):** Tambah meja, ubah nomor meja, kapasitas, dan toggle status aktif/nonaktif.
> 4. **Admin Manage Customers (`AdminCustomerController`):** Melihat daftar pelanggan terdaftar beserta total pesanan dan reservasinya.
> 5. **Customer Public Pages (`HomeController` & `MenuController`):**
>    - `views/customer/home.php`: Hero banner CozyCafe, menu unggulan/promo aktif, dan tombol cepat menuju Order/Reservation.
>    - `views/customer/menu.php`: Filter berdasarkan kategori, pencarian nama menu, tampilan badge diskon jika sedang promo, dan tombol 'Add to Cart'.
>    - `views/customer/menu_detail.php`: Detail lengkap menu, info promo aktif, input catatan item, dan jumlah."

---

### Tahap 7: Jam Operasional, Penutupan Reservasi, & Sistem Reservasi Meja (Anti Double-Booking)

> **Prompt 7:**
> "Implementasikan logika bisnis reservasi meja yang ketat sesuai aturan PRD:
> 1. **Admin Operating Hours (`AdminOperatingHourController`):** Form untuk mengatur jam buka, jam tutup, dan status libur (`is_closed`) untuk setiap hari (Senin - Minggu).
> 2. **Admin Reservation Closures (`AdminClosureController`):** CRUD periode penutupan reservasi (`start_date`, `end_date`, `reason`).
> 3. **Reservation Engine (`BookingService` / `BookingModel`):**
>    - Durasi reservasi baku adalah **2 jam** (`end_time = start_time + 2 hours`).
>    - Validasi 1: Tanggal tidak boleh di masa lalu dan tidak boleh masuk dalam rentang `reservation_closures`.
>    - Validasi 2: Hari tersebut tidak boleh libur pada `operating_hours`, dan `start_time` hingga `end_time` harus berada dalam rentang `open_time` dan `close_time`.
>    - Validasi 3: Meja harus berstatus aktif dan `capacity >= guest_count`.
>    - Validasi 4 (Anti Double-Booking): Cek apakah meja tersebut memiliki booking aktif (`status = 'Confirmed'`) pada tanggal yang sama di mana waktu bertabrakan (`existing.start_time < new.end_time AND existing.end_time > new.start_time`). Booking berurutan diperbolehkan (misal: 10:00-12:00 dan 12:00-14:00 tidak dianggap tabrakan).
>    - Gunakan **Database Transaction (`beginTransaction()`, `commit()`, `rollBack()`)** saat menyimpan reservasi untuk mencegah *race condition*.
> 4. **Customer Reservation UI (`views/customer/reservation.php` & `my_bookings.php`):** Form interaktif (gunakan AJAX/Fetch API untuk mengecek ketersediaan meja secara real-time saat tanggal, jam, dan jumlah tamu dipilih), pembuatan reservasi mandiri, dan riwayat reservasi pelanggan."

---

### Tahap 8: Manajemen Promosi & Shopping Cart (Server-Side Calculation)

> **Prompt 8:**
> "Bangun sistem promosi dan keranjang belanja yang aman dari manipulasi harga:
> 1. **Admin Promotions (`AdminPromotionController`):** CRUD promosi (`percentage` atau `fixed`), tanggal berlaku (`start_date` - `end_date`), status aktif, dan checklist pemilihan menu yang mendapatkan promo (`promotion_menus`).
> 2. **Shopping Cart (`CartController` & `CartModel`):**
>    - Simpan keranjang di database (`carts` & `cart_items`) per user yang login.
>    - Fitur: Tambah item, update kuantitas (`+`/`-`), hapus item, dan simpan catatan per item.
>    - Perhitungan harga wajib dilakukan di server: Ambil harga asli dari tabel `menus`, cek apakah menu memiliki promosi aktif pada hari ini di `promotions`, hitung nominal diskon per item, pastikan total tidak pernah negatif (`max(0, total)`), lalu tampilkan rincian Subtotal, Diskon, dan Total Akhir di `views/customer/cart.php`."

---

### Tahap 9: Sistem Pemesanan (Dine-in & Takeaway) & Pembayaran Tunai

> **Prompt 9:**
> "Implementasikan alur Checkout, Manajemen Pesanan, dan Pembayaran Tunai:
> 1. **Checkout Flow (`CheckoutController` & `views/customer/checkout.php`):**
>    - Pelanggan memilih jenis pesanan: `Takeaway` atau `Dine-in`.
>    - Jika memilih `Dine-in`, pelanggan **wajib** memilih salah satu reservasi aktif miliknya (`Confirmed` untuk hari ini/mendatang) ATAU langsung mengisi form reservasi meja baru di halaman checkout yang akan diproses bersamaan.
>    - Gunakan **Database Transaction** saat checkout: Simpan data ke `orders` (generate `order_code` unik seperti `ORD-YYYYMMDD-XXXX`), salin detail item beserta *snapshot* nama & harga saat itu ke `order_items`, buat record di `payments` dengan metode `Cash` dan status `Unpaid`, lalu kosongkan `cart_items` pelanggan.
> 2. **Customer Orders (`views/customer/my_orders.php`):** Menampilkan daftar pesanan, rincian item snapshot, status pembayaran (`Unpaid`/`Paid`), dan pelacakan status pesanan (`Pending` -> `Confirmed` -> `Preparing` -> `Ready` -> `Completed` / `Cancelled`).
> 3. **Admin Order, Booking & Payment Management:**
>    - `AdminOrderController`: Melihat semua pesanan, filter berdasarkan status, ubah status pesanan, dan tombol **Confirm Cash Payment** untuk mengubah status pembayaran menjadi `Paid` (mencatat `paid_at`).
>    - `AdminBookingController`: Melihat daftar reservasi meja, menandai reservasi `Completed` atau `Cancelled`."

---

### Tahap 10: Sistem Notifikasi, Dashboard Statistik, & Laporan

> **Prompt 10:**
> "Lengkapi aplikasi dengan sistem notifikasi internal, ringkasan dashboard, dan laporan:
> 1. **Notification System (`NotificationHelper` & `NotificationController`):**
>    - Trigger otomatis simpan notifikasi ke database:
>      - Ke **Admin** saat ada pesanan baru atau reservasi baru.
>      - Ke **Customer** saat status pesanan berubah, reservasi berhasil dikonfirmasi, atau reservasi dibatalkan.
>    - Halaman `views/customer/notifications.php` dan dropdown notifikasi di navbar/topbar dengan fitur 'Tandai sudah dibaca' dan 'Tandai semua dibaca'.
> 2. **Admin Dashboard (`AdminDashboardController`):** Tampilkan kartu statistik (Total Pesanan, Total Reservasi, Total Pelanggan, Total Pendapatan Paid), serta tabel 5 Pesanan Terbaru dan 5 Reservasi Terbaru.
> 3. **Admin Reports (`AdminReportController` & `views/admin/reports.php`):**
>    - Filter berdasarkan rentang tanggal (`start_date` hingga `end_date`).
>    - Tampilkan ringkasan total pendapatan (dari pesanan `Paid`), total transaksi, total reservasi, daftar transaksi pada periode tersebut, dan tabel **Top 5 Menu Terlaris** berdasarkan kuantitas terjual. Sertakan tombol cetak/print-friendly CSS."

---

### Tahap 11: Persiapan Docker & Deployment ke Dokploy

> **Prompt 11:**
> "Siapkan konfigurasi containerization agar aplikasi CozyCafe siap di-deploy ke **Dokploy**:
> 1. Buat `Dockerfile` berbasis `php:8.2-apache`:
>    - Install ekstensi PHP `pdo` dan `pdo_mysql`.
>    - Aktifkan Apache `mod_rewrite`.
>    - Atur `DocumentRoot` Apache ke `/var/www/html/public`.
>    - Salin seluruh kode proyek ke `/var/www/html` dan atur kepemilikan folder `public/uploads` ke `www-data:www-data` dengan izin tulis (`775`).
> 2. Buat `docker-compose.yml` untuk pengujian container secara lokal maupun referensi Dokploy (terdiri dari service `app` dan `db` MySQL 8.0, dengan volume persisten untuk `mysql_data` dan `uploads_data`, serta auto-import `database/cozycafe.sql` pada inisialisasi pertama).
> 3. Buat `.dockerignore` (abaikan `.git`, `README.md`, dll.) dan perbarui `README.md` dengan panduan instalasi lokal (XAMPP) serta panduan langkah demi langkah deploy ke Dokploy."

---

## Panduan Deployment ke Dokploy (Checklist Operasional)

Setelah seluruh kode selesai dibangun dan di-push ke repositori Git (GitHub/GitLab), ikuti langkah berikut di dashboard **Dokploy**:

1. **Buat Project Baru:**
   - Masuk ke dashboard Dokploy -> klik **Create Project** -> beri nama `CozyCafe`.
2. **Deploy Database MySQL:**
   - Di dalam project `CozyCafe`, klik **Create Service** -> pilih **Database** -> **MySQL**.
   - Catat kredensial internal yang dibuat (misal: Service Name/Host: `cozycafe-db`, Database: `cozycafe`, User: `cozycafe_user`, Password: `<strong_password>`).
   - Jalankan/Deploy service database, lalu import isi file `database/cozycafe.sql` melalui terminal/client database atau fitur koneksi eksternal.
3. **Deploy Aplikasi PHP (CozyCafe Web):**
   - Klik **Create Service** -> pilih **Application**.
   - Hubungkan repositori Git proyek `CozyCafe` (pilih branch `main`).
   - Pada bagian **Build Type**, pilih **Dockerfile** (pastikan path mengarah ke `./Dockerfile`).
4. **Konfigurasi Environment Variables:**
   - Buka tab **Environment** pada aplikasi `CozyCafe`, lalu tambahkan variabel berikut sesuai kredensial database di langkah 2:
     ```env
     DB_HOST=cozycafe-db
     DB_PORT=3306
     DB_NAME=cozycafe
     DB_USER=cozycafe_user
     DB_PASS=<strong_password>
     APP_ENV=production
     ```
5. **Konfigurasi Persistent Storage (Wajib untuk Foto Menu):**
   - Buka tab **Mounts / Volumes** pada aplikasi `CozyCafe`.
   - Tambahkan Volume Mount agar foto menu yang diunggah Admin tidak hilang saat aplikasi di-redeploy:
     - **Volume Name:** `cozycafe-uploads`
     - **Mount Path (Container):** `/var/www/html/public/uploads`
6. **Konfigurasi Domain & SSL:**
   - Buka tab **Domains**, tambahkan domain/subdomain Anda (misal: `cozycafe.domainanda.com`), set **Container Port** ke `80`, dan aktifkan **HTTPS (Let's Encrypt)**.
7. **Deploy & Verifikasi Akhir:**
   - Klik **Deploy** dan pantau log build hingga berstatus *Running*.
   - Akses domain aplikasi, uji login Admin (`admin@cozycafe.com`), ubah password default Admin, uji upload foto menu, dan lakukan simulasi reservasi serta checkout pesanan.