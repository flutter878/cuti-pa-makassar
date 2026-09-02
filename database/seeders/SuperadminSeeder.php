<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRole = Role::where('slug', 'superadmin')->first();

        User::firstOrCreate(
            ['email' => 'superadmin@pa-makassar.go.id'],
            [
                'role_id'  => $superadminRole->id,
                'name'     => 'Superadmin',
                'password' => Hash::make('password'),
                'status'   => 'aktif',
            ]
        );
    }
}
