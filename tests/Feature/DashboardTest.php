<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameEntry;
use App\Models\GameRound;
use App\Models\GameTenant;
use App\Models\GameWinner;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_games_statistics(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corporation',
        ]);

        TenantDomain::query()->create([
            'tenant_id' => $tenant->id,
            'domain' => 'acme.playmatic.test',
            'is_primary' => true,
        ]);

        $role = Role::query()->create([
            'name' => 'admin',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);

        $userOne = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
        ]);

        TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userOne->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $gameOne = Game::query()->create([
            'slug' => 'adivina-el-aforo',
            'name' => 'Adivina el aforo',
            'game_type' => 'attendance_guess',
            'is_active' => true,
        ]);

        $gameTwo = Game::query()->create([
            'slug' => 'trivial',
            'name' => 'Trivial',
            'game_type' => 'quiz',
            'is_active' => true,
        ]);

        $gameHidden = Game::query()->create([
            'slug' => 'hidden-game',
            'name' => 'Hidden Game',
            'game_type' => 'quiz',
            'is_active' => true,
        ]);

        GameTenant::query()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameOne->id,
            'is_visible' => true,
        ]);

        GameTenant::query()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameTwo->id,
            'is_visible' => true,
        ]);

        GameTenant::query()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameHidden->id,
            'is_visible' => false,
        ]);

        $openRound = GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameOne->id,
            'name' => 'Round Open',
            'management_mode' => 'manual',
            'activated_at' => now()->subHour(),
            'deactivated_at' => null,
        ]);

        $closedRound = GameRound::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameOne->id,
            'name' => 'Round Closed',
            'management_mode' => 'manual',
            'activated_at' => now()->subHours(3),
            'deactivated_at' => now()->subHours(2),
        ]);

        $entryOne = GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameOne->id,
            'game_round_id' => $openRound->id,
            'participant_name' => 'John',
            'participant_email' => 'john@example.com',
            'status' => 'winner',
        ]);

        $entryTwo = GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameOne->id,
            'game_round_id' => $closedRound->id,
            'participant_name' => 'Jane',
            'participant_email' => 'jane@example.com',
            'status' => 'winner',
        ]);

        GameEntry::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameTwo->id,
            'game_round_id' => null,
            'participant_name' => 'Alex',
            'participant_email' => 'alex@example.com',
            'status' => 'submitted',
        ]);

        GameWinner::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameOne->id,
            'game_round_id' => $openRound->id,
            'game_entry_id' => $entryOne->id,
            'participant_name' => 'John',
            'position' => 1,
        ]);

        GameWinner::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'game_id' => $gameOne->id,
            'game_round_id' => $closedRound->id,
            'game_entry_id' => $entryTwo->id,
            'participant_name' => 'Jane',
            'position' => 2,
        ]);

        $response = $this
            ->actingAs($userOne)
            ->get('http://acme.playmatic.test/en');

        $response->assertOk();
        $response->assertViewHas('totals', fn (array $totals): bool => $totals['active_games'] === 2
            && $totals['total_participants'] === 3
            && $totals['total_winners'] === 2
            && $totals['open_rounds'] === 1
            && $totals['total_plays'] === 3);
        $response->assertViewHas('gameStats', fn ($stats): bool => $stats->count() === 2);
        $response->assertSeeText('Games Dashboard');
        $response->assertSeeText('Plays by game');
    }
}
