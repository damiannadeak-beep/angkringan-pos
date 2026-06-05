# PROPOSAL MAGANG / PRAKTIK KERJA LAPANGAN (PKL)
## PENGEMBANGAN SISTEM POINT OF SALES (POS) DAN MANAJEMEN PEMESANAN BERBASIS WEB PADA BISNIS F&B (STUDI KASUS: ANGKRINGAN)

**Disusun Oleh:**
Nama: Damian Gregorius Nadeak
NIM: 6103240028
Program Studi: D3 Teknik Informatika
Jurusan: Teknik Informatika
Institusi: Politeknik Negeri Bengkalis
Tahun: 2026

---

### BAB I. PENDAHULUAN

#### 1.1 Latar Belakang
Perkembangan teknologi informasi yang pesat telah mendorong berbagai sektor bisnis, termasuk industri Food & Beverage (F&B) skala Mikro, Kecil, dan Menengah (UMKM) seperti angkringan, untuk melakukan transformasi digital. Sistem pencatatan transaksi dan pesanan yang dilakukan secara manual (konvensional) menggunakan kertas rentan terhadap human-error, kehilangan data, dan menyulitkan proses rekapitulasi keuangan. 

Untuk menyelesaikan permasalahan tersebut, dibutuhkan sebuah Sistem Informasi *Point of Sales* (POS) yang terintegrasi. Sistem ini tidak hanya mempermudah kasir dalam mencatat transaksi, tetapi juga memungkinkan konsumen untuk melakukan pemesanan secara mandiri (Self-Order) melalui QR Code, serta membantu pemilik (Admin) dalam memantau stok bahan baku, pengeluaran operasional, dan laporan pendapatan secara *real-time*.

#### 1.2 Rumusan Masalah
1. Bagaimana merancang dan membangun sistem kasir (POS) yang efisien untuk mencatat transaksi penjualan?
2. Bagaimana membangun fitur *Self-Ordering* berbasis QR Code untuk memudahkan konsumen memesan menu?
3. Bagaimana mengelola data master (stok bahan, pengeluaran, menu, dan kasir) agar mudah dipantau oleh pemilik?

#### 1.3 Tujuan
1. Membangun aplikasi berbasis web "Angkringan POS" yang mengintegrasikan pencatatan kasir, pesanan pelanggan, dan laporan keuangan.
2. Menerapkan fitur E-Receipt (Struk Digital) via Email untuk mengurangi penggunaan kertas cetak.
3. Mengimplementasikan manajemen stok resep bahan baku yang berkurang secara otomatis setiap ada transaksi.

#### 1.4 Manfaat
- **Bagi Tempat Magang/Perusahaan:** Mendapatkan sistem kasir modern yang siap pakai untuk meningkatkan efisiensi operasional bisnis.
- **Bagi Mahasiswa:** Mengaplikasikan ilmu Kerangka Pemrograman Berbasis Web (Laravel) ke dalam studi kasus industri yang nyata.
- **Bagi Konsumen:** Mendapatkan pengalaman memesan hidangan yang lebih interaktif dan transparan.

---

### BAB II. DESKRIPSI SISTEM DAN RUANG LINGKUP

#### 2.1 Deskripsi Sistem
Aplikasi **Angkringan POS** adalah sistem kasir berbasis web yang dibangun menggunakan arsitektur MVC (Model-View-Controller). Sistem ini memiliki tiga antarmuka (Role) utama, yaitu: Admin (Pemilik), Kasir, dan Konsumen.

#### 2.2 Fitur Utama (Ruang Lingkup)
1. **Modul Admin (Pemilik):**
   - *Dashboard Analytics* (Laporan Penjualan harian, bulanan, grafik).
   - Manajemen Data Master: Menu, Meja, Promo, Manajemen Kasir.
   - Manajemen Keuangan & Gudang: Pencatatan Pengeluaran (Expense) dan Manajemen Stok Bahan Baku (Ingredient Inventory).
2. **Modul Kasir:**
   - *Point of Sales* (Layar Transaksi).
   - *Order Management* (Pesanan Masuk, Pemrosesan, Split Bill, Void Order).
   - Pencetakan Struk (Thermal Print & Dapur) serta Pengiriman E-Receipt via Email SMTP.
   - Laporan Tutup Shift (Rekonsiliasi Kas Harian).
3. **Modul Konsumen:**
   - Pemesanan Mandiri (*Dine-In* via QR Code Meja & *Takeaway*).
   - Sistem Penilaian (Rating & Ulasan).

#### 2.3 Batasan Masalah
Sistem ini berfokus pada manajemen internal bisnis F&B (Satu Cabang). Sistem belum mencakup integrasi *Payment Gateway* otomatis (pembayaran masih divalidasi manual oleh kasir) dan belum mencakup fitur pemesanan antar (Delivery Online).

---

### BAB III. METODOLOGI DAN TEKNOLOGI

#### 3.1 Metodologi Pengembangan Perangkat Lunak
Pengembangan sistem ini menggunakan metodologi **Agile / RAD (Rapid Application Development)**, yang terdiri dari tahapan:
1. Analisis Kebutuhan & Perancangan Database.
2. Pembuatan *Prototype* & *User Interface*.
3. Implementasi Kode (Coding).
4. Pengujian (Testing) fitur secara berkala.
5. Evaluasi dan Perbaikan.

#### 3.2 Teknologi yang Digunakan (Tech Stack)
- **Bahasa Pemrograman:** PHP 8, JavaScript, HTML5, CSS3.
- **Framework Web:** Laravel 11 (Backend), Bootstrap / Laravel UI (Frontend).
- **Database:** MySQL / MariaDB.
- **Fitur Tambahan:** Google SMTP Mailer (E-Receipt), QR Code Generator.

---

### BAB IV. PENUTUP
Proposal ini diajukan sebagai gambaran umum pelaksanaan Praktik Kerja Lapangan (Magang). Harapannya, pengembangan Sistem Informasi Angkringan POS ini dapat memberikan kontribusi nyata bagi tempat magang sekaligus menjadi wadah implementasi keilmuan vokasi yang komprehensif bagi mahasiswa.
