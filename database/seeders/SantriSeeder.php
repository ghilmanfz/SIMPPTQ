<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Santri;
use Illuminate\Database\Seeder;

class SantriSeeder extends Seeder
{
    public function run(): void
    {
        $kelas = Kelas::pluck('id', 'name');

        $santris = [
            ['name' => 'Muhammad Hafizh Al-Fatih', 'nis' => '26001', 'nisn' => '0098765431', 'kelas' => '7A', 'gender' => 'L', 'status' => 'Aktif', 'wali' => 'Rahmat Kartolo', 'wali_phone' => '081377778888', 'wali_relation' => 'Ayah Kandung', 'birth_place' => 'Bandung', 'birth_date' => '2013-04-12'],
            ['name' => 'Ahmad Rafli Aditya', 'nis' => '26002', 'nisn' => '0098765432', 'kelas' => '7A', 'gender' => 'L', 'status' => 'Aktif', 'wali' => 'Sugeng Pranoto', 'wali_phone' => '081366665555', 'wali_relation' => 'Ayah Kandung', 'birth_place' => 'Cimahi', 'birth_date' => '2013-07-01'],
            ['name' => 'Fadhil Nur Hakim', 'nis' => '26003', 'nisn' => '0098765433', 'kelas' => '7B', 'gender' => 'L', 'status' => 'Aktif', 'wali' => 'Hendra Wijaya', 'wali_phone' => '081344443333', 'wali_relation' => 'Ayah Kandung', 'birth_place' => 'Garut', 'birth_date' => '2013-02-20'],
            ['name' => 'Zahra Aulia Rahma', 'nis' => '26004', 'nisn' => '0098765434', 'kelas' => '8B', 'gender' => 'P', 'status' => 'Aktif', 'wali' => 'Imam Santoso', 'wali_phone' => '081322221111', 'wali_relation' => 'Ayah Kandung', 'birth_place' => 'Bandung', 'birth_date' => '2012-09-15'],
            ['name' => 'Khaira Salsabila', 'nis' => '26005', 'nisn' => '0098765435', 'kelas' => '8B', 'gender' => 'P', 'status' => 'Aktif', 'wali' => 'Bayu Saputra', 'wali_phone' => '081311110000', 'wali_relation' => 'Ayah Kandung', 'birth_place' => 'Sumedang', 'birth_date' => '2012-11-30'],
            ['name' => 'Yusuf Ramadhan', 'nis' => '26006', 'nisn' => '0098765436', 'kelas' => '7B', 'gender' => 'L', 'status' => 'Aktif', 'wali' => 'Dedi Mulyana', 'wali_phone' => '081399998888', 'wali_relation' => 'Ayah Kandung', 'birth_place' => 'Tasikmalaya', 'birth_date' => '2013-01-05'],
            ['name' => 'Salman Al-Farisi', 'nis' => '24010', 'nisn' => '0078654321', 'kelas' => null, 'gender' => 'L', 'status' => 'Lulus', 'wali' => 'Mahmudin', 'wali_phone' => '081300001111', 'wali_relation' => 'Ayah Kandung', 'birth_place' => 'Bandung', 'birth_date' => '2010-03-18'],
        ];

        foreach ($santris as $s) {
            Santri::updateOrCreate(
                ['nis' => $s['nis']],
                [
                    'name' => $s['name'],
                    'nisn' => $s['nisn'],
                    'gender' => $s['gender'],
                    'kelas_id' => $s['kelas'] ? ($kelas[$s['kelas']] ?? null) : null,
                    'status' => $s['status'],
                    'wali_name' => $s['wali'],
                    'wali_phone' => $s['wali_phone'],
                    'wali_relation' => $s['wali_relation'],
                    'birth_place' => $s['birth_place'],
                    'birth_date' => $s['birth_date'],
                    'card_token' => Santri::generateCardToken(),
                ]
            );
        }
    }
}
