<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $target = max(0, (int) env('DEMO_USERS_COUNT', 300));

        $current = User::query()
            ->where('email', '!=', 'admin@playmatic.local')
            ->count();

        $missing = $target - $current;

        if ($missing <= 0) {
            $this->command?->info("UsersTableSeeder: ya existen {$current} usuarios de prueba (objetivo: {$target}).");
            return;
        }

        User::factory()
            ->count($missing)
            ->create([
                'is_superadmin' => false,
            ]);

        $this->command?->info("UsersTableSeeder: creados {$missing} usuarios de prueba.");
    }
}
