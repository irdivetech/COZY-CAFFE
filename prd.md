# PRODUCT REQUIREMENTS DOCUMENT (PRD)
# CozyCafe — Online Ordering & Table Reservation System

**Versi:** 1.0  
**Status:** Final — Siap untuk Pengembangan  
**Platform:** Responsive Website  
**Backend:** Native PHP OOP  
**Database:** MySQL  
**Frontend:** HTML, CSS, JavaScript, Bootstrap 5  
**Local Development:** XAMPP dan Visual Studio Code

---

## 1. Product Overview

### 1.1 Nama Produk
**CozyCafe**

### 1.2 Tagline
*A Cozy Place, A Better Taste.*

### 1.3 Deskripsi Produk
CozyCafe merupakan aplikasi website untuk mempermudah pelanggan melihat menu, melakukan pemesanan makanan dan minuman, serta melakukan reservasi meja secara online.

Admin dapat mengelola menu, kategori, pesanan, reservasi, meja, pelanggan, promosi, jam operasional, dan laporan melalui dashboard khusus.

Website dirancang responsif sehingga dapat digunakan melalui smartphone, tablet, maupun desktop.

### 1.4 Tujuan Produk
1. Memudahkan pelanggan melihat menu dan harga.
2. Mempercepat proses pemesanan makanan dan minuman.
3. Mempermudah pelanggan melakukan reservasi meja.
4. Mengurangi kesalahan pencatatan pesanan dan reservasi.
5. Membantu Admin mengelola operasional cafe.
6. Menyediakan informasi status pesanan secara online.

---

## 2. Target Users

CozyCafe memiliki dua jenis pengguna.

### 2.1 Customer
Pelanggan yang menggunakan website untuk:
- Registrasi dan login.
- Melihat menu dan harga.
- Memilih makanan dan minuman.
- Menggunakan keranjang belanja.
- Memesan makanan untuk dine-in atau takeaway.
- Melakukan reservasi meja.
- Melihat status pesanan.
- Melihat riwayat pesanan dan reservasi.
- Menerima notifikasi.

### 2.2 Admin
Pengelola cafe yang menggunakan dashboard untuk:
- Mengelola menu dan kategori.
- Mengelola pesanan.
- Mengelola reservasi.
- Mengelola meja.
- Mengelola pelanggan.
- Mengelola promosi.
- Mengatur jam operasional.
- Mengatur penutupan reservasi.
- Melihat laporan.
- Mengelola profil Admin.

Registrasi publik hanya tersedia untuk Customer. Akun Admin dibuat melalui proses pengelolaan database atau oleh Admin yang berwenang.

---

## 3. Problems & Solutions

| Masalah | Solusi CozyCafe |
|---|---|
| Pelanggan kesulitan melihat menu dan harga | Menu online dengan kategori |
| Pemesanan masih dilakukan secara manual | Sistem pemesanan online |
| Ketersediaan meja sulit diketahui | Sistem reservasi meja |
| Reservasi melalui WhatsApp atau telepon | Reservasi langsung melalui website |
| Pelanggan tidak mengetahui status pesanan | Fitur pelacakan status pesanan |
| Admin kesulitan mencatat transaksi | Dashboard pengelolaan pesanan |
| Informasi promosi tidak terpusat | Sistem promosi online |

---

## 4. Scope & Product Rules

### 4.1 Jenis Pemesanan

CozyCafe mendukung dua jenis pemesanan:

**Dine-in**
- Pelanggan makan di cafe.
- Pelanggan wajib memilih atau melakukan reservasi meja sebelum checkout.
- Pesanan terhubung dengan reservasi meja.

**Takeaway**
- Pelanggan memesan makanan untuk dibawa pulang.
- Tidak memerlukan reservasi meja.

CozyCafe versi pertama tidak mendukung layanan delivery.

### 4.2 Metode Pembayaran
- Pembayaran hanya menggunakan uang tunai.
- Pembayaran dilakukan di kasir cafe.
- Status awal pembayaran adalah Unpaid.
- Admin dapat mengubah status menjadi Paid setelah menerima pembayaran.

