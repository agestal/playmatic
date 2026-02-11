@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('Create game winner') : __('Edit game winner'))
@section('page_title', $mode === 'create' ? __('Create game winner') : __('Edit game winner'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">
                    {{ $mode === 'create' ? __('New winner') : __('Edit winner #:id', ['id' => $winner->id]) }}
                </h3>
                <span class="text-muted fw-semibold fs-7">{{ __('Link winners to an existing entry or register them manually.') }}</span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('games.winners.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ $mode === 'create' ? route('games.winners.store') : route('games.winners.update', ['winner' => $winner]) }}">
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
                                    @selected((int) old('game_id', $winner?->game_id) === (int) $gameOption['id'])
                                >
                                    {{ $gameOption['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="game_entry_id">{{ __('Entry (optional)') }}</label>
                        <select id="game_entry_id" name="game_entry_id" class="form-select form-select-solid">
                            <option value="">{{ __('No linked entry') }}</option>
                            @foreach ($entries as $entry)
                                <option
                                    value="{{ $entry['id'] }}"
                                    @selected((int) old('game_entry_id', $winner?->game_entry_id) === (int) $entry['id'])
                                >
                                    {{ $entry['label'] }}
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
                                    @selected((int) old('participant_user_id', $winner?->participant_user_id) === (int) $participant['id'])
                                >
                                    {{ $participant['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="form-label fw-semibold" for="position">{{ __('Position') }}</label>
                        <input
                            id="position"
                            name="position"
                            type="number"
                            min="1"
                            max="1000"
                            class="form-control form-control-solid"
                            value="{{ old('position', $winner?->position ?? 1) }}"
                            required
                        >
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="form-label fw-semibold" for="decided_at">{{ __('Decided at') }}</label>
                        <input
                            id="decided_at"
                            name="decided_at"
                            type="datetime-local"
                            class="form-control form-control-solid"
                            value="{{ old('decided_at', $winner?->decided_at?->format('Y-m-d\TH:i')) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="participant_name">{{ __('Participant name') }}</label>
                        <input
                            id="participant_name"
                            name="participant_name"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('participant_name', $winner?->participant_name) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="participant_email">{{ __('Participant email') }}</label>
                        <input
                            id="participant_email"
                            name="participant_email"
                            type="email"
                            class="form-control form-control-solid"
                            value="{{ old('participant_email', $winner?->participant_email) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prize_name">{{ __('Prize name') }}</label>
                        <input
                            id="prize_name"
                            name="prize_name"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('prize_name', $winner?->prize_name) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold" for="prize_value">{{ __('Prize value') }}</label>
                        <input
                            id="prize_value"
                            name="prize_value"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('prize_value', $winner?->prize_value) }}"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="winner_payload">{{ __('Winner payload (JSON)') }}</label>
                        <textarea
                            id="winner_payload"
                            name="winner_payload"
                            rows="6"
                            class="form-control form-control-solid font-monospace"
                            placeholder='{"reason":"closest score"}'
                        >{{ old('winner_payload', $winner ? json_encode($winner->winner_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold" for="notes">{{ __('Notes') }}</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            class="form-control form-control-solid"
                        >{{ old('notes', $winner?->notes) }}</textarea>
                    </div>
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit">
                        {{ $mode === 'create' ? __('Create winner') : __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
