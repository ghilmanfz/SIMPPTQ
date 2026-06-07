<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Katalog permission (dikelompokkan untuk menu pengaturan) ----
        $catalog = [
            'Sistem' => [
                'dashboard_view' => 'Lihat Dashboard',
                'user_manage' => 'Kelola Akun User',
                'role_manage' => 'Kelola Role & Permission',
                'setting_manage' => 'Kelola Branding & Integrasi',
                'reports_view' => 'Laporan Strategis',
            ],
            'Personil' => [
                'personnel_view' => 'Lihat Data Personil',
                'personnel_manage' => 'Kelola Data Personil',
                'schedule_view' => 'Lihat Jadwal Mengajar',
                'schedule_manage' => 'Kelola Jadwal Mengajar',
                'presence_gps' => 'Presensi GPS (Check-in/out)',
                'presence_manage' => 'Kelola & Koreksi Presensi',
                'location_manage' => 'Kelola Lokasi Presensi',
                'leave_apply' => 'Ajukan Izin / Cuti',
                'leave_approve' => 'Setujui Izin / Cuti',
                'swap_apply' => 'Ajukan Tukar Jam',
                'swap_approve' => 'Setujui Tukar Jam',
                'payroll_view' => 'Lihat Slip Gaji',
                'payroll_manage' => 'Kelola Penggajian',
            ],
            'Santri' => [
                'santri_view' => 'Lihat Data Santri',
                'santri_manage' => 'Kelola Data Santri & Kartu',
                'class_view' => 'Lihat Kelas',
                'class_manage' => 'Kelola Kelas',
                'academic_manage' => 'Kelola Master Akademik',
                'santri_presence' => 'Presensi Santri (Scan)',
                'behavior_log' => 'Catat Pelanggaran & Kebaikan',
                'grade_log' => 'Input Nilai & Perkembangan',
                'visit_log' => 'Catat Kunjungan Wali',
            ],
            'Komunikasi' => [
                'announcement_view' => 'Lihat Pengumuman',
                'announcement_manage' => 'Kelola Pengumuman',
            ],
        ];

        $keyToId = [];
        foreach ($catalog as $group => $items) {
            foreach ($items as $key => $label) {
                $perm = Permission::updateOrCreate(['key' => $key], ['label' => $label, 'group' => $group]);
                $keyToId[$key] = $perm->id;
            }
        }

        $all = array_keys($keyToId);

        // ---- Role beserta permission-nya (mengacu Matriks Akses) ----
        $roles = [
            'superadmin' => [
                'label' => 'Super Admin',
                'description' => 'Akses penuh konfigurasi dan administrasi sistem.',
                'permissions' => $all,
            ],
            'admin' => [
                'label' => 'Admin Operasional',
                'description' => 'Petugas pengelola data & proses harian pondok.',
                'permissions' => [
                    'dashboard_view', 'user_manage', 'setting_manage', 'reports_view',
                    'personnel_view', 'personnel_manage', 'schedule_view', 'schedule_manage',
                    'presence_gps', 'presence_manage', 'location_manage',
                    'leave_apply', 'leave_approve', 'swap_approve',
                    'payroll_view', 'payroll_manage',
                    'santri_view', 'santri_manage', 'class_view', 'class_manage', 'academic_manage',
                    'santri_presence', 'behavior_log', 'grade_log', 'visit_log',
                    'announcement_view', 'announcement_manage',
                ],
            ],
            'teacher' => [
                'label' => 'Guru (Pengajar)',
                'description' => 'Asatidzah pengajar/halaqah.',
                'permissions' => [
                    'dashboard_view', 'schedule_view', 'presence_gps',
                    'leave_apply', 'swap_apply',
                    'santri_view', 'class_view', 'santri_presence',
                    'behavior_log', 'grade_log', 'announcement_view',
                ],
            ],
            'staff' => [
                'label' => 'Staf Non-Pengajar',
                'description' => 'Pegawai non-pengajar.',
                'permissions' => [
                    'dashboard_view', 'presence_gps', 'leave_apply', 'announcement_view',
                ],
            ],
            'hybrid' => [
                'label' => 'Dua Fungsi',
                'description' => 'Personil merangkap staf dan pengajar.',
                'permissions' => [
                    'dashboard_view', 'schedule_view', 'presence_gps',
                    'leave_apply', 'swap_apply',
                    'santri_view', 'class_view', 'santri_presence',
                    'behavior_log', 'grade_log', 'announcement_view',
                ],
            ],
            'leader' => [
                'label' => 'Pimpinan Pondok',
                'description' => 'Monitoring dan evaluasi melalui dashboard & laporan.',
                'permissions' => [
                    'dashboard_view', 'reports_view',
                    'personnel_view', 'schedule_view',
                    'santri_view', 'class_view', 'payroll_view', 'announcement_view',
                ],
            ],
        ];

        foreach ($roles as $name => $cfg) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['label' => $cfg['label'], 'description' => $cfg['description'], 'is_system' => true]
            );
            $ids = array_map(fn ($k) => $keyToId[$k], $cfg['permissions']);
            $role->permissions()->sync($ids);
        }
    }
}
