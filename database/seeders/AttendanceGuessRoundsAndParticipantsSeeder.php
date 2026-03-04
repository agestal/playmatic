<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameEntry;
use App\Models\GameRound;
use App\Models\GameTenant;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class AttendanceGuessRoundsAndParticipantsSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = $this->resolveTenant();
        $game = $this->resolveGame($tenant->id);

        $roundName = trim((string) env('PLAYMATIC_SEED_ROUND_NAME', ''));
        if ($roundName === '') {
            throw new InvalidArgumentException('Debes definir PLAYMATIC_SEED_ROUND_NAME para este seeder.');
        }

        $participantsCount = max(1, (int) env('PLAYMATIC_SEED_PARTICIPANTS_COUNT', 25));
        $emailPrefix = trim((string) env('PLAYMATIC_SEED_PARTICIPANTS_EMAIL_PREFIX', 'seed.aforo'));
        $roundMode = $this->resolveRoundMode();
        $resetEntries = $this->boolEnv('PLAYMATIC_SEED_RESET_ENTRIES', true);

        $round = GameRound::query()->withoutGlobalScopes()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'game_id' => $game->id,
                'name' => $roundName,
            ],
            [
                'management_mode' => $roundMode,
                'starts_at' => null,
                'ends_at' => null,
                'activated_at' => $roundMode === 'manual' ? now() : null,
                'deactivated_at' => null,
                'result_value' => null,
                'result_recorded_at' => null,
            ]
        );

        if ($resetEntries) {
            GameEntry::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('game_id', $game->id)
                ->where('game_round_id', $round->id)
                ->where('participant_email', 'like', $emailPrefix.'.%@seed.playmatic.local')
                ->delete();
        }

        $baseTimestamp = now()->subHours(2)->startOfMinute();
        $expectedAttendance = random_int(2500, 4500);

        for ($index = 1; $index <= $participantsCount; $index++) {
            $createdAt = $baseTimestamp->copy()->addMinutes(($index - 1) * 2);
            $guess = max(0, $expectedAttendance + random_int(-450, 450));
            $email = $emailPrefix.'.'.$index.'@seed.playmatic.local';

            GameEntry::query()->withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'game_id' => $game->id,
                    'game_round_id' => $round->id,
                    'participant_email' => $email,
                ],
                [
                    'participant_user_id' => null,
                    'participant_name' => 'Participante Seed '.$index,
                    'participant_phone' => '+34 700 000 '.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                    'status' => 'submitted',
                    'score' => null,
                    'answer_payload' => [
                        'attendance_guess' => $guess,
                    ],
                    'submitted_at' => $createdAt,
                    'evaluated_at' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );
        }

        $this->command?->info('Seeder ejecutado: jornada "'.$round->name.'" con '.$participantsCount.' participantes para tenant "'.$tenant->slug.'".');
    }

    protected function resolveTenant(): Tenant
    {
        $tenantId = (int) env('PLAYMATIC_SEED_TENANT_ID', 0);
        if ($tenantId > 0) {
            $tenant = Tenant::query()->find($tenantId);
            if ($tenant) {
                return $tenant;
            }

            throw new InvalidArgumentException('No existe tenant con PLAYMATIC_SEED_TENANT_ID='.$tenantId.'.');
        }

        $tenantSlug = trim((string) env('PLAYMATIC_SEED_TENANT_SLUG', ''));
        if ($tenantSlug === '') {
            throw new InvalidArgumentException('Debes definir PLAYMATIC_SEED_TENANT_ID o PLAYMATIC_SEED_TENANT_SLUG.');
        }

        $tenant = Tenant::query()->where('slug', $tenantSlug)->first();
        if (! $tenant) {
            throw new InvalidArgumentException('No existe tenant con PLAYMATIC_SEED_TENANT_SLUG="'.$tenantSlug.'".');
        }

        return $tenant;
    }

    protected function resolveGame(int $tenantId): Game
    {
        $gameId = (int) env('PLAYMATIC_SEED_GAME_ID', 0);
        if ($gameId > 0) {
            $game = Game::query()->find($gameId);
            if (! $game) {
                throw new InvalidArgumentException('No existe game con PLAYMATIC_SEED_GAME_ID='.$gameId.'.');
            }
        } else {
            $gameSlug = trim((string) env('PLAYMATIC_SEED_GAME_SLUG', 'adivina-el-aforo'));
            $game = Game::query()->where('slug', $gameSlug)->first();
            if (! $game) {
                throw new InvalidArgumentException('No existe game con PLAYMATIC_SEED_GAME_SLUG="'.$gameSlug.'".');
            }
        }

        $isLinkedToTenant = GameTenant::query()
            ->where('tenant_id', $tenantId)
            ->where('game_id', $game->id)
            ->exists();

        if (! $isLinkedToTenant) {
            throw new InvalidArgumentException(
                'El juego seleccionado no está vinculado al tenant (tabla games_tenants).'
            );
        }

        return $game;
    }

    protected function resolveRoundMode(): string
    {
        $mode = trim((string) env('PLAYMATIC_SEED_ROUND_MANAGEMENT_MODE', 'manual'));
        if (! in_array($mode, ['manual', 'scheduled'], true)) {
            throw new InvalidArgumentException(
                'PLAYMATIC_SEED_ROUND_MANAGEMENT_MODE debe ser "manual" o "scheduled".'
            );
        }

        return $mode;
    }

    protected function boolEnv(string $key, bool $default): bool
    {
        return filter_var(env($key, $default), FILTER_VALIDATE_BOOL);
    }
}
