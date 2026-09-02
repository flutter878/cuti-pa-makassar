<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Pegawai',    'slug' => 'pegawai'],
            ['name' => 'Admin',      'slug' => 'admin'],
            ['name' => 'Superadmin', 'slug' => 'superadmin'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
