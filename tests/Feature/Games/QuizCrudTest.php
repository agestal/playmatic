<?php

namespace Tests\Feature\Games;

use App\Http\Middleware\ResolveTenantFromDomain;
use App\Models\Game;
use App\Models\GameQuizAnswer;
use App\Models\GameQuizQuestion;
use App\Models\GameTenant;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_user_can_create_quiz_question_for_its_tenant(): void
    {
        [$tenant] = $this->createTenantContextWithTrivialGame();

        $user = User::factory()->create([
            'is_superadmin' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('games.quiz.questions.store'), [
                'question' => '¿Cuál es el color del escudo?',
                'is_active' => '1',
                'sort_order' => '10',
            ]);

        $response->assertRedirect(route('games.quiz.questions.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('games_quiz_questions', [
            'tenant_id' => $tenant->id,
            'question' => '¿Cuál es el color del escudo?',
            'is_active' => 1,
            'sort_order' => 10,
        ]);
    }

    public function test_tenant_user_cannot_access_quiz_routes_when_trivial_is_not_enabled_for_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Tenant Without Quiz',
            'slug' => 'tenant-without-quiz',
        ]);

        $this->setTenantContext($tenant);
        $this->withoutMiddleware(ResolveTenantFromDomain::class);

        $user = User::factory()->create([
            'is_superadmin' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('games.quiz.questions.index'));

        $response->assertNotFound();
    }

    public function test_superadmin_can_manage_questions_across_tenants(): void
    {
        [$tenantA, $tenantB] = $this->createTwoTenantsWithTrivialGame();
        $this->setTenantContext($tenantA);
        $this->withoutMiddleware(ResolveTenantFromDomain::class);

        GameQuizQuestion::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'question' => 'Pregunta tenant A',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        GameQuizQuestion::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'question' => 'Pregunta tenant B',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $superadmin = User::factory()->create([
            'is_superadmin' => true,
        ]);

        $listResponse = $this
            ->actingAs($superadmin)
            ->get(route('games.quiz.questions.index'));

        $listResponse->assertOk();
        $listResponse->assertSeeText('Pregunta tenant A');
        $listResponse->assertSeeText('Pregunta tenant B');

        $createResponse = $this
            ->actingAs($superadmin)
            ->post(route('games.quiz.questions.store'), [
                'tenant_id' => $tenantB->id,
                'question' => 'Nueva pregunta tenant B',
                'is_active' => '1',
                'sort_order' => '30',
            ]);

        $createResponse->assertRedirect(route('games.quiz.questions.index'));
        $createResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('games_quiz_questions', [
            'tenant_id' => $tenantB->id,
            'question' => 'Nueva pregunta tenant B',
            'is_active' => 1,
            'sort_order' => 30,
        ]);
    }

    public function test_only_one_correct_answer_is_allowed_per_question(): void
    {
        [$tenant] = $this->createTenantContextWithTrivialGame();

        $question = GameQuizQuestion::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'question' => '¿Quién ganó la liga?',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $user = User::factory()->create([
            'is_superadmin' => false,
        ]);

        $firstResponse = $this
            ->actingAs($user)
            ->post(route('games.quiz.answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Equipo A',
                'is_correct' => '1',
            ]);

        $firstResponse->assertRedirect(route('games.quiz.answers.index'));
        $firstResponse->assertSessionHasNoErrors();

        $secondResponse = $this
            ->actingAs($user)
            ->from(route('games.quiz.answers.create'))
            ->post(route('games.quiz.answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Equipo B',
                'is_correct' => '1',
            ]);

        $secondResponse->assertRedirect(route('games.quiz.answers.create'));
        $secondResponse->assertSessionHasErrors('is_correct');

        $this->assertSame(
            1,
            GameQuizAnswer::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('question_id', $question->id)
                ->where('is_correct', true)
                ->count()
        );
    }

    /**
     * @return array{Tenant}
     */
    protected function createTenantContextWithTrivialGame(): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Quiz',
            'slug' => 'acme-quiz',
        ]);

        $trivial = Game::query()->create([
            'slug' => 'trivial',
            'name' => 'Trivial',
            'game_type' => 'quiz',
            'description' => 'Juego de quiz por tenant.',
            'is_active' => true,
        ]);

        GameTenant::query()->create([
            'game_id' => $trivial->id,
            'tenant_id' => $tenant->id,
            'is_visible' => true,
        ]);

        $this->setTenantContext($tenant);
        $this->withoutMiddleware(ResolveTenantFromDomain::class);

        return [$tenant];
    }

    /**
     * @return array{Tenant,Tenant}
     */
    protected function createTwoTenantsWithTrivialGame(): array
    {
        $tenantA = Tenant::query()->create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
        ]);

        $tenantB = Tenant::query()->create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
        ]);

        $trivial = Game::query()->create([
            'slug' => 'trivial',
            'name' => 'Trivial',
            'game_type' => 'quiz',
            'description' => 'Juego de quiz por tenant.',
            'is_active' => true,
        ]);

        GameTenant::query()->create([
            'game_id' => $trivial->id,
            'tenant_id' => $tenantA->id,
            'is_visible' => true,
        ]);

        GameTenant::query()->create([
            'game_id' => $trivial->id,
            'tenant_id' => $tenantB->id,
            'is_visible' => true,
        ]);

        return [$tenantA, $tenantB];
    }

    protected function setTenantContext(Tenant $tenant): void
    {
        $tenantContext = new TenantContext();
        $tenantContext->setTenant($tenant);
        $this->app->instance(TenantContext::class, $tenantContext);
    }
}
