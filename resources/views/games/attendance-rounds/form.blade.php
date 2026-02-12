@extends('layouts.metronic.app')

@section('title', $mode === 'create' ? __('Create round') : __('Edit round'))
@section('page_title', $mode === 'create' ? __('Create round') : __('Edit round'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">
                    {{ $mode === 'create' ? __('New attendance round') : __('Edit attendance round #:id', ['id' => $round->id]) }}
                </h3>
                <span class="text-muted fw-semibold fs-7">{{ __('Configure manual or automatic activation for /adivina-aforo.') }}</span>
            </div>

            <div class="card-toolbar">
                <a class="btn btn-light-primary btn-sm" href="{{ route('games.attendance-rounds.index') }}">
                    <i class="ki-duotone ki-left fs-3"></i>
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ $mode === 'create' ? route('games.attendance-rounds.store') : route('games.attendance-rounds.update', ['round' => $round]) }}">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
                @endif

                <div class="row g-6">
                    <div class="col-12 col-lg-7">
                        <label class="form-label fw-semibold" for="name">{{ __('Round name') }}</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            class="form-control form-control-solid"
                            value="{{ old('name', $round?->name) }}"
                            placeholder="{{ __('Example: Matchday 17 - Sunday') }}"
                            required
                        >
                    </div>

                    <div class="col-12 col-lg-5">
                        <label class="form-label fw-semibold" for="management_mode">{{ __('Management mode') }}</label>
                        <select id="management_mode" name="management_mode" class="form-select form-select-solid" required>
                            @foreach ($managementModeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('management_mode', $round?->management_mode ?? 'manual') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">{{ __('Activation behavior') }}</h4>
                                    <div class="fs-6 text-gray-700">
                                        {{ __('Manual mode requires using Activate/Deactivate actions. Automatic mode is active only between start and end date/time.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6" data-schedule-field>
                        <label class="form-label fw-semibold" for="starts_at">{{ __('Starts at') }}</label>
                        <input
                            id="starts_at"
                            name="starts_at"
                            type="datetime-local"
                            class="form-control form-control-solid"
                            value="{{ old('starts_at', $round?->starts_at?->format('Y-m-d\TH:i')) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-6" data-schedule-field>
                        <label class="form-label fw-semibold" for="ends_at">{{ __('Ends at') }}</label>
                        <input
                            id="ends_at"
                            name="ends_at"
                            type="datetime-local"
                            class="form-control form-control-solid"
                            value="{{ old('ends_at', $round?->ends_at?->format('Y-m-d\TH:i')) }}"
                        >
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold" for="result_value">{{ __('Final attendance result') }}</label>
                        <input
                            id="result_value"
                            name="result_value"
                            type="number"
                            min="0"
                            step="1"
                            class="form-control form-control-solid"
                            value="{{ old('result_value', $round?->result_value) }}"
                            placeholder="{{ __('Optional') }}"
                        >
                        <div class="form-text">{{ __('Add this value after the event finishes.') }}</div>
                    </div>

                    @if ($mode === 'edit')
                        <div class="col-12 col-lg-8">
                            <label class="form-label fw-semibold">{{ __('Current state') }}</label>
                            <div class="d-flex flex-wrap gap-3">
                                <span class="badge badge-light">{{ __('Activated') }}: {{ $round->activated_at?->format('d/m/Y H:i') ?: '-' }}</span>
                                <span class="badge badge-light">{{ __('Deactivated') }}: {{ $round->deactivated_at?->format('d/m/Y H:i') ?: '-' }}</span>
                                <span class="badge badge-light">{{ __('Result recorded') }}: {{ $round->result_recorded_at?->format('d/m/Y H:i') ?: '-' }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="pt-8">
                    <button class="btn btn-primary" type="submit">
                        {{ $mode === 'create' ? __('Create round') : __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const modeField = document.getElementById('management_mode');
            const startsAtField = document.getElementById('starts_at');
            const endsAtField = document.getElementById('ends_at');
            const scheduleContainers = document.querySelectorAll('[data-schedule-field]');

            if (!modeField || !startsAtField || !endsAtField || scheduleContainers.length === 0) {
                return;
            }

            const applyMode = function () {
                const isScheduled = modeField.value === 'scheduled';

                scheduleContainers.forEach(function (element) {
                    element.style.display = isScheduled ? '' : 'none';
                });

                startsAtField.required = isScheduled;
                endsAtField.required = isScheduled;
            };

            modeField.addEventListener('change', applyMode);
            applyMode();
        })();
    </script>
@endpush
