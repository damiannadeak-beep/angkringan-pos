# Portofolio Proyek Pengembangan Perangkat Lunak
## Sistem Point of Sales (POS) & Self-Ordering Berbasis Web

**Disusun Oleh:**
- **Nama:** Damian Gregorius Nadeak
- **Program Studi:** D3 Teknik Informatika
- **Institusi:** Politeknik Negeri Bengkalis
- **Email:** damiannadeak@gmail.com
- **Telepon:** [Isi Nomor HP Anda]

---

## Profil Proyek (Executive Summary)

**Deskripsi Singkat:**
Aplikasi "Angkringan POS" adalah sistem kasir terpadu berbasis web yang dirancang khusus untuk memecahkan masalah antrean, pencatatan transaksi manual, dan ketidakefisienan operasional pada bisnis F&B skala UMKM.

**Tujuan Proyek:**
Proyek ini difokuskan pada digitalisasi pesanan, pelacakan inventaris bahan baku yang terpotong secara otomatis (*auto-deduct*), manajemen laporan shift kasir, dan memberikan kemudahan akses interaktif bagi konsumen tanpa harus menunggu pelayan.

**Arsitektur Sistem:**
Aplikasi ini dibangun menggunakan arsitektur Model-View-Controller (MVC) yang canggih, membagi hak akses secara ketat ke dalam tiga peran utama:
1. **Admin (Pemilik):** Memantau laporan analitik, mengatur menu, promo, dan manajemen pengguna.
2. **Kasir:** Mengeksekusi transaksi, validasi pembayaran, dan mencetak laporan tutup shift.
3. **Konsumen:** Melakukan pesanan mandiri langsung dari meja mereka.

---

## Teknologi & Fitur Utama (Tech Stack & Features)

**Tech Stack:**
- **Backend:** PHP 8, Laravel 11
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap (Laravel UI)
- **Database:** MySQL / MariaDB
- **Integrasi Tambahan:** Google SMTP Mailer (Email Engine), QR Code Generator

**Sorotan Fitur Utama:**
- Pengiriman E-Receipt (Struk Digital) otomatis langsung ke email konsumen.
- Sistem *Self-Ordering* mandiri via pemindaian kode QR di setiap meja.
- Manajemen *Split Bill* (Pisah Bon) dan Pembatalan Pesanan (*Void*) dengan pencatatan log keamanan.
- Manajemen Stok Resep/Bahan Baku yang langsung terpotong akurat setiap menu terjual.

---

## Etalase Visual (System Showcase)

*(Catatan: Hapus teks kurung siku di bawah ini dan GANTI dengan gambar screenshot (tangkapan layar) dari aplikasi Anda. Pastikan gambar sudah di-compress agar ukuran PDF akhir di bawah 1 MB!)*

### 1. Dasbor Analitik Admin
![Dashboard Admin]([MASUKKAN_GAMBAR_DASHBOARD_DI_SINI])
*Dasbor interaktif yang menyajikan grafik penjualan, rekapitulasi data pendapatan, dan laporan operasional secara real-time.*

### 2. Antarmuka Layar Kasir POS
![Layar Kasir]([MASUKKAN_GAMBAR_KASIR_DI_SINI])
*Antarmuka Point of Sales yang bersih dan responsif, dirancang untuk kecepatan transaksi (tap-and-go) dan akurasi perhitungan pesanan.*

### 3. Tampilan E-Katalog Konsumen di Mobile
![E-Katalog Mobile]([MASUKKAN_GAMBAR_HP_KONSUMEN_DI_SINI])
*Tampilan antarmuka yang sangat mobile-friendly. Memungkinkan konsumen memindai QR Code di meja dan langsung memilih menu tanpa bantuan pelayan.*

### 4. E-Receipt Digital via Email
![E-Receipt Email]([MASUKKAN_GAMBAR_EMAIL_DI_SINI])
*Contoh struk digital yang otomatis terkirim ke kotak masuk pelanggan melalui integrasi SMTP Gmail, mendukung operasional yang ramah lingkungan (paperless).*

---

## Kesimpulan & Nilai Tambah (Value Proposition)

**Pencapaian Pembelajaran:**
Melalui pengembangan proyek ini secara mandiri (End-to-End), saya telah berhasil mengasah secara tajam kemampuan logika pemrograman struktural, desain relasi basis data (RDBMS) yang kompleks, serta insting penyelesaian masalah (*problem-solving*) dalam menghadapi bug teknis. 

**Kesiapan Magang:**
Saya sangat siap untuk membawa etos kerja yang berorientasi pada penyelesaian masalah ini ke dalam lingkungan profesional. Saya memiliki kemampuan adaptasi yang cepat terhadap teknologi/kerangka kerja baru yang digunakan di perusahaan, serta berkomitmen penuh untuk memberikan kontribusi nyata dan positif bagi kesuksesan tim pengembangan perangkat lunak di perusahaan Anda.