### 4.3 Durasi Reservasi
- Setiap reservasi berlangsung selama 2 jam.
- Pelanggan memilih tanggal, jam, jumlah orang, dan meja.
- Sistem memeriksa ketersediaan meja.
- Reservasi otomatis dikonfirmasi jika tersedia.
- Sistem wajib memeriksa ulang ketersediaan saat reservasi disimpan.

### 4.4 Jam Operasional
- Jam operasional dapat diatur Admin.
- Admin dapat mengatur jam buka dan tutup.
- Admin dapat mengatur hari operasional dan hari libur.
- Pelanggan hanya dapat melakukan reservasi pada jam yang tersedia.

### 4.5 Penutupan Reservasi
Admin dapat menutup reservasi:
- Pada tanggal tertentu.
- Pada rentang tanggal tertentu.
- Karena acara khusus.
- Karena renovasi.
- Karena kebutuhan operasional cafe.

Pelanggan tidak dapat membuat reservasi pada periode yang ditutup.

---

## 5. Functional Requirements

### 5.1 Authentication

**Customer**
- Registrasi akun.
- Login.
- Logout.
- Mengubah profil.
- Mengubah password.

**Admin**
- Login.
- Logout.
- Mengubah profil.
- Mengubah password.

Persyaratan:
- Password disimpan menggunakan password hashing.
- Session digunakan untuk autentikasi.
- Halaman Admin hanya dapat diakses oleh Admin.

### 5.2 Menu Management

Admin dapat:
- Menambah menu.
- Mengubah menu.
- Menghapus atau menonaktifkan menu.
- Mengunggah foto menu.
- Mengatur harga.
- Mengatur deskripsi.
- Mengatur kategori.
- Mengatur ketersediaan menu.

Kategori menu:
1. Coffee
2. Non-Coffee
3. Foods
4. Snacks
5. Desserts

Customer dapat melihat menu berdasarkan kategori.

### 5.3 Shopping Cart

Customer dapat:
- Menambahkan menu ke keranjang.
- Mengurangi jumlah menu.
- Menambah jumlah menu.
- Menghapus menu.
- Menambahkan catatan untuk setiap item.
- Melihat subtotal.
- Melihat total pembayaran.
- Melanjutkan ke checkout.

Harga harus dihitung ulang di server untuk mencegah manipulasi harga dari browser.

### 5.4 Order Management

Alur pemesanan:

1. Customer memilih menu.
2. Customer menambahkan menu ke keranjang.
3. Customer memilih jenis pesanan.
4. Jika Dine-in, Customer wajib memilih meja dan membuat reservasi.
5. Customer memeriksa ringkasan pesanan.
6. Customer melakukan checkout.
7. Pesanan masuk ke dashboard Admin.
8. Admin memproses pesanan.
9. Customer dapat melihat status pesanan.

Status pesanan:

- Pending
- Confirmed
- Preparing
- Ready
- Completed
- Cancelled

Admin bertanggung jawab memperbarui status pesanan.

### 5.5 Table Reservation

Customer dapat:
- Melihat daftar meja.
- Melihat kapasitas meja.
- Memilih tanggal.
- Memilih jam.
- Memasukkan jumlah orang.
- Memasukkan catatan tambahan.
- Memilih meja yang tersedia.
- Melihat status reservasi.
- Melihat riwayat reservasi.

Aturan reservasi:
- Durasi setiap reservasi adalah 2 jam.
- Jumlah orang tidak boleh melebihi kapasitas meja.
- Meja tidak boleh memiliki reservasi yang waktunya bertabrakan.
- Reservasi otomatis dikonfirmasi jika tersedia.
- Sistem memeriksa ulang ketersediaan ketika data disimpan.
- Reservasi yang dibatalkan tidak lagi memblokir ketersediaan meja.

Dua reservasi pada meja yang sama boleh berurutan apabila waktu reservasi sebelumnya sudah selesai.

### 5.6 Manage Tables

Admin dapat:
- Menambah meja.
- Mengubah nomor atau nama meja.
- Mengubah kapasitas meja.
- Mengaktifkan atau menonaktifkan meja.
- Melihat daftar meja.

