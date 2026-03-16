@extends('layouts.metronic.auth')

@section('title', __('Adivina el aforo'))

@php
    $initialGuess = old('attendance_guess');
    $formattedMaxCapacity = $maxCapacity ? number_format($maxCapacity, 0, ',', '.') : null;
@endphp

@push('styles')
    <style>
        .attendance-guess-page {
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at top, rgba(var(--pm-primary-rgb), 0.28), transparent 32%),
                radial-gradient(circle at bottom right, rgba(var(--pm-secondary-rgb), 0.18), transparent 28%),
                linear-gradient(135deg, #07141d 0%, #0c2430 45%, #122d1e 100%);
            position: relative;
        }

        .attendance-guess-page::before,
        .attendance-guess-page::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .attendance-guess-page::before {
            background:
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 42px 42px;
            opacity: 0.18;
        }

        .attendance-guess-page::after {
            background: radial-gradient(circle, transparent 52%, rgba(1, 8, 13, 0.55) 100%);
        }

        .attendance-stage {
            position: relative;
            z-index: 1;
            width: min(1200px, calc(100vw - 2rem));
            margin: 0 auto;
            padding: 2rem 0;
            opacity: 0;
            transform: translateY(44px) scale(0.96);
            animation: attendance-stage-enter 0.9s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .attendance-hero {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            color: #f4fbf6;
        }

        .attendance-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: fit-content;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .attendance-hero h1 {
            margin: 0;
            font-size: clamp(2.4rem, 5vw, 4.8rem);
            font-weight: 800;
            line-height: 0.95;
            color: #fff;
            text-shadow: 0 18px 40px rgba(0, 0, 0, 0.34);
        }

        .attendance-hero p {
            margin: 0;
            max-width: 760px;
            color: rgba(244, 251, 246, 0.78);
            font-size: 1.05rem;
        }

        .attendance-board {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .attendance-chip {
            min-height: 96px;
            padding: 1rem 1.15rem;
            border-radius: 22px;
            background: rgba(8, 16, 22, 0.52);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(18px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
        }

        .attendance-chip-label {
            display: block;
            margin-bottom: 0.4rem;
            color: rgba(244, 251, 246, 0.62);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .attendance-chip-value {
            color: #fff;
            font-size: clamp(1.25rem, 3vw, 2rem);
            font-weight: 800;
        }

        .attendance-stadium {
            position: relative;
            min-height: 780px;
            padding: 1.5rem;
            border-radius: 42px;
            background:
                radial-gradient(circle at center, rgba(255, 255, 255, 0.08), transparent 55%),
                linear-gradient(145deg, rgba(7, 18, 24, 0.96), rgba(11, 28, 37, 0.9));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow:
                0 36px 90px rgba(1, 6, 10, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .attendance-stadium::before {
            content: "";
            position: absolute;
            inset: 1rem;
            border-radius: 36px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            pointer-events: none;
        }

        .attendance-seats {
            position: absolute;
            inset: 2rem;
            border-radius: 36px;
            background:
                radial-gradient(ellipse at center, rgba(255, 255, 255, 0.05) 0%, transparent 56%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.06), transparent 18%, transparent 82%, rgba(255, 255, 255, 0.05));
            overflow: hidden;
        }

        .attendance-seat {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(203, 223, 216, 0.15);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: background-color 0.28s ease, transform 0.28s ease, box-shadow 0.28s ease;
        }

        .attendance-seat.is-filled {
            background: #f3f6ce;
            box-shadow: 0 0 12px rgba(243, 246, 206, 0.45);
            transform: scale(1.2);
        }

        .attendance-pitch-shell {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 740px;
            z-index: 1;
        }

        .attendance-pitch {
            position: relative;
            width: min(760px, 100%);
            min-height: 620px;
            padding: 2rem;
            border-radius: 170px / 120px;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                linear-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                linear-gradient(180deg, #2c8d48 0%, #267942 50%, #1f6136 100%);
            background-size: 70px 70px, 70px 70px, cover;
            box-shadow:
                inset 0 0 0 8px rgba(255, 255, 255, 0.1),
                inset 0 0 0 80px rgba(18, 70, 38, 0.18),
                0 24px 70px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }

        .attendance-pitch::before,
        .attendance-pitch::after {
            content: "";
            position: absolute;
            inset: 7.5%;
            border: 3px solid rgba(255, 255, 255, 0.7);
            border-radius: 120px / 82px;
            pointer-events: none;
        }

        .attendance-pitch::after {
            inset: 50% 24% auto 24%;
            height: 0;
            border-width: 0;
            border-top: 3px solid rgba(255, 255, 255, 0.72);
            border-radius: 0;
        }

        .attendance-center-circle,
        .attendance-center-dot,
        .attendance-box {
            position: absolute;
            pointer-events: none;
        }

        .attendance-center-circle {
            top: 50%;
            left: 50%;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.72);
            transform: translate(-50%, -50%);
        }

        .attendance-center-dot {
            top: 50%;
            left: 50%;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
            transform: translate(-50%, -50%);
        }

        .attendance-box {
            left: 50%;
            width: 44%;
            height: 92px;
            transform: translateX(-50%);
            border: 3px solid rgba(255, 255, 255, 0.72);
            border-radius: 0 0 56px 56px;
            border-top: 0;
        }

        .attendance-box.top {
            top: 7.5%;
            transform: translateX(-50%) rotate(180deg);
        }

        .attendance-box.bottom {
            bottom: 7.5%;
        }

        .attendance-form-shell {
            position: relative;
            width: min(560px, 100%);
            padding: 1.5rem;
            border-radius: 34px;
            background: rgba(7, 20, 16, 0.22);
            opacity: 0;
            transform: translateY(26px);
            animation: attendance-form-enter 0.8s 0.18s ease-out forwards;
        }

        .attendance-form-panel {
            position: relative;
            padding: 1.5rem;
            border-radius: 28px;
            background: rgba(246, 253, 248, 0.92);
            box-shadow: 0 24px 50px rgba(8, 20, 13, 0.24);
        }

        .attendance-alert {
            border: 0;
            border-radius: 18px;
            backdrop-filter: blur(12px);
        }

        .attendance-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .attendance-field-full {
            grid-column: 1 / -1;
        }

        .attendance-capacity-input {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-align: center;
        }

        .attendance-capacity-note {
            margin-top: 0.55rem;
            color: #4c6654;
            font-size: 0.92rem;
            text-align: center;
        }

        .attendance-progress {
            margin-top: 1rem;
            padding: 1rem 1.1rem;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(var(--pm-primary-rgb), 0.12), rgba(var(--pm-secondary-rgb), 0.08));
        }

        .attendance-progress-top {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .attendance-progress-label {
            display: block;
            margin-bottom: 0.3rem;
            color: #5d7462;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .attendance-progress-value {
            color: #153222;
            font-size: clamp(1.7rem, 4vw, 2.6rem);
            font-weight: 800;
            line-height: 1;
        }

        .attendance-progress-track {
            height: 14px;
            border-radius: 999px;
            background: rgba(17, 39, 28, 0.12);
            overflow: hidden;
        }

        .attendance-progress-bar {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--pm-primary), #f2d35c);
            transition: width 0.3s ease;
        }

        .attendance-consents {
            display: grid;
            gap: 0.7rem;
        }

        .attendance-empty {
            max-width: 680px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
            color: #fff;
        }

        @keyframes attendance-stage-enter {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes attendance-form-enter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991.98px) {
            .attendance-board {
                grid-template-columns: 1fr;
            }

            .attendance-stadium {
                min-height: auto;
            }

            .attendance-pitch-shell {
                min-height: auto;
                padding: 1rem 0;
            }

            .attendance-pitch {
                min-height: auto;
                padding: 5rem 1rem;
                border-radius: 56px;
            }

            .attendance-pitch::before {
                inset: 8%;
                border-radius: 40px;
            }

            .attendance-box {
                width: 58%;
            }
        }

        @media (max-width: 767.98px) {
            .attendance-stage {
                width: min(100vw - 1rem, 1200px);
                padding: 0.5rem 0 1.5rem;
            }

            .attendance-fields {
                grid-template-columns: 1fr;
            }

            .attendance-progress-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .attendance-seat {
                width: 7px;
                height: 7px;
            }

            .attendance-pitch {
                padding: 4.25rem 0.85rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="attendance-guess-page d-flex align-items-center justify-content-center p-3 p-lg-6">
        <div class="attendance-stage">
            <div class="attendance-hero">
                <div class="attendance-kicker">
                    <span>{{ $tenant->name }}</span>
                    @if ($activeRound)
                        <span>•</span>
                        <span>{{ $activeRound->name }}</span>
                    @endif
                </div>

                <div>
                    <h1>{{ __('Adivina el aforo') }}</h1>
                    <p>{{ __('Participa y acierta el aforo real del evento activo.') }}</p>
                </div>

                @if ($activeRound)
                    <div class="attendance-board">
                        <div class="attendance-chip">
                            <span class="attendance-chip-label">{{ __('Jornada activa') }}</span>
                            <div class="attendance-chip-value">{{ $activeRound->name }}</div>
                        </div>
                        <div class="attendance-chip">
                            <span class="attendance-chip-label">Aforo máximo</span>
                            <div class="attendance-chip-value">{{ $formattedMaxCapacity ?? 'Sin definir' }}</div>
                        </div>
                        <div class="attendance-chip">
                            <span class="attendance-chip-label">Estado estimado</span>
                            <div class="attendance-chip-value" id="attendance-stage-status">Introduce tu cifra</div>
                        </div>
                    </div>
                @endif
            </div>

            @if (session('status'))
                <div class="alert alert-success attendance-alert mb-5">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger attendance-alert mb-5">{{ $errors->first() }}</div>
            @endif

            @if (! $activeRound)
                <div class="attendance-stadium d-flex align-items-center justify-content-center">
                    <div class="attendance-empty">
                        <h3 class="fw-bold mb-3">{{ __('No hay concursos activos en este momento.') }}</h3>
                        <p class="mb-0">{{ __('Vuelve más tarde para participar en una nueva jornada de Adivina el aforo.') }}</p>
                    </div>
                </div>
            @else
                <div class="attendance-stadium">
                    <div class="attendance-seats" id="attendance-seats" aria-hidden="true"></div>

                    <div class="attendance-pitch-shell">
                        <div class="attendance-pitch">
                            <div class="attendance-center-circle"></div>
                            <div class="attendance-center-dot"></div>
                            <div class="attendance-box top"></div>
                            <div class="attendance-box bottom"></div>

                            <div class="attendance-form-shell">
                                <div class="attendance-form-panel">
                                    <form
                                        method="POST"
                                        action="{{ route('public.attendance-guess.store') }}"
                                        data-attendance-form
                                        data-max-capacity="{{ $maxCapacity ?? '' }}"
                                        data-initial-guess="{{ $initialGuess ?? '' }}"
                                    >
                                        @csrf

                                        <div class="attendance-fields">
                                            <div>
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

                                            <div>
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

                                            <div>
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

                                            <div>
                                                <label class="form-label fw-semibold" for="attendance_guess">{{ __('Tu predicción de aforo') }}</label>
                                                <input
                                                    id="attendance_guess"
                                                    name="attendance_guess"
                                                    type="number"
                                                    min="0"
                                                    @if ($maxCapacity) max="{{ $maxCapacity }}" @endif
                                                    step="1"
                                                    class="form-control form-control-solid attendance-capacity-input"
                                                    value="{{ $initialGuess }}"
                                                    required
                                                >
                                                <div class="attendance-capacity-note">
                                                    @if ($maxCapacity)
                                                        Máximo disponible: {{ $formattedMaxCapacity }} espectadores.
                                                    @else
                                                        Define el aforo máximo en configuración para activar la previsualización exacta de ocupación.
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="attendance-progress">
                                            <div class="attendance-progress-top">
                                                <div>
                                                    <span class="attendance-progress-label">Ocupación estimada</span>
                                                    <div class="attendance-progress-value" id="attendance-progress-value">0%</div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="attendance-progress-label">Visualización</span>
                                                    <div class="fw-semibold text-dark" id="attendance-progress-note">
                                                        Esperando tu predicción
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="attendance-progress-track">
                                                <div class="attendance-progress-bar" id="attendance-progress-bar"></div>
                                            </div>
                                        </div>

                                        <div class="attendance-consents mt-5">
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

                                        <div class="mt-5">
                                            <button type="submit" class="btn btn-primary w-100 btn-lg">{{ __('Enviar participación') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-attendance-form]');

            if (!form) {
                return;
            }

            const input = form.querySelector('#attendance_guess');
            const seatsLayer = document.getElementById('attendance-seats');
            const progressValue = document.getElementById('attendance-progress-value');
            const progressBar = document.getElementById('attendance-progress-bar');
            const progressNote = document.getElementById('attendance-progress-note');
            const stageStatus = document.getElementById('attendance-stage-status');

            const maxCapacity = Number(form.dataset.maxCapacity || 0);
            const totalSeats = window.innerWidth < 768 ? 180 : 260;
            const seats = [];

            const formatNumber = (value) => new Intl.NumberFormat('es-ES').format(value);

            const seededIndex = (seed, index, length) => {
                const raw = Math.sin((seed + 1) * (index + 3) * 12.9898) * 43758.5453;
                return Math.abs(Math.floor(raw)) % length;
            };

            const createSeat = (x, y) => {
                const seat = document.createElement('span');
                seat.className = 'attendance-seat';
                seat.style.left = `${x}%`;
                seat.style.top = `${y}%`;
                seatsLayer.appendChild(seat);
                seats.push(seat);
            };

            const buildSeats = () => {
                for (let i = 0; i < totalSeats; i += 1) {
                    const ratio = i / totalSeats;
                    const angle = ratio * Math.PI * 2;
                    const xRadius = 44;
                    const yRadius = 34;
                    const ringOffset = i % 4;
                    const x = 50 + Math.cos(angle) * (xRadius + ringOffset * 1.7);
                    const y = 50 + Math.sin(angle) * (yRadius + ringOffset * 1.4);

                    if (x > 16 && x < 84 && y > 18 && y < 82) {
                        continue;
                    }

                    createSeat(x, y);
                }
            };

            const occupancyLabel = (ratio) => {
                if (ratio <= 0) {
                    return 'Esperando tu predicción';
                }

                if (ratio < 0.25) {
                    return 'Entrada floja';
                }

                if (ratio < 0.5) {
                    return 'Media entrada';
                }

                if (ratio < 0.8) {
                    return 'Buen ambiente';
                }

                if (ratio < 0.95) {
                    return 'Casi lleno';
                }

                return 'Lleno técnico';
            };

            const paintSeats = (guess) => {
                const ratio = maxCapacity > 0 ? Math.max(0, Math.min(guess / maxCapacity, 1)) : 0;
                const filledSeats = Math.round(seats.length * ratio);
                const filledIndexes = new Set();

                for (let i = 0; i < filledSeats; i += 1) {
                    filledIndexes.add(seededIndex(guess || 1, i, seats.length));
                }

                seats.forEach((seat, index) => {
                    seat.classList.toggle('is-filled', filledIndexes.has(index));
                });

                progressValue.textContent = `${Math.round(ratio * 100)}%`;
                progressBar.style.width = `${ratio * 100}%`;
                progressNote.textContent = maxCapacity > 0
                    ? `${formatNumber(Math.min(guess, maxCapacity))} / ${formatNumber(maxCapacity)} espectadores`
                    : 'Configura un aforo máximo para calibrar la simulación';

                if (stageStatus) {
                    stageStatus.textContent = occupancyLabel(ratio);
                }
            };

            buildSeats();
            paintSeats(Number(input.value || 0));

            input.addEventListener('input', () => {
                const guess = Math.max(0, Number(input.value || 0));
                paintSeats(guess);
            });
        })();
    </script>
@endpush
