# Sistem Informasi Manajemen PPTQ Nurul Iman (SIMPPTQ)

SIMPPTQ adalah sistem informasi manajemen kepondokan berbasis web terpadu untuk mendigitalisasi operasional harian **PPTQ Nurul Iman**: pengelolaan personil internal, presensi berbasis GPS, izin/cuti, tukar jam mengajar, penggajian (payroll), data & presensi santri (kartu QR), nilai/perkembangan, perilaku, kunjungan, pengumuman, dashboard, dan laporan strategis.

Dibangun dengan **Laravel 13 (MVC)**, **MySQL**, **Tailwind CSS v4**, dan **Alpine.js**. Seluruh data tersimpan di database (tidak ada data hardcode), dengan autentikasi dan otorisasi berbasis role-permission yang nyata.

---

## 🎨 Tema Visual
Tampilan bersih, modern, dan premium dengan palet korporat:
* **Navy Blue (`#0b2265`)** — warna utama (profesionalitas administrasi).
* **Emerald Green (`#107c41`)** — aksen (nilai qur'ani & pertumbuhan).
* Panel rounded, soft shadow, dan tipografi *Plus Jakarta Sans*.

---

## 🧱 Arsitektur
* **Backend:** Laravel 13 — Controller + Eloquent Model + Blade (server-rendered MVC).
* **Database:** MySQL, ±25 tabel ber-relasi (lihat `database/migrations`).
* **Autentikasi:** session-based, throttling 5x percobaan, hanya akun aktif yang bisa masuk (tanpa registrasi publik).
* **Otorisasi:** sistem role–permission kustom (`roles`, `permissions`, pivot) + `Gate::before`. Permission diperiksa via middleware `permission:` dan direktif `@can` di Blade.
* **Fungsi kerja** (Non-Pengajar / Pengajar / Dua Fungsi) dipisahkan dari role — menentukan kelayakan modul operasional (jadwal & tukar jam).
* **Presensi GPS:** validasi jarak sungguhan (rumus *haversine*) terhadap radius lokasi.
* **Integrasi WhatsApp:** pengiriman nyata ke API **Fonnte** via HTTP.
* **Penyimpanan berkas:** logo/hero/foto di disk publik; dokumen sensitif personil di disk privat.

---

## 🚀 Modul Sistem
1. **Landing Page & Branding dinamis** — konten, logo, hero, statistik diatur dari menu Branding (tersimpan di DB).
2. **Autentikasi & Profil** — login/logout, ganti password, profil terbatas.
3. **Manajemen User & Role–Permission** — CRUD akun, matriks hak akses per modul.
4. **Personil Internal & Dokumen** — CRUD, status & fungsi kerja, unggah dokumen privat.
5. **Jadwal Mengajar** — kelola jadwal dengan validasi bentrok pengajar & kelas.
6. **Presensi GPS** — check-in/out dengan validasi radius lokasi + rekap.
7. **Izin/Cuti & Tukar Jam** — pengajuan + alur persetujuan (approve/reject). Tukar jam membuat *pengecualian jadwal* tanpa mengubah jadwal master.
8. **Penggajian (Payroll)** — periode, proses berbasis kehadiran, finalisasi & kunci, slip gaji.
9. **Data Santri & Kartu QR** — CRUD, filter, generate kartu santri ber-QR (cetak).
10. **Presensi Santri** — pencatatan kehadiran via scan token kartu / input manual.
11. **Kelas, Master Akademik** — rombel, tahun ajaran, mapel/halaqah, sesi.
12. **Perilaku, Nilai, Kunjungan** — poin pelanggaran/kebaikan, nilai/perkembangan, register jenguk wali.
13. **Pengumuman** — papan pengumuman bertarget role.
14. **Laporan Strategis** — rekap terfilter (periode) + cetak.
15. **Integrasi WhatsApp Fonnte** — konfigurasi token & uji kirim pesan nyata.

---

## 🛠️ Instalasi Lokal

Prasyarat: PHP 8.3+, Composer, Node.js, MySQL (mis. via Laragon).

```bash
git clone https://github.com/ghilmanfz/SIMPPTQ.git
cd SIMPPTQ

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env` (default sudah `mysql` / database `simpptq`):
```env
DB_DATABASE=simpptq
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `simpptq`, lalu migrasi + isi data awal, build aset, dan jalankan:
```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run build        # atau: npm run dev (mode pengembangan)
php artisan serve
```
Akses di `http://localhost:8000` (atau virtual host Laragon, mis. `http://simpptq.test`).

---

## 👥 Akun Demo
Tersedia tombol **Uji Coba Cepat** di halaman login, atau masuk manual:

| Peran | Email | Password |
| --- | --- | --- |
| Super Admin | `superadmin@nuruliman.net` | `superadmin123` |
| Admin Operasional | `petugas@nuruliman.net` | `petugas123` |
| Guru (Pengajar) | `ustadz.ahmad@nuruliman.net` | `ustadz123` |
| Staf Non-Pengajar | `staff.budiyono@nuruliman.net` | `staff123` |
| Dua Fungsi | `ustadz.fatkur@nuruliman.net` | `ustadzstaff123` |
| Pimpinan | `pimpinan.kiai@nuruliman.net` | `kiai123` |

> Menu yang tampil menyesuaikan permission tiap peran secara otomatis.

---

## ✅ Pengujian
Smoke test memverifikasi seluruh route, otorisasi per role, dan alur inti (login, CRUD, presensi GPS, approval) memakai SQLite in-memory:
```bash
php artisan test
```

---

## 📌 Catatan
Modul lanjutan (komponen gaji granular, workflow kenaikan kelas, ekspor Excel) dapat dikembangkan bertahap sesuai metode Agile tanpa mengubah fondasi yang ada.
