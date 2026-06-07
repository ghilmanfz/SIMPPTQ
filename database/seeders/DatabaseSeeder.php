<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            PersonilUserSeeder::class,
            AcademicSeeder::class,
            SantriSeeder::class,
            OperationalSeeder::class,
            PayrollSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
