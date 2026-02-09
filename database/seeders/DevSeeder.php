<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;

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
        $tenantSlug = 'playmatic-demo';
        $now = now();

        $tenant = DB::table('tenants')
            ->where('slug', $tenantSlug)
            ->first();

        if ($tenant) {
            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update([
                    'name' => 'Playmatic Demo',
                    'branding' => json_encode([
                        'logo' => null,
                        'primary_color' => '#111827',
                    ]),
                    'features' => json_encode([]),
                    'updated_at' => $now,
                ]);

            $tenantId = (int) $tenant->id;
        } else {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Playmatic Demo',
                'slug' => $tenantSlug,
                'branding' => json_encode([
                    'logo' => null,
                    'primary_color' => '#111827',
                ]),
                'features' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
         |------------------------------------------------------------
         | 3. Dominio del tenant
         |------------------------------------------------------------
         */
        $domain = DB::table('tenant_domains')
            ->where('domain', 'playmatic.local')
            ->first();

        if ($domain) {
            DB::table('tenant_domains')
                ->where('id', $domain->id)
                ->update([
                    'tenant_id' => $tenantId,
                    'is_primary' => true,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('tenant_domains')->insert([
                'tenant_id' => $tenantId,
                'domain' => 'playmatic.local',
                'is_primary' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roles = collect(['tenant_admin', 'tenant_manager', 'tenant_viewer'])
            ->mapWithKeys(function (string $roleName) use ($tenantId) {
                $role = Role::query()->firstOrCreate([
                    'tenant_id' => $tenantId,
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions(PermissionCatalog::defaultsForRole($roleName));

                return [$roleName => $role];
            });

        $adminRole = $roles['tenant_admin'];

        /*
         |------------------------------------------------------------
         | 5. Relación user ↔ tenant con role_id
         |------------------------------------------------------------
         */
        $tenantUser = DB::table('tenant_users')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->first();

        if ($tenantUser) {
            DB::table('tenant_users')
                ->where('id', $tenantUser->id)
                ->update([
                    'role_id' => $adminRole->id,
                    'status' => 'active',
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('tenant_users')->insert([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'role_id' => $adminRole->id,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $samples = [
            [
                'email' => 'manager@playmatic.local',
                'name' => 'Manager Playmatic',
                'role' => 'tenant_manager',
            ],
            [
                'email' => 'viewer@playmatic.local',
                'name' => 'Viewer Playmatic',
                'role' => 'tenant_viewer',
            ],
        ];

        foreach ($samples as $sample) {
            $sampleUser = User::firstOrCreate(
                ['email' => $sample['email']],
                [
                    'name' => $sample['name'],
                    'password' => Hash::make('admin12345'),
                    'is_superadmin' => false,
                ]
            );

            DB::table('tenant_users')->updateOrInsert(
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $sampleUser->id,
                ],
                [
                    'role_id' => $roles[$sample['role']]->id,
                    'status' => 'active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
