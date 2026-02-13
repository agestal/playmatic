<?php

namespace Database\Seeders;

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
        $this->call([
            RbacSeeder::class,
            GameCatalogSeeder::class,
        ]);

        if ((bool) config('playmatic.seed_demo_data')) {
            $this->call([
                DevSeeder::class,
                UsersTableSeeder::class,
            ]);
        }
    }
}
