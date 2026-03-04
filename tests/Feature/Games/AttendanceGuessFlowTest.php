<?php

namespace Tests\Feature\Games;

use App\Models\Game;
use App\Models\GameAttendanceGuessSetting;
use App\Models\GameEntry;
use App\Models\GameRound;
use App\Models\GameWinner;
use App\Models\GameTenant;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceGuessFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_shows_no_active_contest_message_when_no_round_is_active(): void
    {
        [$tenant, $domain] = $this->createTenantWithDomain();
        $game = $this->createAttendanceGameForTenant($tenant);

        GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'name' => 'Jornada sin activar',
            'management_mode' => 'manual',
            'activated_at' => null,
            'deactivated_at' => null,
        ]);

        $response = $this->get("http://{$domain}/adivina-aforo");

        $response->assertOk();
        $response->assertSeeText('No hay concursos activos en este momento.');
    }

    public function test_public_page_accepts_entries_when_a_round_is_active(): void
    {
        [$tenant, $domain] = $this->createTenantWithDomain();
        $game = $this->createAttendanceGameForTenant($tenant);

        $round = GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'name' => 'Jornada activa automatica',
            'management_mode' => 'scheduled',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'activated_at' => null,
            'deactivated_at' => null,
        ]);

        $response = $this->get("http://{$domain}/adivina-aforo");

        $response->assertOk();
        $response->assertSeeText('Jornada activa automatica');

        $storeResponse = $this->post("http://{$domain}/adivina-aforo", [
            'participant_name' => 'Jane Doe',
            'participant_phone' => '+34 600 111 222',
            'participant_email' => 'jane@example.com',
            'attendance_guess' => 54321,
            'accept_terms' => '1',
        ]);

        $storeResponse->assertSessionHasNoErrors();
        $storeResponse->assertSessionHas('status');

        $this->assertDatabaseHas('games_entries', [
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'participant_name' => 'Jane Doe',
            'participant_phone' => '+34 600 111 222',
            'participant_email' => 'jane@example.com',
        ]);

        $entry = GameEntry::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('game_round_id', $round->id)
            ->firstOrFail();

        $this->assertSame(54321, data_get($entry->answer_payload, 'attendance_guess'));
        $this->assertTrue((bool) data_get($entry->answer_payload, 'consents.terms'));
        $this->assertFalse((bool) data_get($entry->answer_payload, 'consents.marketing'));
    }

    public function test_manual_round_activation_is_blocked_if_another_round_is_active(): void
    {
        [$tenant, $domain] = $this->createTenantWithDomain();
        $game = $this->createAttendanceGameForTenant($tenant);

        GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'name' => 'Jornada activa',
            'management_mode' => 'manual',
            'activated_at' => now()->subMinutes(30),
            'deactivated_at' => null,
        ]);

        $roundToActivate = GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'name' => 'Jornada pendiente',
            'management_mode' => 'manual',
            'activated_at' => null,
            'deactivated_at' => null,
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from("http://{$domain}/".config('app.locale').'/games/attendance-guess/rounds')
            ->post(
                "http://{$domain}".route('games.attendance-rounds.activate', ['round' => $roundToActivate], false)
            );

        $response->assertSessionHasErrors('round');

        $roundToActivate->refresh();

        $this->assertNull($roundToActivate->activated_at);
    }

    public function test_generate_winners_uses_absolute_score_and_created_at_as_tie_breaker(): void
    {
        [$tenant, $domain] = $this->createTenantWithDomain();
        $game = $this->createAttendanceGameForTenant($tenant);
        $user = User::factory()->create();

        $round = GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'name' => 'Jornada cerrada',
            'management_mode' => 'manual',
            'activated_at' => now()->subHours(3),
            'deactivated_at' => now()->subHour(),
            'result_value' => 1000,
            'result_recorded_at' => now()->subHour(),
        ]);

        GameAttendanceGuessSetting::query()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'winners_count' => 2,
            'ranking_enabled' => true,
        ]);

        $bestEntry = GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'participant_name' => 'Best',
            'participant_email' => 'best@example.com',
            'status' => 'submitted',
            'answer_payload' => ['attendance_guess' => 997],
            'submitted_at' => now()->subMinutes(15),
            'created_at' => now()->subMinutes(15),
            'updated_at' => now()->subMinutes(15),
        ]);

        $tieEarlierEntry = GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'participant_name' => 'Tie earlier',
            'participant_email' => 'tie-earlier@example.com',
            'status' => 'submitted',
            'answer_payload' => ['attendance_guess' => 1010],
            'submitted_at' => now()->subMinutes(20),
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);

        $tieLaterEntry = GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'participant_name' => 'Tie later',
            'participant_email' => 'tie-later@example.com',
            'status' => 'submitted',
            'answer_payload' => ['attendance_guess' => 990],
            'submitted_at' => now()->subMinutes(10),
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                "http://{$domain}".route('games.attendance-rounds.generate-winners', ['round' => $round], false)
            );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('games.attendance-rounds.index', absolute: false));

        $this->assertDatabaseHas('games_winners', [
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'game_entry_id' => $bestEntry->id,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('games_winners', [
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'game_entry_id' => $tieEarlierEntry->id,
            'position' => 2,
        ]);

        $this->assertDatabaseMissing('games_winners', [
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'game_entry_id' => $tieLaterEntry->id,
        ]);

        $bestEntry->refresh();
        $tieEarlierEntry->refresh();
        $tieLaterEntry->refresh();

        $this->assertSame('winner', $bestEntry->status);
        $this->assertSame('winner', $tieEarlierEntry->status);
        $this->assertSame('evaluated', $tieLaterEntry->status);
        $this->assertSame('3.00', number_format((float) $bestEntry->score, 2, '.', ''));
        $this->assertSame('10.00', number_format((float) $tieEarlierEntry->score, 2, '.', ''));
        $this->assertSame('10.00', number_format((float) $tieLaterEntry->score, 2, '.', ''));

        $winners = GameWinner::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('game_id', $game->id)
            ->where('game_round_id', $round->id)
            ->orderBy('position')
            ->get();

        $this->assertCount(2, $winners);
        $this->assertSame($bestEntry->id, (int) $winners[0]->game_entry_id);
        $this->assertSame($tieEarlierEntry->id, (int) $winners[1]->game_entry_id);
    }

    public function test_attendance_guess_settings_are_saved_per_tenant(): void
    {
        [$tenantA, $domainA] = $this->createTenantWithDomain('acme.playmatic.test');
        [$tenantB] = $this->createTenantWithDomain('globex.playmatic.test');
        $user = User::factory()->create();

        $game = Game::query()->create([
            'slug' => 'adivina-el-aforo',
            'name' => 'Adivina el aforo',
            'game_type' => 'attendance_guess',
            'description' => 'Juego de prediccion del aforo real.',
            'is_active' => true,
            'config' => [
                'input_type' => 'number',
            ],
        ]);

        GameTenant::query()->create([
            'game_id' => $game->id,
            'tenant_id' => $tenantA->id,
            'is_visible' => true,
        ]);

        GameTenant::query()->create([
            'game_id' => $game->id,
            'tenant_id' => $tenantB->id,
            'is_visible' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                "http://{$domainA}".route('games.attendance-rounds.settings.update', [], false),
                [
                    'winners_count' => 5,
                    'ranking_enabled' => '1',
                ]
            );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('games.attendance-rounds.index', absolute: false));

        $this->assertDatabaseHas('games_attendance_guess_settings', [
            'tenant_id' => $tenantA->id,
            'game_id' => $game->id,
            'winners_count' => 5,
            'ranking_enabled' => 1,
        ]);

        $this->assertDatabaseMissing('games_attendance_guess_settings', [
            'tenant_id' => $tenantB->id,
            'game_id' => $game->id,
        ]);
    }

    public function test_reset_winners_removes_round_winners_and_restores_entry_status(): void
    {
        [$tenant, $domain] = $this->createTenantWithDomain();
        $game = $this->createAttendanceGameForTenant($tenant);
        $user = User::factory()->create();

        $round = GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'name' => 'Jornada con ganadores',
            'management_mode' => 'manual',
            'result_value' => 1000,
        ]);

        $entry = GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'participant_name' => 'Winner entry',
            'participant_email' => 'winner-entry@example.com',
            'status' => 'winner',
            'score' => 2,
            'answer_payload' => ['attendance_guess' => 998],
            'submitted_at' => now()->subMinutes(20),
            'evaluated_at' => now()->subMinutes(10),
        ]);

        GameWinner::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'game_entry_id' => $entry->id,
            'participant_name' => $entry->participant_name,
            'participant_email' => $entry->participant_email,
            'position' => 1,
            'decided_at' => now()->subMinutes(10),
        ]);

        $response = $this
            ->actingAs($user)
            ->post(
                "http://{$domain}".route('games.attendance-rounds.reset-winners', ['round' => $round], false)
            );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('games.attendance-rounds.index', absolute: false));

        $this->assertDatabaseMissing('games_winners', [
            'tenant_id' => $tenant->id,
            'game_id' => $game->id,
            'game_round_id' => $round->id,
            'game_entry_id' => $entry->id,
        ]);

        $entry->refresh();
        $this->assertSame('evaluated', $entry->status);
    }

    /**
     * @return array{Tenant,string}
     */
    protected function createTenantWithDomain(string $domain = 'acme.playmatic.test'): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
        ]);

        TenantDomain::query()->create([
            'tenant_id' => $tenant->id,
            'domain' => $domain,
            'is_primary' => true,
        ]);

        return [$tenant, $domain];
    }

    protected function createAttendanceGameForTenant(Tenant $tenant): Game
    {
        $game = Game::query()->create([
            'slug' => 'adivina-el-aforo',
            'name' => 'Adivina el aforo',
            'game_type' => 'attendance_guess',
            'description' => 'Juego de prediccion del aforo real.',
            'is_active' => true,
            'config' => [
                'input_type' => 'number',
            ],
        ]);

        GameTenant::query()->create([
            'game_id' => $game->id,
            'tenant_id' => $tenant->id,
            'is_visible' => true,
        ]);

        return $game;
    }
}
