@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('Create game entry') : __('Edit game entry'))
@section('page_title', $mode === 'create' ? __('Create game entry') : __('Edit game entry'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">
                    {{ $mode === 'create' ? __('New game entry') : __('Edit game entry #:id', ['id' => $entry->id]) }}
                </h3>
                <span class="text-muted fw-semibold fs-7">{{ __('Use this CRUD to register participant submissions for each game.') }}</span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('games.entries.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ $mode === 'create' ? route('games.entries.store') : route('games.entries.update', ['entry' => $entry]) }}">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
                @endif

                <div class="row g-6">
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="game_id">{{ __('Game') }}</label>
                        <select id="game_id" name="game_id" class="form-select form-select-solid" required>
                            <option value="">{{ __('Select game') }}</option>
                            @foreach ($games as $gameOption)
                                <option
                                    value="{{ $gameOption['id'] }}"
                                    @selected((int) old('game_id', $entry?->game_id) === (int) $gameOption['id'])
                                >
                                    {{ $gameOption['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="participant_user_id">{{ __('Participant user (optional)') }}</label>
                        <select id="participant_user_id" name="participant_user_id" class="form-select form-select-solid">
                            <option value="">{{ __('No user linked') }}</option>
                            @foreach ($participants as $participant)
                                <option
                                    value="{{ $participant['id'] }}"
                                    @selected((int) old('participant_user_id', $entry?->participant_user_id) === (int) $participant['id'])
                                >
                                    {{ $participant['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="participant_name">{{ __('Participant name') }}</label>
                        <input
                            id="participant_name"
                            name="participant_name"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('participant_name', $entry?->participant_name) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="participant_email">{{ __('Participant email') }}</label>
                        <input
                            id="participant_email"
                            name="participant_email"
                            type="email"
                            class="form-control form-control-solid"
                            value="{{ old('participant_email', $entry?->participant_email) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="participant_phone">{{ __('Participant phone') }}</label>
                        <input
                            id="participant_phone"
                            name="participant_phone"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('participant_phone', $entry?->participant_phone) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="status">{{ __('Status') }}</label>
                        <select id="status" name="status" class="form-select form-select-solid" required>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $entry?->status ?? 'submitted') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="score">{{ __('Score') }}</label>
                        <input
                            id="score"
                            name="score"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-control form-control-solid"
                            value="{{ old('score', $entry?->score) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="submitted_at">{{ __('Submitted at') }}</label>
                        <input
                            id="submitted_at"
                            name="submitted_at"
                            type="datetime-local"
                            class="form-control form-control-solid"
                            value="{{ old('submitted_at', $entry?->submitted_at?->format('Y-m-d\TH:i')) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="evaluated_at">{{ __('Evaluated at') }}</label>
                        <input
                            id="evaluated_at"
                            name="evaluated_at"
                            type="datetime-local"
                            class="form-control form-control-solid"
                            value="{{ old('evaluated_at', $entry?->evaluated_at?->format('Y-m-d\TH:i')) }}"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="answer_payload">{{ __('Answer payload (JSON)') }}</label>
                        <textarea
                            id="answer_payload"
                            name="answer_payload"
                            rows="7"
                            class="form-control form-control-solid font-monospace"
                            placeholder='{"answer": 42000}'
                        >{{ old('answer_payload', $entry ? json_encode($entry->answer_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    </div>
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit">
                        {{ $mode === 'create' ? __('Create entry') : __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
