<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameTenant;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class GameCatalogSeeder extends Seeder
{
    public function run(): void
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
                'description' => 'Juego de preguntas con una respuesta correcta y varias opciones.',
                'is_active' => true,
                'config' => [
                    'input_type' => 'single_choice',
                    'has_options' => true,
                ],
            ],
        ];

        $tenantIds = Tenant::query()->pluck('id')->all();

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

            foreach ($tenantIds as $tenantId) {
                GameTenant::query()->updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'tenant_id' => (int) $tenantId,
                    ],
                    [
                        'is_visible' => true,
                    ]
                );
            }
        }
    }
}
