<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Personil;
use App\Models\Sesi;
use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $ta = TahunAjaran::updateOrCreate(
            ['name' => '2025/2026 Genap'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-06-30', 'is_active' => true]
        );

        // --- Mapel / Halaqah ---
        $mapels = [
            'Tahfidzul Qur\'an' => 'Halaqah',
            'Nahwu Sharaf' => 'Mapel',
            'Hadits Arbain' => 'Mapel',
            'Tilawah & Tajwid' => 'Halaqah',
            'Fiqih Ibadah' => 'Mapel',
        ];
        $mapelByName = [];
        foreach ($mapels as $name => $type) {
            $mapelByName[$name] = Mapel::updateOrCreate(['name' => $name], ['type' => $type]);
        }

        // --- Sesi / jam pelajaran ---
        $sesis = [
            ['name' => 'Sesi Subuh', 'start_time' => '05:00', 'end_time' => '06:00', 'order' => 1],
            ['name' => 'Sesi Pagi', 'start_time' => '08:00', 'end_time' => '09:30', 'order' => 2],
            ['name' => 'Sesi Dhuha', 'start_time' => '10:00', 'end_time' => '11:30', 'order' => 3],
            ['name' => 'Sesi Sore', 'start_time' => '16:00', 'end_time' => '17:30', 'order' => 4],
        ];
        $sesiByName = [];
        foreach ($sesis as $s) {
            $sesiByName[$s['name']] = Sesi::updateOrCreate(['name' => $s['name']], $s);
        }

        // --- Personil pengajar (untuk wali kelas & jadwal) ---
        $ahmad = Personil::where('name', 'like', '%Ahmad Fauzi%')->first();
        $fatkur = Personil::where('name', 'like', '%Fatkur%')->first();

        // --- Kelas / rombel ---
        $kelas7a = Kelas::updateOrCreate(['name' => '7A'], [
            'tingkat' => '7', 'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $ahmad?->id, 'is_active' => true,
        ]);
        $kelas8b = Kelas::updateOrCreate(['name' => '8B'], [
            'tingkat' => '8', 'tahun_ajaran_id' => $ta->id,
            'wali_kelas_id' => $fatkur?->id, 'is_active' => true,
        ]);
        Kelas::updateOrCreate(['name' => '7B'], [
            'tingkat' => '7', 'tahun_ajaran_id' => $ta->id, 'is_active' => true,
        ]);

        // --- Jadwal mengajar (master) ---
        $jadwals = [
            ['personil' => $ahmad, 'mapel' => 'Tahfidzul Qur\'an', 'kelas' => $kelas7a, 'sesi' => 'Sesi Subuh', 'day' => 'Senin'],
            ['personil' => $ahmad, 'mapel' => 'Tahfidzul Qur\'an', 'kelas' => $kelas7a, 'sesi' => 'Sesi Sore', 'day' => 'Selasa'],
            ['personil' => $fatkur, 'mapel' => 'Nahwu Sharaf', 'kelas' => $kelas8b, 'sesi' => 'Sesi Pagi', 'day' => 'Rabu'],
            ['personil' => $fatkur, 'mapel' => 'Hadits Arbain', 'kelas' => $kelas7a, 'sesi' => 'Sesi Dhuha', 'day' => 'Kamis'],
        ];

        foreach ($jadwals as $j) {
            if (! $j['personil']) {
                continue;
            }
            $sesi = $sesiByName[$j['sesi']];
            Jadwal::updateOrCreate(
                [
                    'personil_id' => $j['personil']->id,
                    'kelas_id' => $j['kelas']->id,
                    'mapel_id' => $mapelByName[$j['mapel']]->id,
                    'day' => $j['day'],
                ],
                [
                    'tahun_ajaran_id' => $ta->id,
                    'sesi_id' => $sesi->id,
                    'start_time' => $sesi->start_time,
                    'end_time' => $sesi->end_time,
                ]
            );
        }
    }
}
