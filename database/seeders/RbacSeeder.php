<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Importantísimo: limpiar cache de Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // Permisos mínimos (los que definimos)
        $permissions = [
            'games.manage',
            'participants.view',
            'winners.view',
            'exports.run',
            'users.manage',
            'tenant.branding.manage',
            'tenant.domains.manage',
        ];

        // Crear permisos de forma inequívoca (name + guard_name)
        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        // Crear roles (también con guard explícito)
        $admin   = Role::firstOrCreate(['name' => 'tenant_admin',   'guard_name' => $guard]);
        $manager = Role::firstOrCreate(['name' => 'tenant_manager', 'guard_name' => $guard]);
        $viewer  = Role::firstOrCreate(['name' => 'tenant_viewer',  'guard_name' => $guard]);

        // Asignación determinista
        $admin->syncPermissions($permissions);

        $manager->syncPermissions([
            'games.manage',
            'participants.view',
            'winners.view',
            'exports.run',
        ]);

        $viewer->syncPermissions([
            'participants.view',
            'winners.view',
        ]);

        // Limpia cache otra vez por si acaso
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
