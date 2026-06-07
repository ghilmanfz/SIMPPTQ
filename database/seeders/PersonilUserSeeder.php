<?php

namespace Database\Seeders;

use App\Models\Personil;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PersonilUserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        // Setiap baris: akun user + data personil yang terhubung.
        // Kredensial dipertahankan agar tombol "Uji Coba Cepat" di halaman login tetap bekerja.
        $people = [
            [
                'role' => 'superadmin', 'email' => 'superadmin@nuruliman.net', 'password' => 'superadmin123',
                'name' => 'Dr. Zeth Boroh, Sp.KO', 'nik' => '3404100204910001',
                'jabatan' => 'Administrator Sistem', 'unit_kerja' => 'Sekretariat',
                'status_kerja' => 'Tetap', 'fungsi_kerja' => 'Non-Pengajar', 'gender' => 'L',
                'phone' => '081234567890', 'base' => 4500000, 'allowance' => 750000, 'deduction' => 50000,
            ],
            [
                'role' => 'admin', 'email' => 'petugas@nuruliman.net', 'password' => 'petugas123',
                'name' => 'Hj. Siti Aminah, S.E.', 'nik' => '3404100204910010',
                'jabatan' => 'Admin Operasional / Kepala TU', 'unit_kerja' => 'Tata Usaha',
                'status_kerja' => 'Tetap', 'fungsi_kerja' => 'Non-Pengajar', 'gender' => 'P',
                'phone' => '081255550010', 'base' => 3500000, 'allowance' => 500000, 'deduction' => 0,
            ],
            [
                'role' => 'teacher', 'email' => 'ustadz.ahmad@nuruliman.net', 'password' => 'ustadz123',
                'name' => 'Ustadz Ahmad Fauzi, S.Pd.I', 'nik' => '3404100204910002',
                'jabatan' => 'Wali Asrama Takhassus', 'unit_kerja' => 'Tahfidz',
                'status_kerja' => 'Tetap', 'fungsi_kerja' => 'Pengajar', 'gender' => 'L',
                'phone' => '081298765432', 'base' => 3200000, 'allowance' => 500000, 'deduction' => 0,
            ],
            [
                'role' => 'staff', 'email' => 'staff.budiyono@nuruliman.net', 'password' => 'staff123',
                'name' => 'Budiyono, S.Kom', 'nik' => '3404100204910003',
                'jabatan' => 'Staff Administrasi Akademik', 'unit_kerja' => 'Akademik',
                'status_kerja' => 'Tidak Tetap', 'fungsi_kerja' => 'Non-Pengajar', 'gender' => 'L',
                'phone' => '081345678912', 'base' => 2800000, 'allowance' => 300000, 'deduction' => 150000,
            ],
            [
                'role' => 'hybrid', 'email' => 'ustadz.fatkur@nuruliman.net', 'password' => 'ustadzstaff123',
                'name' => 'Ustadz Fatkur Rahman, Lc.', 'nik' => '3404100204910004',
                'jabatan' => 'Pengajar Nahwu & Staff Kurikulum', 'unit_kerja' => 'Kurikulum',
                'status_kerja' => 'Tetap', 'fungsi_kerja' => 'Dua Fungsi', 'gender' => 'L',
                'phone' => '081398765411', 'base' => 3800000, 'allowance' => 600000, 'deduction' => 0,
            ],
            [
                'role' => 'leader', 'email' => 'pimpinan.kiai@nuruliman.net', 'password' => 'kiai123',
                'name' => 'K.H. Nurul Huda, M.A.', 'nik' => '3404100204910005',
                'jabatan' => 'Mudir / Pimpinan Pondok', 'unit_kerja' => 'Pimpinan',
                'status_kerja' => 'Tetap', 'fungsi_kerja' => 'Non-Pengajar', 'gender' => 'L',
                'phone' => '081211112222', 'base' => 6000000, 'allowance' => 1000000, 'deduction' => 0,
            ],
        ];

        foreach ($people as $p) {
            $user = User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'password' => Hash::make($p['password']),
                    'role_id' => $roles[$p['role']] ?? null,
                    'is_active' => true,
                ]
            );

            Personil::updateOrCreate(
                ['nik' => $p['nik']],
                [
                    'user_id' => $user->id,
                    'name' => $p['name'],
                    'gender' => $p['gender'],
                    'phone' => $p['phone'],
                    'email' => $p['email'],
                    'jabatan' => $p['jabatan'],
                    'unit_kerja' => $p['unit_kerja'],
                    'status_kerja' => $p['status_kerja'],
                    'fungsi_kerja' => $p['fungsi_kerja'],
                    'salary_base' => $p['base'],
                    'salary_allowance' => $p['allowance'],
                    'salary_deduction' => $p['deduction'],
                    'is_active' => true,
                ]
            );
        }
    }
}
