# 🍡 Angkringan POS (Point of Sales) & Self-Ordering System

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

Angkringan POS adalah sebuah Sistem Kasir Terpadu berbasis web yang dikembangkan menggunakan arsitektur **MVC (Model-View-Controller)** pada framework **Laravel 11**. Aplikasi ini dirancang untuk menyelesaikan permasalahan antrean dan rekapitulasi manual pada bisnis skala UMKM (Food & Beverage).

Sistem ini memiliki 3 antarmuka/Role utama: **Admin (Pemilik), Kasir, dan Konsumen**.

---

## ✨ Fitur Utama

### 👨‍💼 1. Panel Admin (Pemilik)
- **Dashboard Analitik:** Pantau total pendapatan harian, bulanan, dan grafik penjualan secara *real-time*.
- **Manajemen Master Data:** Kelola data Kasir, Meja (beserta pembuatan QR Code otomatis), dan Promo/Diskon.
- **Manajemen Inventaris (Auto-Deduct):** Sistem secara cerdas akan memotong stok bahan baku (resep) secara otomatis setiap kali ada menu yang terjual.
- **Pencatatan Keuangan:** Catat pengeluaran operasional warung (*Expense Tracking*).

### 👩‍💻 2. Panel Kasir (Point of Sales)
- **Proses Transaksi Cepat:** Antarmuka kasir yang dirancang untuk kecepatan (tap-and-go).
- **Manajemen Pesanan Lanjut:** Mendukung *Split Bill* (Pisah Bon) dan *Void Order* (Pembatalan dengan catatan log keamanan).
- **Sistem Cetak Ganda:** Mendukung pencetakan Struk Thermal Kasir dan Struk Dapur (KDS).
- **Laporan Tutup Shift:** Rekonsiliasi jumlah uang fisik dengan sistem secara akurat di akhir shift.
- **E-Receipt Integration:** Mampu mengirimkan struk digital langsung ke email pelanggan via integrasi **Google SMTP**.

### 📱 3. Panel Konsumen (Self-Ordering)
- **Pesan via QR Code:** Konsumen dapat memindai QR di meja mereka dan langsung memilih menu tanpa bantuan pelayan (*Dine-in*).
- **Takeaway Mode:** Mendukung pemesanan bungkus/bawa pulang.
- **Sistem Rating:** Konsumen dapat memberikan ulasan dan rating untuk pelayanan/makanan.

---

## 🛠️ Teknologi yang Digunakan (Tech Stack)

- **Backend:** PHP 8.x, Laravel 11.x
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5 (Laravel UI)
- **Database:** MySQL / MariaDB
- **Integrasi Pihak Ketiga:** SimpleSoftwareIO/QrCode, Symfony Mailer (Google SMTP)

---

## 🚀 Panduan Instalasi (Local Development)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di komputer lokal Anda.

### 1. Kebutuhan Sistem (Prerequisites)
Pastikan Anda sudah menginstal:
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL (XAMPP / Laragon)

### 2. Langkah Instalasi

Clone repositori ini ke komputer Anda:
```bash
git clone https://github.com/damiannadeak-beep/angkringan-pos.git
cd angkringan-pos
```

Install semua *dependency* PHP (Vendor):
```bash
composer install
```

Install semua *dependency* Frontend (Node Modules) dan lakukan proses Build:
```bash
npm install
npm run build
```

Salin file pengaturan environment dan *generate* kunci aplikasi:
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi Database & Email (.env)
Buka file `.env` dan sesuaikan pengaturan berikut:

**Database:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=angkringan_pos
DB_USERNAME=root
DB_PASSWORD=
```

**Email SMTP (Untuk E-Receipt):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_google_anda
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="email_anda@gmail.com"
MAIL_FROM_NAME="Angkringan POS"
```

### 4. Migrasi & Seeder (Reset Database)
Jalankan perintah ini untuk membuat tabel database beserta akun Admin bawaan:
```bash
php artisan migrate:fresh --seed
```

*(Catatan: Akun bawaan Super Admin bisa Anda ubah di dalam file `.env` pada variabel `ADMIN_EMAIL` dan `ADMIN_PASSWORD` sebelum menjalankan seeder).*

### 5. Jalankan Aplikasi
Nyalakan *local server*:
```bash
php artisan serve
```

Buka browser dan akses: `http://127.0.0.1:8000`

---

## 👨‍💻 Kontributor
- **Damian Gregorius Nadeak** - *Fullstack Developer* - Politeknik Negeri Bengkalis (D3 Teknik Informatika).

---
*Proyek ini dikembangkan sebagai portofolio pengembangan perangkat lunak.*
