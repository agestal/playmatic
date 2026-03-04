<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameAttendanceGuessSetting;
use App\Models\GameEntry;
use App\Models\GameTenant;
use App\Models\GameQuizAnswer;
use App\Models\GameQuizQuestion;
use App\Models\GameRound;
use App\Models\GameWinner;
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
                    'logo' => null,
                    'primary_color' => '#111827',
                    'secondary_color' => '#3b82f6',
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
                'logo' => null,
                'primary_color' => '#111827',
                'secondary_color' => '#3b82f6',
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

        $this->seedSampleGamesForTenant($tenantId);
        $this->seedSampleAttendanceGuessData($tenantId);
        $this->seedSampleQuizQuestionsForTenant($tenantId);

        $roles = collect(['admin', 'gestor'])
            ->mapWithKeys(function (string $roleName) use ($tenantId) {
                $role = Role::query()->firstOrCreate([
                    'tenant_id' => $tenantId,
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions(PermissionCatalog::defaultsForRole($roleName));

                return [$roleName => $role];
            });

        $adminRole = $roles['admin'];

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
                'role' => 'gestor',
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

    protected function seedSampleGamesForTenant(int $tenantId): void
    {
        $games = [
            [
                'slug' => 'adivina-el-aforo',
                'name' => 'Adivina el aforo',
                'game_type' => 'attendance_guess',
                'description' => 'Predice el aforo real de una jornada, fecha o evento.',
                'is_active' => true,
                'config' => [
                    'input_type' => 'number',
                    'has_options' => false,
                ],
            ],
            [
                'slug' => 'trivial',
                'name' => 'Trivial',
                'game_type' => 'quiz',
                'description' => 'Contest questions con una respuesta correcta entre varias opciones.',
                'is_active' => true,
                'config' => [
                    'input_type' => 'single_choice',
                    'has_options' => true,
                ],
            ],
        ];

        foreach ($games as $payload) {
            $game = Game::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                [
                    'name' => $payload['name'],
                    'game_type' => $payload['game_type'],
                    'description' => $payload['description'],
                    'is_active' => $payload['is_active'],
                    'config' => $payload['config'],
                ]
            );

            GameTenant::query()->updateOrCreate(
                [
                    'game_id' => $game->id,
                    'tenant_id' => $tenantId,
                ],
                [
                    'is_visible' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                ]
            );
        }
    }

    protected function seedSampleAttendanceGuessData(int $tenantId): void
    {
        $game = Game::query()
            ->where('slug', 'adivina-el-aforo')
            ->first();

        if (! $game) {
            return;
        }

        GameAttendanceGuessSetting::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'game_id' => $game->id,
            ],
            [
                'winners_count' => 3,
                'ranking_enabled' => true,
            ]
        );

        $round = GameRound::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'game_id' => $game->id,
                'name' => 'Jornada Demo - Adivina el aforo',
            ],
            [
                'management_mode' => 'manual',
                'starts_at' => null,
                'ends_at' => null,
                'activated_at' => now()->subHours(8),
                'deactivated_at' => now()->subHours(4),
                'result_value' => null,
                'result_recorded_at' => null,
            ]
        );

        GameWinner::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('game_id', $game->id)
            ->where('game_round_id', $round->id)
            ->delete();

        GameEntry::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('game_id', $game->id)
            ->where('game_round_id', $round->id)
            ->where('participant_email', 'like', 'demo.aforo.%@playmatic.local')
            ->delete();

        $baseTimestamp = now()->subHours(7)->startOfMinute();
        $expectedAttendance = random_int(2500, 4500);
        $entriesCount = 25;

        for ($index = 1; $index <= $entriesCount; $index++) {
            $createdAt = $baseTimestamp->copy()->addMinutes(($index - 1) * 3 + random_int(0, 2));
            $guess = max(0, $expectedAttendance + random_int(-600, 600));

            GameEntry::query()->withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'game_id' => $game->id,
                'game_round_id' => $round->id,
                'participant_user_id' => null,
                'participant_name' => 'Participante Demo '.$index,
                'participant_email' => 'demo.aforo.'.$index.'@playmatic.local',
                'participant_phone' => '+34 600 000 '.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'status' => 'submitted',
                'score' => null,
                'answer_payload' => [
                    'attendance_guess' => $guess,
                    'consents' => [
                        'terms' => true,
                        'marketing' => false,
                        'third' => false,
                    ],
                ],
                'submitted_at' => $createdAt,
                'evaluated_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    protected function seedSampleQuizQuestionsForTenant(int $tenantId): void
    {
        $questions = [
            [
                'question' => '¿Cuál es la capital de España?',
                'answers' => [
                    ['answer' => 'Madrid', 'is_correct' => true],
                    ['answer' => 'Barcelona', 'is_correct' => false],
                    ['answer' => 'Valencia', 'is_correct' => false],
                ],
            ],
            [
                'question' => '¿Cuántos continentes hay en el planeta Tierra?',
                'answers' => [
                    ['answer' => '5', 'is_correct' => false],
                    ['answer' => '6', 'is_correct' => false],
                    ['answer' => '7', 'is_correct' => true],
                ],
            ],
        ];

        $sortOrder = 1;

        foreach ($questions as $payload) {
            $question = GameQuizQuestion::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'question' => $payload['question'],
                ],
                [
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]
            );

            foreach ($payload['answers'] as $answerPayload) {
                GameQuizAnswer::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'question_id' => $question->id,
                        'answer' => $answerPayload['answer'],
                    ],
                    [
                        'is_correct' => $answerPayload['is_correct'],
                        'correct_question_id' => $answerPayload['is_correct'] ? $question->id : null,
                    ]
                );
            }

            $sortOrder++;
        }
    }
}
