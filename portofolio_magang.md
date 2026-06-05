# Portofolio Proyek Pengembangan Perangkat Lunak
## Sistem Point of Sales (POS) & Self-Ordering Berbasis Web

**Disusun Oleh:**
- **Nama:** Damian Gregorius Nadeak
- **Program Studi:** D3 Teknik Informatika
- **Institusi:** Politeknik Negeri Bengkalis
- **Email:** damiannadeak@gmail.com
- **Telepon:** [Isi Nomor HP Anda]
- **Tautan GitHub:** [https://github.com/damiannadeak-beep/angkringan-pos](https://github.com/damiannadeak-beep/angkringan-pos)

---

## Profil Proyek & Logika Bisnis (Executive Summary)

**Deskripsi Singkat:**
Aplikasi "Angkringan POS" adalah sistem kasir terpadu berbasis web yang dirancang khusus untuk memecahkan masalah antrean, inefisiensi pencatatan transaksi manual, dan kebocoran stok bahan baku pada bisnis F&B skala UMKM (studi kasus angkringan).

**Tujuan & Solusi:**
Proyek ini difokuskan pada digitalisasi alur pemesanan secara menyeluruh. Sistem mempermudah kasir dalam berekonsiliasi data keuangan di akhir shift, melacak pemotongan inventaris bahan baku (resep) secara otomatis, dan memberikan kenyamanan bagi pelanggan melalui fitur *self-ordering* yang instan.

**Ketersediaan Source Code:**
Seluruh basis kode (*source code*) dari arsitektur sistem ini bersifat *open-source* dan dapat ditinjau langsung (termasuk riwayat *commit* dan struktur direktori) melalui repositori GitHub yang terlampir di atas.

---

## Sorotan Teknis & Arsitektur Kode (Technical Highlight)

**Tech Stack:**
- **Bahasa & Framework:** PHP 8.x, Laravel 12.x
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5 (Laravel UI)
- **Database:** MySQL / MariaDB

**Penerapan Konsep MVC:**
Aplikasi ini secara disiplin menerapkan pola arsitektur Model-View-Controller (MVC) bawaan Laravel untuk memisahkan logika bisnis (Model), logika kontrol (Controller), dan presentasi antarmuka pengguna (View), sehingga kode lebih terstruktur dan mudah dikembangkan (*scalable*).

**Praktik Coding Lanjut (Laravel Best Practices):**
- **Sistem Role & Middleware:** Menggunakan proteksi Middleware untuk mendistribusikan 3 level hak akses yang ketat (Admin, Kasir, dan Konsumen) agar keamanan rute (*routing*) terjamin.
- **Eloquent ORM:** Menerapkan pemetaan relasi antar tabel database yang kompleks (seperti relasi *Many-to-Many* antara Menu dan Bahan Baku) melalui fitur Eloquent ORM.
- **Migrations & Seeders:** Memanfaatkan struktur *Database Migrations* untuk pelacakan skema tabel secara *version-control*, serta *Database Seeders* untuk otomatisasi pembuatan akun *Super Admin* awal.

---

## Etalase Visual (System Showcase)

*(Catatan: Hapus teks kurung siku di bawah ini dan GANTI dengan gambar screenshot aplikasi Anda!)*

### 1. Antarmuka Layar Kasir POS
![Layar Kasir]([MASUKKAN_GAMBAR_KASIR_DI_SINI])
*Antarmuka kasir yang responsif. Mendukung fitur Split Bill (Pisah Bon), Void Order (Pembatalan pesanan terenkripsi), dan E-Receipt via email SMTP.*

### 2. Katalog Mobile Self-Ordering
![E-Katalog Mobile]([MASUKKAN_GAMBAR_HP_KONSUMEN_DI_SINI])
*Hasil pemindaian QR Code dari meja pengunjung. Memungkinkan pelanggan langsung melakukan pemesanan tanpa perantara pelayan.*

### 3. Dasbor Analitik Admin
![Dashboard Admin]([MASUKKAN_GAMBAR_DASHBOARD_DI_SINI])
*Pusat kendali operasional (Admin Panel) untuk memantau grafik penjualan real-time, manajemen stok bahan baku, dan riwayat pengeluaran.*

---

## Tautan Akses & Penutup (Call to Action)

**Akses Repositori GitHub:**
Silakan kunjungi [https://github.com/damiannadeak-beep/angkringan-pos](https://github.com/damiannadeak-beep/angkringan-pos) untuk meninjau secara mendalam logika arsitektur aplikasi, file dokumentasi teknis `README.md`, dan riwayat pengembangan proyek ini.

*(Opsional: Sisipkan Gambar QR Code di sini yang jika di-scan akan mengarah langsung ke link GitHub Anda!)*

**Kesiapan Magang:**
Pembuatan proyek perangkat lunak dari hulu ke hilir (*End-to-End*) ini membuktikan dedikasi saya terhadap kualitas kode dan penyelesaian masalah bisnis. Saya sangat siap membawa kemampuan analitis, pemahaman arsitektur sistem, dan etos kerja profesional ini untuk memberikan kontribusi nyata di perusahaan/tim IT yang Bapak/Ibu pimpin.
