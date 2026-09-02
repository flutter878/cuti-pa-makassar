<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            JabatanSeeder::class,
            UnitKerjaSeeder::class,
            JenisCutiSeeder::class,
            SuperadminSeeder::class,
        ]);
    }
}