Meja yang sedang tidak tersedia tidak boleh dipilih pelanggan.

### 5.7 Operating Hours Management

Admin dapat:
- Mengatur jam buka.
- Mengatur jam tutup.
- Mengatur jam operasional setiap hari.
- Menandai hari libur.
- Mengubah jadwal operasional.

Sistem reservasi harus mengikuti konfigurasi terbaru.

### 5.8 Reservation Closure Management

Admin dapat:
- Membuat periode penutupan reservasi.
- Mengatur tanggal mulai dan selesai.
- Menambahkan alasan penutupan.
- Mengubah periode penutupan.
- Menghapus periode penutupan.

Sistem akan menolak reservasi baru yang masuk dalam periode penutupan.

### 5.9 Payment Management

Metode pembayaran: Cash.

Admin dapat:
- Melihat status pembayaran.
- Mengonfirmasi pembayaran.
- Mengubah status menjadi Paid.

Status pembayaran:
- Unpaid
- Paid

### 5.10 Promotion Management

Admin dapat membuat promosi berupa:
- Diskon persentase.
- Diskon nominal tetap.

Informasi promosi:
- Nama promosi.
- Deskripsi.
- Jenis diskon.
- Nilai diskon.
- Menu yang mendapatkan promosi.
- Tanggal mulai.
- Tanggal berakhir.
- Status aktif atau tidak aktif.

Sistem menghitung diskon secara otomatis berdasarkan promosi yang valid.

Diskon tidak boleh menghasilkan total pembayaran negatif.

### 5.11 Notification

Sistem menyediakan notifikasi di dalam website untuk Customer dan Admin.

**Customer menerima notifikasi ketika:**
- Pesanan dikonfirmasi.
- Status pesanan berubah.
- Reservasi berhasil.
- Reservasi dibatalkan.

**Admin menerima notifikasi ketika:**
- Ada pesanan baru.
- Ada reservasi baru.

Notifikasi disimpan di database dan dapat ditandai sudah dibaca.

### 5.12 Dashboard Admin

Dashboard menyediakan:
- Ringkasan jumlah pesanan.
- Jumlah reservasi.
- Jumlah pelanggan.
- Ringkasan pendapatan.
- Pesanan terbaru.
- Reservasi terbaru.

Menu dashboard:
1. Dashboard
2. Manage Menu
3. Manage Categories
4. Manage Orders
5. Manage Bookings
6. Manage Tables
7. Manage Customers
8. Manage Promotions
9. Operating Hours
10. Reservation Closures
11. Reports
12. Profile

### 5.13 Reports

Admin dapat melihat:
- Laporan pesanan.
- Laporan pendapatan.
- Laporan reservasi.
- Ringkasan menu terlaris.

Laporan dapat difilter berdasarkan tanggal.

---

## 6. User Interface & Design System

### 6.1 Design Concept
Modern Cozy — tampilan modern, bersih, hangat, dan nyaman.

### 6.2 Color Palette

| Nama | Hex Color | Penggunaan |
|---|---|---|
| Coffee Brown | #6F4E37 | Warna utama |
| Cream | #F5EFE6 | Background |
| Warm White | #FFFCF7 | Card dan section |
| Caramel | #C69C6D | Aksen |
| Dark Text | #29231F | Teks utama |

### 6.3 Typography
Font utama: Poppins.

### 6.4 Framework
Bootstrap 5.

### 6.5 Responsive Design
Website harus mendukung:
- Smartphone.
- Tablet.
- Desktop.

### 6.6 Customer Pages
- Home
- Menu
- Menu Detail
- Cart
- Checkout
- Reservation
- My Orders
- My Bookings
- Notifications
- Profile
- Login
- Register

### 6.7 Admin Pages
- Admin Login
- Dashboard
- Menu Management
- Category Management
- Order Management
- Booking Management
- Table Management
- Customer Management
- Promotion Management
- Operating Hours
- Reservation Closures
- Reports
- Profile

---

## 7. Database Requirements

Database menggunakan MySQL.

