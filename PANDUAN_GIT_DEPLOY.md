# Panduan Workflow Git & Deployment cPanel (Angkringan POS)

Dokumen ini berisi langkah-langkah standar untuk pengembangan fitur baru, perbaikan bug (trial-error di local), hingga deployment ke server cPanel live.

---

## 1. Pengembangan & Trial-Error di Laptop (Localhost)

Kerjakan seluruh eksperimen dan uji coba di laptop (Local XAMPP) menggunakan **Branch**.

```bash
# 1. Pindah ke branch utama & pastikan kode lokal terbaru
git checkout master
git pull origin master

# 2. Buat branch baru untuk fitur/bug tertentu
# Format: fitur/nama-fitur  atau  fix/nama-bug
git checkout -b fitur-stok-otomatis

# 3. Bebas koding dan trial-error di local (http://localhost:8000)
# Setelah fitur berhasil & lulus uji coba, simpan perubahan:
git add -A
git commit -m "feat: implementasi fitur stok otomatis"

# 4. Push branch fitur ke GitHub
git push --set-upstream origin fitur-stok-otomatis
```

---

## 2. Penggabungan Kode (Merge ke Master)

Setelah fitur di branch diuji dan tidak ada error:

```bash
# Pindah kembali ke branch master lokal
git checkout master

# Gabungkan kode dari branch fitur ke master
git merge fitur-stok-otomatis

# Push master terbaru ke GitHub
git push origin master

# (Opsional) Hapus branch fitur lokal jika sudah selesai
git branch -d fitur-stok-otomatis
```

---

## 3. Deployment ke Server cPanel Live

Buka **Terminal cPanel** pada server hosting:

```bash
# 1. Masuk ke folder repository server
cd /home/nadp3189/repositories/angkringan-pos

# 2. Pindah ke branch master & tarik kode terbaru dari GitHub
git checkout master
git pull origin master

# 3. Sinkronkan kode terbaru ke folder web live (angkringan.nadeak.net)
cp -Rf app /home/nadp3189/public_html/angkringan.nadeak.net/
cp -Rf resources /home/nadp3189/public_html/angkringan.nadeak.net/
cp -Rf routes /home/nadp3189/public_html/angkringan.nadeak.net/
cp -Rf public/* /home/nadp3189/public_html/angkringan.nadeak.net/public/ 2>/dev/null || true

# 4. Pastikan ijin folder storage & public tetap aman (0777)
chmod -R 777 /home/nadp3189/public_html/angkringan.nadeak.net/storage 2>/dev/null || true
chmod -R 777 /home/nadp3189/public_html/angkringan.nadeak.net/public/storage 2>/dev/null || true
```

---

## Catatan Penting
- **Jangan pernah koding langsung di server live.** Selalu gunakan branch lokal untuk uji coba.
- **Folder Gambar:** Folder `public/storage` di server adalah folder fisik asli (bukan symlink) untuk menghindari pemblokiran keamanan CageFS cPanel.
