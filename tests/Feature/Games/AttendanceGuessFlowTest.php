<?php

namespace Tests\Feature\Games;

use App\Models\Game;
use App\Models\GameEntry;
use App\Models\GameRound;
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