Rancangan awal terdiri dari 15 tabel:

| No. | Tabel | Fungsi |
|---|---|---|
| 1 | users | Data Customer dan Admin |
| 2 | categories | Kategori menu |
| 3 | menus | Data makanan dan minuman |
| 4 | tables | Data meja cafe |
| 5 | operating_hours | Jam operasional |
| 6 | reservation_closures | Periode penutupan reservasi |
| 7 | bookings | Data reservasi |
| 8 | carts | Data keranjang |
| 9 | cart_items | Item dalam keranjang |
| 10 | orders | Data pesanan |
| 11 | order_items | Detail item pesanan |
| 12 | payments | Data pembayaran |
| 13 | promotions | Data promosi |
| 14 | promotion_menus | Relasi promosi dan menu |
| 15 | notifications | Data notifikasi |

### 7.1 Relasi Utama

- Satu User dapat memiliki banyak pesanan.
- Satu User dapat memiliki banyak reservasi.
- Satu kategori memiliki banyak menu.
- Satu meja dapat memiliki banyak riwayat reservasi.
- Satu reservasi terhubung dengan satu meja.
- Satu keranjang memiliki banyak item.
- Satu pesanan memiliki banyak item pesanan.
- Satu pesanan dapat memiliki satu data pembayaran.
- Satu pesanan Dine-in dapat terhubung dengan reservasi.
- Satu promosi dapat berlaku untuk banyak menu.
- Satu User dapat memiliki banyak notifikasi.

### 7.2 Catatan Database

- Primary key menggunakan ID.
- Foreign key digunakan untuk menjaga relasi data.
- Harga dan nilai pembayaran menggunakan tipe data numerik yang sesuai.
- Waktu reservasi disimpan dengan tanggal dan waktu yang jelas.
- Data pesanan perlu menyimpan snapshot nama dan harga item saat checkout agar riwayat tidak berubah ketika menu diedit.
- Data penting seperti pesanan dan pembayaran sebaiknya tidak dihapus sembarangan.

---

## 8. Technical Architecture

### 8.1 Technology Stack

| Bagian | Teknologi |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| CSS Framework | Bootstrap 5 |
| Backend | Native PHP OOP |
| Database | MySQL |
| Database Manager | phpMyAdmin |
| Local Server | XAMPP |
| Code Editor | Visual Studio Code |

Laravel dan Composer tidak digunakan pada versi pertama agar proses pengembangan lebih sederhana.

### 8.2 Project Folder Structure

```text
htdocs/
└── cozycafe/
    ├── app/
    │   ├── Controllers/
    │   ├── Models/
    │   ├── Core/
    │   └── Helpers/
    │
    ├── config/
    │   └── database.php
    │
    ├── public/
    │   ├── assets/
    │   │   ├── css/
    │   │   ├── js/
    │   │   └── images/
    │   ├── uploads/
    │   └── index.php
    │
    ├── views/
    │   ├── layouts/
    │   ├── auth/
    │   ├── customer/
    │   └── admin/
    │
    ├── database/
    │   └── cozycafe.sql
    │
    └── README.md
```

### 8.3 Architecture Rules

- Menggunakan konsep Object-Oriented Programming.
- Memisahkan proses aplikasi, database, dan tampilan.
- Menggunakan PDO untuk koneksi database.
- Menggunakan prepared statements.
- Menggunakan session untuk autentikasi.
- Memisahkan akses Customer dan Admin.

---

## 9. Non-Functional Requirements

### 9.1 Security
- Password menggunakan `password_hash()` dan `password_verify()`.
- Database menggunakan PDO prepared statements.
- Validasi dilakukan di sisi server.
- Output pengguna di-escape untuk mencegah XSS.
- Form penting dilindungi CSRF token.
- Halaman Admin dilindungi role-based access.
- File upload divalidasi berdasarkan tipe dan ukuran.

### 9.2 Performance
- Halaman harus dapat dimuat dengan cepat.
- Query database dibuat efisien.
- Gambar menu dioptimalkan.
- Data besar menggunakan pagination.

