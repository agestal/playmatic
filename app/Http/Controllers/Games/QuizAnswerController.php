<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\GameQuizAnswer;
use App\Models\GameQuizQuestion;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuizAnswerController extends Controller
{
    public function index(Request $request, TenantContext $tenantContext): View
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $isSuperadmin = (bool) $user->is_superadmin;
        $tenant = $this->tenantOrFail($tenantContext);

        $search = trim(strval($request->query('search', '')));
        $tenantFilter = intval($request->query('tenant_id', $isSuperadmin ? 0 : $tenant->id));
        $questionFilter = intval($request->query('question_id', 0));
        $correctFilter = trim(strval($request->query('is_correct', '')));
        $perPage = intval($request->query('per_page', 10));

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $answersQuery = GameQuizAnswer::query()
            ->withoutGlobalScopes()
            ->with([
                'tenant:id,name',
                'question:id,tenant_id,question',
            ]);

        if (! $isSuperadmin) {
            $answersQuery->where('tenant_id', $tenant->id);
            $tenantFilter = $tenant->id;
        } elseif ($tenantFilter > 0) {
            $answersQuery->where('tenant_id', $tenantFilter);
        }

        if ($questionFilter > 0) {
            $answersQuery->where('question_id', $questionFilter);
        }

        if (in_array($correctFilter, ['0', '1'], true)) {
            $answersQuery->where('is_correct', $correctFilter === '1');
        }

        if ($search !== '') {
            $like = '%'.$search.'%';

            $answersQuery->where(function (Builder $query) use ($like): void {
                $query
                    ->where('answer', 'like', $like)
                    ->orWhereHas('question', fn (Builder $questionQuery) => $questionQuery
                        ->where('question', 'like', $like))
                    ->orWhereHas('tenant', fn (Builder $tenantQuery) => $tenantQuery
                        ->where('name', 'like', $like));
            });
        }

        $answers = $answersQuery
            ->orderByDesc('is_correct')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('games.quiz.answers.index', [
            'answers' => $answers,
            'search' => $search,
            'tenantFilter' => $tenantFilter,
            'questionFilter' => $questionFilter,
            'correctFilter' => $correctFilter,
            'tenantOptions' => $this->tenantOptions(),
            'questionOptions' => $this->questionOptions($isSuperadmin ? $tenantFilter : $tenant->id),
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

        return view('games.quiz.answers.form', [
            'mode' => 'create',
            'answer' => null,
            'isSuperadmin' => $isSuperadmin,
            'tenantOptions' => $tenantOptions,
            'selectedTenantId' => $selectedTenantId,
            'questionOptions' => $this->questionOptions($selectedTenantId),
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

        $questionId = intval($validated['question_id']);
        $isCorrect = (bool) ($validated['is_correct'] ?? false);

        $this->questionForTenantOrFail($questionId, $tenantId);

        if ($isCorrect) {
            $this->assertSingleCorrectAnswer($questionId, $tenantId);
        }

        try {
            GameQuizAnswer::query()
                ->withoutGlobalScopes()
                ->create([
                    'tenant_id' => $tenantId,
                    'question_id' => $questionId,
                    'answer' => trim($validated['answer']),
                    'is_correct' => $isCorrect,
                    'correct_question_id' => $isCorrect ? $questionId : null,
                ]);
        } catch (QueryException $exception) {
            $this->throwSingleCorrectAnswerValidation($exception);
            throw $exception;
        }

        return redirect()
            ->route('games.quiz.answers.index')
            ->with('status', __('Quiz answer created successfully.'));
    }

    public function edit(
        string $locale,
        int $answer,
        Request $request,
        TenantContext $tenantContext
    ): View {
        $answerModel = $this->answerForUserOrFail($answer, $request, $tenantContext);

        return view('games.quiz.answers.form', [
            'mode' => 'edit',
            'answer' => $answerModel,
            'isSuperadmin' => (bool) $request->user()?->is_superadmin,
            'tenantOptions' => $this->tenantOptions(),
            'selectedTenantId' => intval($answerModel->tenant_id),
            'questionOptions' => $this->questionOptions(intval($answerModel->tenant_id)),
        ]);
    }

    public function update(
        Request $request,
        string $locale,
        int $answer,
        TenantContext $tenantContext
    ): RedirectResponse {
        $answerModel = $this->answerForUserOrFail($answer, $request, $tenantContext);
        $validated = $this->validatePayload($request, false);

        $tenantId = intval($answerModel->tenant_id);
        $questionId = intval($validated['question_id']);
        $isCorrect = (bool) ($validated['is_correct'] ?? false);

        $this->questionForTenantOrFail($questionId, $tenantId);

        if ($isCorrect) {
            $this->assertSingleCorrectAnswer($questionId, $tenantId, intval($answerModel->id));
        }

        try {
            $answerModel->update([
                'question_id' => $questionId,
                'answer' => trim($validated['answer']),
                'is_correct' => $isCorrect,
                'correct_question_id' => $isCorrect ? $questionId : null,
            ]);
        } catch (QueryException $exception) {
            $this->throwSingleCorrectAnswerValidation($exception);
            throw $exception;
        }

        return redirect()
            ->route('games.quiz.answers.index')
            ->with('status', __('Quiz answer updated successfully.'));
    }

    public function destroy(
        string $locale,
        int $answer,
        Request $request,
        TenantContext $tenantContext
    ): RedirectResponse {
        $answerModel = $this->answerForUserOrFail($answer, $request, $tenantContext);

        $answerModel->delete();

        return redirect()
            ->route('games.quiz.answers.index')
            ->with('status', __('Quiz answer deleted successfully.'));
    }

    /**
     * @return array{
     *   tenant_id?:int|string,
     *   question_id:int|string,
     *   answer:string,
     *   is_correct?:bool|string|int
     * }
     */
    protected function validatePayload(Request $request, bool $requireTenant): array
    {
        $rules = [
            'question_id' => ['required', 'integer', Rule::exists('games_quiz_questions', 'id')],
            'answer' => ['required', 'string', 'max:4000'],
            'is_correct' => ['nullable', 'boolean'],
        ];

        if ($requireTenant) {
            $rules['tenant_id'] = ['required', 'integer', Rule::exists('tenants', 'id')];
        }

        return $request->validate($rules);
    }

    protected function answerForUserOrFail(
        int $answerId,
        Request $request,
        TenantContext $tenantContext
    ): GameQuizAnswer {
        $answer = GameQuizAnswer::query()
            ->withoutGlobalScopes()
            ->with([
                'tenant:id,name',
                'question:id,tenant_id,question',
            ])
            ->find($answerId);

        if (! $answer) {
            abort(404);
        }

        if (! (bool) $request->user()?->is_superadmin) {
            $tenant = $this->tenantOrFail($tenantContext);

            if ((int) $answer->tenant_id !== (int) $tenant->id) {
                abort(404);
            }
        }

        return $answer;
    }

    protected function questionForTenantOrFail(int $questionId, int $tenantId): GameQuizQuestion
    {
        $question = GameQuizQuestion::query()
            ->withoutGlobalScopes()
            ->where('id', $questionId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $question) {
            throw ValidationException::withMessages([
                'question_id' => __('The selected question does not belong to the selected tenant.'),
            ]);
        }

        return $question;
    }

    protected function assertSingleCorrectAnswer(
        int $questionId,
        int $tenantId,
        ?int $ignoreAnswerId = null
    ): void {
        $existingCorrect = GameQuizAnswer::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('question_id', $questionId)
            ->where('is_correct', true);

        if ($ignoreAnswerId) {
            $existingCorrect->where('id', '!=', $ignoreAnswerId);
        }

        if ($existingCorrect->exists()) {
            throw ValidationException::withMessages([
                'is_correct' => __('Only one correct answer is allowed per question.'),
            ]);
        }
    }

    protected function throwSingleCorrectAnswerValidation(QueryException $exception): void
    {
        if (! str_contains($exception->getMessage(), 'games_quiz_answers_one_correct_per_question_unique')) {
            return;
        }

        throw ValidationException::withMessages([
            'is_correct' => __('Only one correct answer is allowed per question.'),
        ]);
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

    /**
     * @return array<int, array{id:int,label:string}>
     */
    protected function questionOptions(int $tenantId = 0): array
    {
        return GameQuizQuestion::query()
            ->withoutGlobalScopes()
            ->when($tenantId > 0, fn (Builder $query) => $query->where('tenant_id', $tenantId))
            ->with('tenant:id,name')
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->limit(500)
            ->get(['id', 'tenant_id', 'question'])
            ->map(function (GameQuizQuestion $question): array {
                $label = '#'.$question->id.' - '.str($question->question)->limit(90)->toString();

                if ($question->tenant?->name) {
                    $label .= ' ('.$question->tenant->name.')';
                }

                return [
                    'id' => intval($question->id),
                    'label' => $label,
                ];
            })
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
