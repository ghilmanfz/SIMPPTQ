<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Behavior;
use App\Models\Grade;
use App\Models\Jadwal;
use App\Models\LeaveRequest;
use App\Models\LokasiPresensi;
use App\Models\Mapel;
use App\Models\Personil;
use App\Models\PresensiPersonil;
use App\Models\Santri;
use App\Models\SantriPresence;
use App\Models\SwapRequest;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OperationalSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $ahmad = Personil::where('name', 'like', '%Ahmad Fauzi%')->first();
        $budiyono = Personil::where('name', 'like', '%Budiyono%')->first();
        $fatkur = Personil::where('name', 'like', '%Fatkur%')->first();

        $adminUser = User::where('email', 'petugas@nuruliman.net')->first();
        $teacherUser = User::where('email', 'ustadz.ahmad@nuruliman.net')->first();

        // --- Lokasi presensi GPS ---
        $lokasi = LokasiPresensi::updateOrCreate(
            ['name' => 'Komplek Utama PPTQ Nurul Iman'],
            ['latitude' => -6.9147440, 'longitude' => 107.6098100, 'radius' => 100, 'is_active' => true]
        );

        // --- Presensi personil (hari ini) ---
        if ($ahmad) {
            PresensiPersonil::updateOrCreate(
                ['personil_id' => $ahmad->id, 'date' => $today->toDateString()],
                ['lokasi_presensi_id' => $lokasi->id, 'check_in_time' => '04:45', 'check_out_time' => '17:40', 'status' => 'Tepat Waktu']
            );
        }
        if ($budiyono) {
            PresensiPersonil::updateOrCreate(
                ['personil_id' => $budiyono->id, 'date' => $today->toDateString()],
                ['lokasi_presensi_id' => $lokasi->id, 'check_in_time' => '08:15', 'check_out_time' => '16:00', 'status' => 'Terlambat']
            );
        }

        // --- Izin / cuti ---
        if ($budiyono) {
            LeaveRequest::updateOrCreate(
                ['personil_id' => $budiyono->id, 'type' => 'Cuti Tahunan', 'start_date' => $today->copy()->addDays(2)->toDateString()],
                ['end_date' => $today->copy()->addDays(4)->toDateString(), 'reason' => 'Acara keluarga pernikahan adik kandung.', 'status' => 'Diajukan']
            );
        }
        if ($ahmad) {
            LeaveRequest::updateOrCreate(
                ['personil_id' => $ahmad->id, 'type' => 'Sakit', 'start_date' => $today->copy()->subDays(4)->toDateString()],
                ['end_date' => $today->copy()->subDays(3)->toDateString(), 'reason' => 'Demam tinggi butuh istirahat (surat dokter terlampir).', 'status' => 'Disetujui', 'approved_by' => $adminUser?->id, 'approved_at' => now()]
            );
        }

        // --- Tukar jam (untuk pengajar) ---
        if ($ahmad) {
            $jadwalSelasa = Jadwal::where('personil_id', $ahmad->id)->where('day', 'Selasa')->first();
            if ($jadwalSelasa) {
                SwapRequest::updateOrCreate(
                    ['jadwal_id' => $jadwalSelasa->id, 'date' => $today->copy()->addDays(3)->toDateString()],
                    ['requester_personil_id' => $ahmad->id, 'target_personil_id' => $fatkur?->id, 'reason' => 'Ada agenda daurah di luar kota.', 'status' => 'Diajukan']
                );
            }
        }

        // --- Presensi santri (scan) hari ini ---
        $hafizh = Santri::where('nis', '26001')->first();
        $rafli = Santri::where('nis', '26002')->first();
        foreach ([[$hafizh, '04:55'], [$rafli, '05:01']] as [$santri, $time]) {
            if ($santri) {
                SantriPresence::updateOrCreate(
                    ['santri_id' => $santri->id, 'date' => $today->toDateString(), 'kegiatan' => 'Halaqah Subuh'],
                    ['kelas_id' => $santri->kelas_id, 'time' => $time, 'status' => 'Hadir', 'recorded_by' => $teacherUser?->id]
                );
            }
        }

        // --- Perilaku santri (pelanggaran & kebaikan) ---
        if ($hafizh) {
            Behavior::updateOrCreate(
                ['santri_id' => $hafizh->id, 'date' => $today->copy()->subDays(3)->toDateString(), 'type' => 'Kebaikan'],
                ['category' => 'Kedisiplinan', 'points' => 15, 'note' => 'Membantu merapikan perpustakaan asrama tanpa disuruh.', 'recorded_by' => $teacherUser?->id]
            );
        }
        if ($rafli) {
            Behavior::updateOrCreate(
                ['santri_id' => $rafli->id, 'date' => $today->copy()->subDays(4)->toDateString(), 'type' => 'Pelanggaran'],
                ['category' => 'Ibadah', 'points' => 10, 'note' => 'Terlambat bangun subuh dan tidak mengikuti jamaah masjid.', 'recorded_by' => $teacherUser?->id]
            );
        }

        // --- Nilai / perkembangan ---
        $tahfidz = Mapel::where('name', 'like', '%Tahfidz%')->first();
        if ($hafizh) {
            Grade::updateOrCreate(
                ['santri_id' => $hafizh->id, 'date' => $today->copy()->subDays(5)->toDateString(), 'subject' => 'Tahfidz Al-Qur\'an'],
                ['mapel_id' => $tahfidz?->id, 'score' => 92, 'note' => 'Lancar setoran Juz 29 dengan makhraj fasih.', 'recorded_by' => $teacherUser?->id]
            );
        }

        // --- Kunjungan / jenguk ---
        if ($hafizh) {
            Visit::updateOrCreate(
                ['santri_id' => $hafizh->id, 'visit_at' => $today->copy()->setTime(10, 15)],
                ['visitor_name' => 'Rahmat Kartolo', 'relation' => 'Ayah Kandung', 'purpose' => 'Mengantar bekal bulanan', 'note' => 'Membawa pakaian ganti dan kitab.', 'recorded_by' => $adminUser?->id]
            );
        }

        // --- Pengumuman (publik 'Semua' tampil di landing; bertarget role tampil di dashboard) ---
        $announcements = [
            ['Penerimaan Raport dan Kunjungan Wali Santri Juni 2026', 'Diberitahukan kepada seluruh wali santri bahwa pembagian laporan perkembangan bulanan santri sekaligus kunjungan terjadwal akan dilaksanakan akhir pekan ini.', 'Semua', 2],
            ['Ujian Tasmi\' Al-Qur\'an 30 Juz Sekali Duduk', 'Mohon doa restu untuk kelancaran ananda Hafizh yang akan menempuh ujian tasmi\' Al-Qur\'an sekali duduk. Acara disiarkan langsung melalui platform internal.', 'Semua', 12],
            ['Pendaftaran Santri Baru Gelombang II Dibuka', 'Pendaftaran calon santri baru gelombang II resmi dibuka. Informasi syarat dan alur seleksi dapat diperoleh di sekretariat pondok.', 'Semua', 1],
            ['Sosialisasi Penggunaan Presensi GPS & Scan Kartu Santri', 'Seluruh ustadz/ustadzah dan staff diwajibkan mengikuti sosialisasi tata cara operasional absensi GPS web dan scan barcode santri pada hari Senin depan.', 'teacher', 3],
            ['Rapat Koordinasi Evaluasi Jadwal Master', 'Diwajibkan berkumpul di Sadewa Room jam 13:00 selepas shalat dzuhur.', 'teacher', 4],
        ];
        foreach ($announcements as [$title, $content, $target, $daysAgo]) {
            Announcement::updateOrCreate(
                ['title' => $title],
                ['content' => $content, 'target' => $target, 'is_active' => true, 'published_at' => $today->copy()->subDays($daysAgo)->toDateString(), 'author_id' => $adminUser?->id]
            );
        }
    }
}