### 9.3 Reliability
- Data pesanan harus tersimpan dengan benar.
- Reservasi tidak boleh mengalami double booking.
- Proses checkout dan reservasi menggunakan transaksi database jika diperlukan.
- Sistem menampilkan pesan kesalahan yang jelas.

### 9.4 Usability
- Navigasi mudah dipahami.
- Tampilan konsisten.
- Form memiliki validasi dan pesan kesalahan.
- Website dapat digunakan pada berbagai ukuran layar.

---

## 10. Acceptance Criteria

Produk dinyatakan memenuhi kebutuhan versi pertama apabila:

1. Customer dapat mendaftar dan login.
2. Admin dapat login menggunakan akun Admin.
3. Customer dapat melihat menu dan kategori.
4. Customer dapat menggunakan keranjang.
5. Customer dapat melakukan pemesanan Takeaway.
6. Customer wajib memilih meja atau membuat reservasi untuk Dine-in.
7. Sistem dapat memeriksa ketersediaan meja.
8. Sistem mencegah reservasi yang bertabrakan.
9. Admin dapat mengelola jam operasional.
10. Admin dapat menutup reservasi pada tanggal tertentu.
11. Admin dapat mengelola menu, kategori, meja, dan pelanggan.
12. Admin dapat memperbarui status pesanan.
13. Customer dapat melihat status pesanan.
14. Admin dapat mengonfirmasi pembayaran tunai.
15. Admin dapat mengelola promosi.
16. Customer dan Admin dapat menerima notifikasi.
17. Admin dapat melihat laporan.
18. Website berjalan dengan baik pada smartphone dan desktop.

---

## 11. Development Roadmap

Pengembangan dilakukan bertahap agar mudah dipahami, diuji, dan diperbaiki.

| Tahap | Pekerjaan |
|---|---|
| 1 | Setup XAMPP dan project |
| 2 | Membuat struktur folder |
| 3 | Membuat database dan tabel |
| 4 | Membuat koneksi database PHP |
| 5 | Membuat sistem login dan register |
| 6 | Membuat layout dan desain website |
| 7 | Membuat fitur kategori dan menu |
| 8 | Membuat shopping cart |
| 9 | Membuat sistem reservasi |
| 10 | Membuat sistem pemesanan |
| 11 | Membuat dashboard Admin |
| 12 | Membuat pengelolaan jam operasional |
| 13 | Membuat pengelolaan penutupan reservasi |
| 14 | Membuat sistem pembayaran |
| 15 | Membuat sistem promosi |
| 16 | Membuat notifikasi |
| 17 | Membuat laporan |
| 18 | Testing dan perbaikan |

---

## 12. Out of Scope — Versi 1.0

Fitur berikut belum termasuk dalam pengembangan awal:

- Online payment gateway.
- Pembayaran menggunakan QRIS.
- Delivery dan pelacakan kurir.
- Aplikasi Android atau iOS terpisah.
- Integrasi WhatsApp otomatis.
- Sistem loyalty points.
- Multi-cabang cafe.
- Integrasi printer dapur.

Fitur tersebut dapat dipertimbangkan untuk versi berikutnya.

---

## 13. Product Success Metrics

Keberhasilan CozyCafe akan dievaluasi berdasarkan:

- Customer berhasil melakukan pemesanan tanpa bantuan Admin.
- Customer dapat melakukan reservasi secara mandiri.
- Tidak terjadi double booking.
- Admin dapat mengelola pesanan melalui dashboard.
- Status pesanan dapat dilihat Customer.
- Informasi menu dan harga selalu konsisten.
- Sistem berjalan baik pada perangkat mobile dan desktop.

---

## 14. Final Product Vision

CozyCafe bertujuan menjadi sistem pengelolaan cafe sederhana yang mengintegrasikan pemesanan makanan dan minuman, reservasi meja, pembayaran tunai, promosi, serta pengelolaan operasional dalam satu website.

Versi pertama akan berfokus pada fungsi utama, kemudahan penggunaan, keamanan dasar, dan struktur kode yang mudah dikembangkan.

**Dokumen PRD CozyCafe v1.0 — Final.**