# PROPOSAL PRAKTIK KERJA LAPANGAN & PORTOFOLIO IT
## IMPLEMENTASI SISTEM POINT OF SALES (POS) DAN SELF-ORDERING BERBASIS WEB

<br>

**Diajukan Oleh:**
Damian Gregorius Nadeak
NIM. 6103240028

**Program Studi D3 Teknik Informatika**
**Politeknik Negeri Bengkalis**
**2026**

---

**Kontak & Tautan Portofolio:**
- **Email:** damiannadeak@gmail.com
- **Telepon/WA:** [Masukkan Nomor Anda]
- **Source Code Repository:** [github.com/damiannadeak-beep/angkringan-pos](https://github.com/damiannadeak-beep/angkringan-pos)

---

<br>

### BAB I: PENDAHULUAN & PROFIL PROYEK

#### 1.1 Latar Belakang & Ringkasan Eksekutif
Transformasi digital merupakan kebutuhan krusial bagi bisnis *Food & Beverage* (F&B) untuk meningkatkan efisiensi operasional dan kualitas pelayanan. Permasalahan umum yang sering terjadi di lapangan mencakup antrean panjang di kasir, pencatatan pesanan manual yang rentan kesalahan (*human error*), serta lambatnya rekapitulasi data stok bahan baku dan keuangan.

Untuk menjawab tantangan tersebut, saya telah mengembangkan **"Angkringan POS"**, sebuah purwarupa Sistem *Point of Sales* terpadu berbasis web. Aplikasi ini dirancang menggunakan arsitektur Model-View-Controller (MVC) untuk mendigitalisasi alur pemesanan dari hulu ke hilir, dilengkapi fitur *Self-Ordering* bagi konsumen. Proposal ini diajukan untuk mengimplementasikan, menyesuaikan, dan menguji keandalan sistem tersebut secara langsung pada lingkungan operasional industri selama masa Praktik Kerja Lapangan.

#### 1.2 Tujuan
1. Mengimplementasikan solusi teknologi nyata yang siap pakai (*production-ready*) untuk membantu efisiensi operasional bisnis perusahaan.
2. Melakukan pengujian langsung di lapangan (*User Acceptance Testing*) terhadap integrasi sistem perangkat lunak dengan perangkat keras (seperti printer *thermal*).
3. Memberikan nilai tambah bagi perusahaan tempat magang melalui digitalisasi layanan tanpa biaya pengembangan perangkat lunak dari awal.

---

<br>

### BAB II: SPESIFIKASI TEKNIS & FITUR SISTEM

Sistem POS ini dikembangkan dengan standar industri yang skalabel dan aman. Pembagian hak akses (Role) dirancang secara terstruktur untuk mengakomodasi kebutuhan setiap divisi operasional.

#### 2.1 Teknologi yang Digunakan (Tech Stack)
- **Backend:** PHP 8, Framework Laravel 12
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5 (Laravel UI)
- **Database:** MySQL / MariaDB
- **Integrasi:** Google SMTP Mailer (Email), QR Code Generator

#### 2.2 Rincian Fitur Utama (Berdasarkan Hak Akses)

**A. Panel Admin (Manajemen Eksekutif)**
- **Dashboard Analitik:** Visualisasi data pendapatan dan tren penjualan secara *real-time*.
- **Manajemen Inventaris (Auto-Deduct):** Pemotongan stok bahan baku/resep secara otomatis setiap kali menu terjual.
- **Pencatatan Keuangan:** Modul pelacakan pengeluaran operasional harian (*Expense Tracking*).
- **Manajemen Master Data:** Pengaturan dinamis untuk kasir, meja, dan promo.

**B. Panel Kasir (Operasional Front-End)**
- **Transaksi Cepat:** Antarmuka *tap-and-go* yang responsif.
- **Manajemen Pesanan Fleksibel:** Mendukung fungsi *Split Bill* (pisah bon) dan *Void Order* (pembatalan dengan log keamanan).
- **E-Receipt & Cetak Ganda:** Pengiriman struk digital otomatis ke email pelanggan via Google SMTP, dan pencetakan fisik ke *Thermal Printer* kasir serta dapur.

**C. Panel Konsumen (Pengalaman Pelanggan)**
- **Self-Ordering QR Code:** Pemesanan mandiri oleh konsumen (*Dine-in*) cukup dengan memindai kode QR di meja menggunakan smartphone.
- **Takeaway Mode:** Adaptasi alur pesanan untuk bungkus/bawa pulang.
- **Rating & Ulasan:** Fasilitas *feedback* digital untuk evaluasi manajemen.

---

<br>

### BAB III: ETALASE SISTEM (SHOWCASE)

*(Catatan saat dipindah ke Word: Kompres gambar terlebih dahulu agar ukuran PDF akhir tetap di bawah 1 MB)*

**1. Dasbor Laporan Analitik (Admin)**
![Dashboard Admin]([MASUKKAN_GAMBAR_DASHBOARD_DI_SINI])
*Keterangan: Antarmuka pemantauan pendapatan dan status operasional bisnis secara real-time.*

**2. Antarmuka Layar Kasir / Point of Sales**
![Layar Kasir]([MASUKKAN_GAMBAR_KASIR_DI_SINI])
*Keterangan: Desain antarmuka kasir yang dioptimalkan untuk kecepatan input pesanan dan manajemen transaksi.*

**3. Tampilan E-Katalog Konsumen (Mobile)**
![E-Katalog Mobile]([MASUKKAN_GAMBAR_HP_KONSUMEN_DI_SINI])
*Keterangan: Antarmuka pemesanan mandiri yang responsif saat pelanggan memindai QR Code di meja.*

---

<br>

### BAB IV: RENCANA IMPLEMENTASI MAGANG

Selama masa magang, kehadiran saya tidak hanya untuk mempelajari alur kerja perusahaan, tetapi juga untuk memberikan kontribusi langsung melalui implementasi sistem ini dengan tahapan berikut:

1. **Analisis & Kustomisasi (Bulan 1):** Mengumpulkan *Standard Operating Procedure* (SOP) perusahaan dan memodifikasi antarmuka serta logika bisnis aplikasi agar selaras dengan kebutuhan perusahaan.
2. **Instalasi & Integrasi (Bulan 2):** Melakukan *deployment* sistem ke server perusahaan dan mengintegrasikannya dengan perangkat keras (seperti jaringan lokal dan printer thermal).
3. **Pengujian & Pelatihan (Bulan 3):** Melaksanakan simulasi transaksi nyata (*User Acceptance Testing*), melatih karyawan (kasir/admin) untuk mengoperasikan sistem secara mandiri, dan menyelesaikan fase *Go-Live*.

---

<br>

### BAB V: PENUTUP

Melalui proposal dan portofolio ini, saya menawarkan solusi teknologi yang konkret dan siap diimplementasikan. Saya sangat antusias untuk membawa kemampuan logika pemrograman, penyelesaian masalah, dan etos kerja profesional saya untuk berkontribusi secara langsung bagi perusahaan.

Seluruh basis kode (*source code*) dari sistem ini bersifat *open-source* dan dapat ditinjau langsung oleh tim teknis perusahaan melalui tautan repositori GitHub yang tertera pada halaman sampul. Atas waktu dan kesempatan yang diberikan, saya ucapkan terima kasih.
