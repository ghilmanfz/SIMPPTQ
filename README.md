# Sistem Informasi Manajemen PPTQ Nurul Iman (SIMPPTQ)

SIMPPTQ adalah sistem informasi manajemen kepondokan berbasis web terpadu yang dirancang untuk mendigitalisasi operasional harian, pengelolaan data personil internal, presensi berbasis GPS, pencatatan akademik ringan santri, penggajian (payroll), serta laporan eksekutif di **PPTQ Nurul Iman**.

Proyek ini dibangun menggunakan **Laravel 11**, **Tailwind CSS v4**, dan **Alpine.js** untuk menghadirkan user interface modern, interaktif, dan responsif.

---

## 🎨 Tema Visual & Estetika
Tampilan antarmuka sistem mengikuti tema bersih (*clean*), modern, dan premium dengan kombinasi palet warna korporat/lembaga tepercaya:
* **Navy Blue (`#0b2265`)**: Sebagai warna utama, melambangkan kestabilan, keteraturan, dan profesionalitas administrasi pondok.
* **Emerald Green (`#107c41`)**: Sebagai aksen warna sekunder, merepresentasikan pertumbuhan, pembinaan karakter qur'ani, serta nilai-nilai keislaman.
* **Glassmorphism Panels & Soft Gradients**: Memberikan sentuhan visual premium, rapi, dan modern di seluruh bagian dashboard.

---

## 🚀 Fitur Utama & Modul Sistem
Sistem ini mengimplementasikan seluruh kebutuhan prioritas (RF-01 hingga RF-30):
1. **Landing Page Publik**: Halaman profil pondok (Visi, Misi, Nilai), papan pengumuman umum, dan link cepat menuju portal login.
2. **Portal Kredensial dengan Akses Demo**: Form login aman yang dilengkapi panel uji coba peran (*Akses Presets*) untuk memudahkan demonstrasi hak akses.
3. **Manajemen Akun & Role-Permission Granular**: Kontrol penuh atas penambahan user dan pengaturan hak akses detail per modul secara dinamis.
4. **Data Personil Internal & Dokumen**: Pengelolaan data pegawai, status hubungan kerja (Tetap/Tidak Tetap), fungsi kerja (Pengajar/Non-Pengajar), serta dokumen arsip digital.
5. **Presensi GPS Web**: Melakukan check-in/out harian dengan validasi otomatis radius koordinat lokasi pondok.
6. **Alur Izin/Cuti & Tukar Jam**: Pengajuan cuti terintegrasi bagi staf dan formulir tukar jam mengajar bagi ustadz yang dilengkapi dengan dashboard persetujuan operator.
7. **Penggajian (Payroll)**: Penghitungan otomatis gaji berkala beserta cetak/unduh PDF rincian slip gaji digital karyawan.
8. **Kartu QR Santri**: Generate kartu identitas santri yang dilengkapi QR code unik untuk absensi fisik.
9. **Scan QR Absensi Santri**: Simulator scanner barcode kamera untuk absensi kehadiran santri secara cepat oleh petugas.
10. **Catatan Perilaku & Nilai**: Logging akumulasi poin pelanggaran/kebaikan santri dan input evaluasi perkembangan hafalan Al-Qur'an mingguan.
11. **Register Kunjungan Wali**: Pencatatan data tamu jengukan santri.
12. **Laporan Strategis**: Ekspor data rekapitulasi kehadiran, santri, dan perilaku ke format Microsoft Excel.

---

## 🛠️ Langkah Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di lingkungan pengembangan lokal Anda:

### 1. Kloning Repositori
```bash
git clone https://github.com/ghilmanfz/SIMPPTQ.git
cd SIMPPTQ
```

### 2. Instalasi Dependensi PHP & JavaScript
```bash
# Instal dependensi PHP (Composer)
composer install

# Instal dependensi JavaScript (NPM)
npm install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file `.env.example` menjadi `.env` dan konfigurasikan database Anda:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Jalankan Migrasi Database & Seeder (Jika Diperlukan)
```bash
php artisan migrate
```

### 5. Kompilasi Aset Frontend (Vite)
Jalankan compiler aset di latar belakang:
```bash
npm run dev
```
Atau untuk build produksi:
```bash
npm run build
```

### 6. Jalankan Server PHP
```bash
php artisan serve
```
Akses aplikasi melalui browser di `http://localhost:8000`.

---

## 👥 Hak Akses Uji Coba Cepat (Demo Akun)
Pada halaman login, Anda dapat mengeklik salah satu tombol pintas peran untuk masuk secara instan:
* **Super Admin**: Akun konfigurasi sistem dan kelola hak peran.
* **Admin Operasional**: Manajemen personil, santri, persetujuan izin, dan payroll.
* **Guru (Pengajar)**: Lihat jadwal mengajar, ajukan tukar jam, input nilai, dan GPS check-in.
* **Staff Non-Pengajar**: GPS check-in dan pengajuan cuti.
* **Dua Fungsi**: Gabungan menu guru dan staf administrasi.
* **Pimpinan**: Visualisasi analitik grafik kehadiran dan monitoring laporan santri.
