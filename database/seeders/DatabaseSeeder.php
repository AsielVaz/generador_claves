<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password', 'is_admin' => false],
        );

        User::updateOrCreate(
            ['email' => 'admin@admin'],
            ['name' => 'Administrador', 'password' => 'CelinaRifa2026', 'is_admin' => true],
        );

        $this->call(CourseSeeder::class);
    }
}
