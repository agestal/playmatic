@extends('layouts.metronic.app')

@section('title', __('Attendance guess configuration'))
@section('page_title', __('Attendance guess configuration'))

@section('content')
    @if (session('status'))
        <div class="alert alert-success mb-6">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-1">{{ __('Attendance guess configuration') }}</h3>
                <span class="text-muted fw-semibold fs-7">{{ __('These values are configured independently for each tenant.') }}</span>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="alert alert-info d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-6">
                <div>
                    <strong>{{ __('Public URL') }}:</strong>
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicUrl }}</a>
                </div>
                <span class="text-muted small">{{ __('Configure manual or automatic activation for /adivina-aforo.') }}</span>
            </div>

            @can('games.edit.content')
                <form method="POST" action="{{ route('games.attendance-rounds.settings.update') }}">
                    @csrf
                    <div class="row g-6">
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold" for="winners_count">{{ __('Number of winners') }}</label>
                            <input
                                id="winners_count"
                                name="winners_count"
                                type="number"
                                min="1"
                                max="500"
                                class="form-control form-control-solid"
                                value="{{ old('winners_count', $attendanceSettings?->winners_count ?? 1) }}"
                                required
                            >
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold" for="ranking_enabled">{{ __('Enable ranking') }}</label>
                            <div class="form-check form-switch form-check-custom form-check-solid mt-2">
                                <input type="hidden" name="ranking_enabled" value="0">
                                <input
                                    class="form-check-input"
                                    id="ranking_enabled"
                                    name="ranking_enabled"
                                    type="checkbox"
                                    value="1"
                                    @checked((bool) old('ranking_enabled', $attendanceSettings?->ranking_enabled ?? false))
                                >
                                <label class="form-check-label" for="ranking_enabled">{{ __('Ranking enabled') }}</label>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                        </div>
                    </div>
                </form>
            @else
                <div class="row g-6">
                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold">{{ __('Number of winners') }}</label>
                        <div class="form-control form-control-solid">{{ $attendanceSettings?->winners_count ?? 1 }}</div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-semibold">{{ __('Ranking enabled') }}</label>
                        <div class="form-control form-control-solid">
                            {{ ($attendanceSettings?->ranking_enabled ?? false) ? __('Yes') : __('No') }}
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
