<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Support\Authorization\PermissionCatalog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';
        foreach (PermissionCatalog::names() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
