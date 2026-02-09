<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        /*
         |------------------------------------------------------------
         | 1. Usuario admin (back)
         |------------------------------------------------------------
         */
        $user = User::firstOrCreate(
            ['email' => 'admin@playmatic.local'],
            [
                'name' => 'Admin Playmatic',
                'password' => Hash::make('admin12345'),
                'is_superadmin' => true,
            ]
        );

        /*
         |------------------------------------------------------------
         | 2. Tenant de ejemplo
         |------------------------------------------------------------
         */
        $tenantId = DB::table('tenants')->insertGetId([
            'name'       => 'Playmatic Demo',
            'slug'       => 'playmatic-demo',
            'branding'   => json_encode([
                'logo' => null,
                'primary_color' => '#111827',
            ]),
            'features'   => json_encode([]),
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        /*
         |------------------------------------------------------------
         | 3. Dominio del tenant
         |------------------------------------------------------------
         */
        DB::table('tenant_domains')->insert([
            'tenant_id'  => $tenantId,
            'domain'     => 'playmatic.local',
            'is_primary' => true,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        /*
         |------------------------------------------------------------
         | 4. Rol tenant_admin (Spatie)
         |------------------------------------------------------------
         */
        $role = Role::where('name', 'tenant_admin')->first();

        if (!$role) {
            throw new \RuntimeException('El rol tenant_admin no existe. Ejecuta primero el RbacSeeder.');
        }

        /*
         |------------------------------------------------------------
         | 5. Relación user ↔ tenant con role_id
         |------------------------------------------------------------
         */
        DB::table('tenant_users')->updateOrInsert(
            [
                'tenant_id' => $tenantId,
                'user_id'   => $user->id,
            ],
            [
                'role_id'   => $role->id,
                'status'    => 'active',
                'created_at'=> now(),
                'updated_at'=> now(),
            ]
        );
    }
}
