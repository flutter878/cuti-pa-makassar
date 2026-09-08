<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            JabatanSeeder::class,        // include kolom kategori baru
            UnitKerjaSeeder::class,
            JenisCutiSeeder::class,
            SuperadminSeeder::class,
            RoutingTemplateSeeder::class, // template routing workflow baru
        ]);
    }
}
