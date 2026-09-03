<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Default ke role 'pegawai' (id=1). Jika belum ada, ambil role pertama yang ada.
        $roleId = Role::where('slug', 'pegawai')->value('id')
                  ?? Role::min('id')
                  ?? 1;

        return [
            'role_id'            => $roleId,
            'name'               => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            'email_verified_at'  => now(),
            'password'           => static::$password ??= Hash::make('password'),
            'remember_token'     => Str::random(10),
            'status'             => 'aktif',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Set role to admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('slug', 'admin')->value('id') ?? 2,
        ]);
    }

    /**
     * Set role to superadmin.
     */
    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('slug', 'superadmin')->value('id') ?? 3,
        ]);
    }
}
