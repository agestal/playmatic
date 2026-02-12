@extends('layouts.metronic.auth')

@section('title', __('Adivina el aforo'))

@section('content')
    <div class="d-flex flex-column min-vh-100 align-items-center justify-content-center p-4" style="background: linear-gradient(120deg, var(--pm-surface-start) 0%, var(--pm-surface-end) 100%);">
        <div class="w-100" style="max-width: 760px;">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-8 p-lg-12">
                    <div class="text-center mb-8">
                        <h1 class="fw-bold mb-2">{{ __('Adivina el aforo') }}</h1>
                        <p class="text-muted mb-0">{{ __('Participa y acierta el aforo real del evento activo.') }}</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success mb-6">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
                    @endif

                    @if (! $activeRound)
                        <div class="text-center py-12">
                            <h3 class="fw-bold mb-3">{{ __('No hay concursos activos en este momento.') }}</h3>
                            <p class="text-muted mb-0">{{ __('Vuelve más tarde para participar en una nueva jornada de Adivina el aforo.') }}</p>
                        </div>
                    @else
                        <div class="alert alert-info d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-7">
                            <div>
                                <strong>{{ __('Jornada activa') }}:</strong>
                                {{ $activeRound->name }}
                            </div>
                            @if ($activeRound->management_mode === 'scheduled' && $activeRound->ends_at)
                                <span class="text-muted">{{ __('Cierre') }}: {{ $activeRound->ends_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('public.attendance-guess.store') }}" class="row g-6">
                            @csrf

                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-semibold" for="participant_name">{{ __('Nombre') }}</label>
                                <input
                                    id="participant_name"
                                    name="participant_name"
                                    type="text"
                                    class="form-control form-control-solid"
                                    value="{{ old('participant_name') }}"
                                    required
                                >
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-semibold" for="participant_phone">{{ __('Teléfono') }}</label>
                                <input
                                    id="participant_phone"
                                    name="participant_phone"
                                    type="text"
                                    class="form-control form-control-solid"
                                    value="{{ old('participant_phone') }}"
                                    required
                                >
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-semibold" for="participant_email">{{ __('Email') }}</label>
                                <input
                                    id="participant_email"
                                    name="participant_email"
                                    type="email"
                                    class="form-control form-control-solid"
                                    value="{{ old('participant_email') }}"
                                    required
                                >
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-semibold" for="attendance_guess">{{ __('Tu predicción de aforo') }}</label>
                                <input
                                    id="attendance_guess"
                                    name="attendance_guess"
                                    type="number"
                                    min="0"
                                    step="1"
                                    class="form-control form-control-solid"
                                    value="{{ old('attendance_guess') }}"
                                    required
                                >
                            </div>

                            <div class="col-12">
                                <div class="d-flex flex-column gap-4">
                                    <label class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="accept_terms" value="1" @checked(old('accept_terms')) required>
                                        <span class="form-check-label">{{ __('Acepto términos y condiciones') }}</span>
                                    </label>

                                    <label class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="accept_marketing" value="1" @checked(old('accept_marketing'))>
                                        <span class="form-check-label">{{ __('Acepto recibir publicidad y comunicaciones comerciales') }}</span>
                                    </label>

                                    <label class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="accept_third" value="1" @checked(old('accept_third'))>
                                        <span class="form-check-label">{{ $thirdConsentLabel }}</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary w-100">{{ __('Enviar participación') }}</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
