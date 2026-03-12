<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\GameQuizQuestion;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuizQuestionController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $search = trim(strval($request->query('search', '')));
        $activeFilter = trim(strval($request->query('is_active', '')));
        $tenantFilter = intval($request->query('tenant_id', 0));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $isSuperadmin = (bool) $user->is_superadmin;

        $questionsQuery = GameQuizQuestion::query()
            ->withoutGlobalScopes()
            ->withCount('answers')
            ->with([
                'tenant:id,name',
                'correctAnswer:id,question_id',
            ]);

        if (! $isSuperadmin) {
            $tenant = $this->tenantOrFail($tenantContext);
            $tenantFilter = $tenant->id;
            $questionsQuery->where('tenant_id', $tenant->id);
        } elseif ($tenantFilter > 0) {
            $questionsQuery->where('tenant_id', $tenantFilter);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';

            $questionsQuery->where(function (Builder $query) use ($like): void {
                $query
                    ->where('question', 'like', $like)
                    ->orWhereHas('tenant', fn (Builder $tenantQuery) => $tenantQuery
                        ->where('name', 'like', $like));
            });
        }

        if (in_array($activeFilter, ['0', '1'], true)) {
            $questionsQuery->where('is_active', $activeFilter === '1');
        }

        $questions = $questionsQuery
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('games.quiz.questions.index', [
            'questions' => $questions,
            'search' => $search,
            'activeFilter' => $activeFilter,
            'tenantFilter' => $tenantFilter,
            'tenantOptions' => $this->tenantOptions(),
            'isSuperadmin' => $isSuperadmin,
            'perPage' => $perPage,
        ]);
    }

    public function create(Request $request, TenantContext $tenantContext): View
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $isSuperadmin = (bool) $user->is_superadmin;
        $tenant = $this->tenantOrFail($tenantContext);
        $tenantOptions = $this->tenantOptions();

        $selectedTenantId = $isSuperadmin
            ? intval(old('tenant_id', $request->query('tenant_id', $tenant->id)))
            : $tenant->id;

        if ($isSuperadmin && $selectedTenantId <= 0 && count($tenantOptions) > 0) {
            $selectedTenantId = intval($tenantOptions[0]['id']);
        }

        return view('games.quiz.questions.form', [
            'mode' => 'create',
            'question' => null,
            'isSuperadmin' => $isSuperadmin,
            'tenantOptions' => $tenantOptions,
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function store(Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $isSuperadmin = (bool) $user->is_superadmin;
        $validated = $this->validatePayload($request, $isSuperadmin);

        $tenantId = $isSuperadmin
            ? intval($validated['tenant_id'])
            : $this->tenantOrFail($tenantContext)->id;

        GameQuizQuestion::query()
            ->withoutGlobalScopes()
            ->create([
                'tenant_id' => $tenantId,
                'question' => trim($validated['question']),
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'sort_order' => intval($validated['sort_order'] ?? 0),
            ]);

        return redirect()
            ->route('games.quiz.questions.index')
            ->with('status', __('Quiz question created successfully.'));
    }

    public function edit(
        string $locale,
        int $question,
        Request $request,
        TenantContext $tenantContext
    ): View {
        $questionModel = $this->questionForUserOrFail($question, $request, $tenantContext)
            ->load([
                'answers' => fn ($query) => $query->withoutGlobalScopes()->orderByDesc('is_correct')->orderBy('id'),
            ]);
        $user = $request->user();

        return view('games.quiz.questions.form', [
            'mode' => 'edit',
            'question' => $questionModel,
            'isSuperadmin' => (bool) $user?->is_superadmin,
            'tenantOptions' => $this->tenantOptions(),
            'selectedTenantId' => intval($questionModel->tenant_id),
        ]);
    }

    public function update(
        Request $request,
        string $locale,
        int $question,
        TenantContext $tenantContext
    ): RedirectResponse {
        $questionModel = $this->questionForUserOrFail($question, $request, $tenantContext);
        $validated = $this->validatePayload($request, false);

        $questionModel->update([
            'question' => trim($validated['question']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'sort_order' => intval($validated['sort_order'] ?? 0),
        ]);

        return redirect()
            ->route('games.quiz.questions.index')
            ->with('status', __('Quiz question updated successfully.'));
    }

    public function destroy(
        string $locale,
        int $question,
        Request $request,
        TenantContext $tenantContext
    ): RedirectResponse {
        $questionModel = $this->questionForUserOrFail($question, $request, $tenantContext);

        $questionModel->delete();

        return redirect()
            ->route('games.quiz.questions.index')
            ->with('status', __('Quiz question deleted successfully.'));
    }

    /**
     * @return array{
     *   tenant_id?:int|string,
     *   question:string,
     *   is_active?:bool|string|int,
     *   sort_order?:int|string
     * }
     */
    protected function validatePayload(Request $request, bool $requireTenant): array
    {
        $rules = [
            'question' => ['required', 'string', 'max:4000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];

        if ($requireTenant) {
            $rules['tenant_id'] = ['required', 'integer', Rule::exists('tenants', 'id')];
        }

        return $request->validate($rules);
    }

    protected function questionForUserOrFail(
        int $questionId,
        Request $request,
        TenantContext $tenantContext
    ): GameQuizQuestion {
        $question = GameQuizQuestion::query()
            ->withoutGlobalScopes()
            ->with('tenant:id,name')
            ->find($questionId);

        if (! $question) {
            abort(404);
        }

        if (! (bool) $request->user()?->is_superadmin) {
            $tenant = $this->tenantOrFail($tenantContext);

            if ((int) $question->tenant_id !== (int) $tenant->id) {
                abort(404);
            }
        }

        return $question;
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    protected function tenantOptions(): array
    {
        return Tenant::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Tenant $tenant): array => [
                'id' => intval($tenant->id),
                'name' => $tenant->name,
            ])
            ->all();
    }

    protected function tenantOrFail(TenantContext $tenantContext): Tenant
    {
        $tenant = $tenantContext->tenant();

        if (! $tenant) {
            abort(404, __('There is no active company for this domain.'));
        }

        return $tenant;
    }
}
