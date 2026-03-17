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
            width: min(1320px, calc(100vw - 1.5rem));
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
            min-height: 900px;
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

        .attendance-arena {
            position: relative;
            width: min(1040px, 100%);
            min-height: 860px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .attendance-visual {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .attendance-architecture {
            position: absolute;
            inset: 0;
            border-radius: 48% / 40%;
            pointer-events: none;
        }

        .attendance-architecture::before,
        .attendance-architecture::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
        }

        .attendance-architecture::before {
            background:
                radial-gradient(ellipse at center, rgba(4, 11, 16, 0) 0 38%, rgba(4, 11, 16, 0.9) 63%, rgba(2, 8, 13, 0.98) 100%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 26%, transparent 74%, rgba(255, 255, 255, 0.04));
            box-shadow:
                inset 0 0 0 2px rgba(255, 255, 255, 0.06),
                inset 0 20px 50px rgba(255, 255, 255, 0.04),
                inset 0 -40px 60px rgba(0, 0, 0, 0.3);
        }

        .attendance-architecture::after {
            inset: 6.5%;
            border-radius: 46% / 38%;
            box-shadow:
                inset 0 0 0 18px rgba(255, 255, 255, 0.03),
                inset 0 0 0 48px rgba(255, 255, 255, 0.018),
                inset 0 0 0 82px rgba(255, 255, 255, 0.01);
        }

        .attendance-stand {
            position: absolute;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow:
                inset 0 14px 24px rgba(255, 255, 255, 0.05),
                inset 0 -18px 30px rgba(0, 0, 0, 0.28),
                0 18px 40px rgba(0, 0, 0, 0.14);
            background:
                linear-gradient(180deg, rgba(184, 198, 208, 0.14), rgba(34, 48, 56, 0.2) 24%, rgba(10, 20, 28, 0.5) 100%),
                repeating-linear-gradient(180deg, rgba(255, 255, 255, 0.07) 0 2px, transparent 2px 12px);
        }

        .attendance-stand::after {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 0.45;
        }

        .attendance-stand.north,
        .attendance-stand.south {
            left: 14%;
            right: 14%;
            height: 18%;
        }

        .attendance-stand.north {
            top: 7%;
            border-radius: 48% 48% 16% 16% / 82% 82% 18% 18%;
        }

        .attendance-stand.south {
            bottom: 7%;
            border-radius: 16% 16% 48% 48% / 18% 18% 82% 82%;
        }

        .attendance-stand.north::after,
        .attendance-stand.south::after {
            background:
                repeating-linear-gradient(90deg, transparent 0 12%, rgba(255, 255, 255, 0.08) 12% 12.6%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.07), transparent 30%, transparent 70%, rgba(0, 0, 0, 0.22));
        }

        .attendance-stand.west,
        .attendance-stand.east {
            top: 18%;
            bottom: 18%;
            width: 12%;
        }

        .attendance-stand.west {
            left: 8.5%;
            border-radius: 46% 16% 16% 46% / 22% 16% 16% 22%;
        }

        .attendance-stand.east {
            right: 8.5%;
            border-radius: 16% 46% 46% 16% / 16% 22% 22% 16%;
        }

        .attendance-stand.west::after,
        .attendance-stand.east::after {
            background:
                repeating-linear-gradient(180deg, transparent 0 12%, rgba(255, 255, 255, 0.08) 12% 12.6%),
                linear-gradient(90deg, rgba(255, 255, 255, 0.07), transparent 30%, transparent 70%, rgba(0, 0, 0, 0.22));
        }

        .attendance-seats {
            position: absolute;
            inset: 5.5% 6.5%;
            border-radius: 45% / 38%;
            overflow: hidden;
        }

        .attendance-seats::before,
        .attendance-seats::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .attendance-seats::before {
            background:
                radial-gradient(ellipse at center, transparent 0 37%, rgba(255, 255, 255, 0.045) 37.6%, transparent 38.2%),
                radial-gradient(ellipse at center, transparent 0 54%, rgba(255, 255, 255, 0.038) 54.6%, transparent 55.2%),
                radial-gradient(ellipse at center, rgba(255, 255, 255, 0.03), transparent 74%);
        }

        .attendance-seats::after {
            background:
                linear-gradient(0deg, transparent 49.5%, rgba(255, 255, 255, 0.025) 50%, transparent 50.5%),
                linear-gradient(90deg, transparent 49.5%, rgba(255, 255, 255, 0.025) 50%, transparent 50.5%);
            opacity: 0.45;
        }

        .attendance-seat {
            position: absolute;
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: rgba(185, 197, 209, 0.2);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.015);
            transition: background-color 0.28s ease, transform 0.28s ease, box-shadow 0.28s ease;
        }

        .attendance-seat.is-filled {
            background: #f3f6ce;
            box-shadow: 0 0 8px rgba(243, 246, 206, 0.45);
            transform: scale(1.35);
        }

        .attendance-pitch-shell {
            position: absolute;
            inset: 0;
            min-height: 860px;
            z-index: 1;
        }

        .attendance-pitch {
            position: absolute;
            inset: 17% 18%;
            padding: 2rem;
            border-radius: 28px;
            background:
                radial-gradient(circle at center, rgba(255, 255, 255, 0.05), transparent 38%),
                linear-gradient(90deg, rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                linear-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.02) 0 38px, rgba(0, 0, 0, 0.02) 38px 76px),
                linear-gradient(180deg, #349b52 0%, #2b8748 36%, #22713d 100%);
            background-size: auto, 70px 70px, 70px 70px, auto, cover;
            box-shadow:
                inset 0 0 0 3px rgba(255, 255, 255, 0.18),
                inset 0 0 0 26px rgba(18, 70, 38, 0.16),
                0 24px 70px rgba(0, 0, 0, 0.28);
            overflow: hidden;
        }

        .attendance-pitch-shadow {
            position: absolute;
            inset: 11% 12%;
            border-radius: 18px;
            background: radial-gradient(circle at center, rgba(0, 0, 0, 0.08), transparent 72%);
            pointer-events: none;
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
            top: 7.5%;
            bottom: 7.5%;
            left: 50%;
            width: 0;
            inset: 7.5% auto 7.5% 50%;
            border-width: 0;
            border-left: 3px solid rgba(255, 255, 255, 0.72);
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
            width: 38%;
            height: 86px;
            transform: translateX(-50%);
            border: 3px solid rgba(255, 255, 255, 0.72);
        }

        .attendance-box.top {
            top: 7.5%;
            border-bottom: 0;
        }

        .attendance-box.bottom {
            bottom: 7.5%;
            border-top: 0;
        }

        .attendance-form-shell {
            position: relative;
            z-index: 3;
            width: min(620px, calc(100% - 3rem));
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

        .attendance-progress-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 128px;
            gap: 1rem;
            align-items: center;
        }

        .attendance-progress-main {
            display: grid;
            gap: 0.75rem;
        }

        .attendance-progress-top {
            display: grid;
            gap: 0.45rem;
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

        .attendance-progress-note {
            color: #365142;
            font-weight: 600;
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

        .attendance-progress-visual {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .attendance-mini-stadium {
            position: relative;
            width: 128px;
            aspect-ratio: 1.1 / 1;
            border-radius: 30px;
            background:
                radial-gradient(circle at center, rgba(255, 255, 255, 0.08), transparent 58%),
                linear-gradient(145deg, rgba(10, 25, 17, 0.92), rgba(19, 47, 33, 0.88));
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.12),
                0 16px 30px rgba(8, 20, 13, 0.18);
            overflow: hidden;
        }

        .attendance-mini-stadium::before {
            content: "";
            position: absolute;
            inset: 0.45rem;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            pointer-events: none;
        }

        .attendance-mini-seats {
            position: absolute;
            inset: 0.5rem;
            border-radius: 24px;
        }

        .attendance-mini-seat {
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(196, 214, 202, 0.16);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.03);
            transition: background-color 0.28s ease, transform 0.28s ease, box-shadow 0.28s ease;
        }

        .attendance-mini-seat.is-filled {
            background: #ffe699;
            box-shadow: 0 0 8px rgba(255, 230, 153, 0.6);
            transform: scale(1.18);
        }

        .attendance-mini-pitch {
            position: absolute;
            inset: 26% 20%;
            border-radius: 16px;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                linear-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                linear-gradient(180deg, #3ba95a 0%, #2c8c4b 55%, #22703d 100%);
            background-size: 18px 18px, 18px 18px, cover;
            box-shadow:
                inset 0 0 0 2px rgba(255, 255, 255, 0.16),
                0 8px 16px rgba(0, 0, 0, 0.18);
            overflow: hidden;
        }

        .attendance-mini-pitch::before {
            content: "";
            position: absolute;
            inset: 12%;
            border: 2px solid rgba(255, 255, 255, 0.7);
            border-radius: 12px;
        }

        .attendance-mini-pitch::after {
            content: "";
            position: absolute;
            top: 12%;
            bottom: 12%;
            left: 50%;
            border-left: 2px solid rgba(255, 255, 255, 0.72);
            transform: translateX(-50%);
        }

        .attendance-mini-center-circle,
        .attendance-mini-center-dot {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .attendance-mini-center-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.72);
        }

        .attendance-mini-center-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
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

        .attendance-mobile-step {
            display: contents;
        }

        .attendance-mobile-step-indicator,
        .attendance-mobile-nav {
            display: none;
        }

        .attendance-mobile-step-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: rgba(17, 39, 28, 0.14);
            transition: width 0.2s ease, background-color 0.2s ease;
        }

        .attendance-mobile-step-dot.is-active {
            width: 24px;
            background: var(--pm-primary);
        }

        .attendance-desktop-submit {
            display: block;
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

        @keyframes attendance-mobile-step-enter {
            from {
                opacity: 0;
                transform: translateX(18px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
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
                min-height: 780px;
            }

            .attendance-arena {
                min-height: 780px;
            }

            .attendance-pitch::before {
                inset: 8%;
                border-radius: 22px;
            }

            .attendance-box {
                width: 58%;
            }

            .attendance-pitch {
                inset: 18% 14%;
            }
        }

        @media (max-width: 767.98px) {
            html,
            body,
            #kt_app_root {
                height: 100%;
                overflow: hidden;
            }

            .attendance-guess-page {
                min-height: 100dvh;
                height: 100dvh;
                padding: 0.4rem !important;
                align-items: stretch !important;
            }

            .attendance-stage {
                width: min(100vw - 0.8rem, 1200px);
                height: calc(100dvh - 0.8rem);
                padding: 0.1rem 0;
                display: grid;
                grid-template-rows: auto minmax(0, 1fr);
                gap: 0.45rem;
            }

            .attendance-hero {
                gap: 0.45rem;
                margin-bottom: 0;
                text-align: center;
            }

            .attendance-kicker {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.25rem 0.4rem;
                padding: 0.3rem 0.7rem;
                font-size: 0.62rem;
            }

            .attendance-hero h1 {
                font-size: clamp(1.65rem, 7vw, 2.25rem);
            }

            .attendance-hero p {
                display: none;
            }

            .attendance-board {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.4rem;
            }

            .attendance-chip {
                min-height: 0;
                padding: 0.55rem 0.5rem;
                border-radius: 18px;
            }

            .attendance-chip-label {
                margin-bottom: 0.2rem;
                font-size: 0.55rem;
                line-height: 1.15;
            }

            .attendance-chip-value {
                font-size: clamp(0.8rem, 3.2vw, 1rem);
                line-height: 1.1;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
            }

            .attendance-alert {
                position: fixed;
                top: 0.6rem;
                left: 0.6rem;
                right: 0.6rem;
                z-index: 20;
                margin-bottom: 0 !important;
            }

            .attendance-stadium {
                min-height: 0;
                height: 100%;
                padding: 0.65rem;
                border-radius: 28px;
            }

            .attendance-fields {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.55rem 0.45rem;
            }

            .attendance-fields .form-label {
                margin-bottom: 0.22rem;
                font-size: 0.78rem;
            }

            .attendance-arena {
                height: 100%;
                min-height: 0;
                grid-template-rows: minmax(120px, 0.62fr) minmax(0, 1fr);
                align-items: stretch;
                gap: 0.35rem;
            }

            .attendance-visual {
                position: relative;
                min-height: 0;
                height: 100%;
            }

            .attendance-pitch-shell {
                min-height: 0;
                height: 100%;
            }

            .attendance-seat {
                width: 2px;
                height: 2px;
            }

            .attendance-pitch {
                inset: 19% 10%;
                padding: 0.9rem 0.7rem;
                border-radius: 20px;
            }

            .attendance-form-shell {
                width: min(620px, 100%);
                margin-top: 0;
                padding: 0;
                min-height: 0;
            }

            .attendance-center-circle {
                width: 100px;
                height: 100px;
            }

            .attendance-box {
                height: 64px;
            }

            .attendance-form-panel {
                height: 100%;
                padding: 0.9rem;
                border-radius: 24px;
            }

            .attendance-form-panel form {
                display: flex;
                flex-direction: column;
                height: 100%;
                gap: 0.65rem;
            }

            .attendance-form-panel .form-control {
                min-height: 42px;
                padding: 0.55rem 0.7rem;
                font-size: 0.95rem;
            }

            .attendance-capacity-input {
                font-size: 1rem;
            }

            .attendance-capacity-note {
                margin-top: 0.28rem;
                font-size: 0.72rem;
                line-height: 1.25;
            }

            .attendance-progress {
                margin-top: 0;
                padding: 0.7rem 0.85rem;
                border-radius: 18px;
            }

            .attendance-progress-shell {
                grid-template-columns: minmax(0, 1fr) 96px;
                gap: 0.7rem;
            }

            .attendance-progress-top {
                gap: 0.2rem;
            }

            .attendance-progress-label {
                margin-bottom: 0.15rem;
                font-size: 0.62rem;
            }

            .attendance-progress-value {
                font-size: clamp(1.2rem, 5.5vw, 1.5rem);
            }

            .attendance-progress-note {
                font-size: 0.78rem;
            }

            .attendance-progress-track {
                height: 10px;
            }

            .attendance-mini-stadium {
                width: 96px;
                border-radius: 22px;
            }

            .attendance-mini-stadium::before {
                inset: 0.35rem;
                border-radius: 18px;
            }

            .attendance-mini-seats {
                inset: 0.38rem;
            }

            .attendance-mini-seat {
                width: 3px;
                height: 3px;
            }

            .attendance-mini-pitch {
                inset: 27% 18%;
                border-radius: 12px;
            }

            .attendance-mini-pitch::before,
            .attendance-mini-pitch::after,
            .attendance-mini-center-circle {
                border-width: 1.5px;
            }

            .attendance-mini-center-circle {
                width: 19px;
                height: 19px;
            }

            .attendance-mini-center-dot {
                width: 4px;
                height: 4px;
            }

            .attendance-mobile-step-indicator {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.35rem;
                margin-bottom: 0.1rem;
            }

            .attendance-mobile-step {
                display: none;
                min-height: 0;
                flex: 1 1 auto;
            }

            .attendance-mobile-step.is-active {
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
                animation: attendance-mobile-step-enter 0.25s ease;
            }

            .attendance-mobile-step[data-mobile-step-panel="2"].is-active {
                justify-content: space-between;
            }

            .attendance-consents {
                gap: 0.45rem;
            }

            .attendance-mobile-step .attendance-consents {
                margin-top: 0 !important;
            }

            .attendance-consents .form-check {
                margin: 0;
            }

            .attendance-consents .form-check-label {
                font-size: 0.78rem;
                line-height: 1.2;
            }

            .attendance-mobile-nav {
                display: grid;
                gap: 0.45rem;
                margin-top: auto;
            }

            .attendance-mobile-nav-split {
                grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
            }

            .attendance-mobile-nav .btn {
                min-height: 42px;
                padding-top: 0.7rem;
                padding-bottom: 0.7rem;
                font-size: 0.92rem;
            }

            .attendance-desktop-submit {
                display: none;
            }

            .attendance-stadium[data-mobile-step="2"] .attendance-arena {
                grid-template-rows: minmax(0, 1fr);
            }

            .attendance-stadium[data-mobile-step="2"] .attendance-visual {
                display: none;
            }
        }

        @media (max-width: 767.98px) and (max-height: 780px) {
            .attendance-stage {
                gap: 0.35rem;
            }

            .attendance-kicker {
                padding: 0.24rem 0.65rem;
                font-size: 0.58rem;
            }

            .attendance-chip {
                padding: 0.45rem 0.45rem;
                border-radius: 16px;
            }

            .attendance-chip-label {
                font-size: 0.5rem;
            }

            .attendance-chip-value {
                font-size: 0.76rem;
            }

            .attendance-arena {
                grid-template-rows: minmax(100px, 0.5fr) minmax(0, 1fr);
            }

            .attendance-form-panel {
                padding: 0.75rem;
            }

            .attendance-form-panel .form-control {
                min-height: 38px;
                padding: 0.5rem 0.65rem;
                font-size: 0.9rem;
            }

            .attendance-fields .form-label,
            .attendance-consents .form-check-label,
            .attendance-progress-note {
                font-size: 0.72rem;
            }

            .attendance-progress {
                padding: 0.6rem 0.75rem;
            }

            .attendance-progress-shell {
                grid-template-columns: minmax(0, 1fr) 82px;
                gap: 0.55rem;
            }

            .attendance-mini-stadium {
                width: 82px;
                border-radius: 18px;
            }

            .attendance-mini-pitch {
                inset: 27% 17%;
            }

            .attendance-mobile-nav .btn {
                min-height: 38px;
                padding-top: 0.6rem;
                padding-bottom: 0.6rem;
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
                <div class="attendance-stadium" data-mobile-step="1">
                    <div class="attendance-arena">
                        <div class="attendance-visual">
                            <div class="attendance-architecture" aria-hidden="true">
                                <span class="attendance-stand north"></span>
                                <span class="attendance-stand south"></span>
                                <span class="attendance-stand west"></span>
                                <span class="attendance-stand east"></span>
                            </div>
                            <div class="attendance-seats" id="attendance-seats" aria-hidden="true"></div>

                            <div class="attendance-pitch-shell">
                                <div class="attendance-pitch">
                                    <div class="attendance-center-circle"></div>
                                    <div class="attendance-center-dot"></div>
                                    <div class="attendance-pitch-shadow"></div>
                                    <div class="attendance-box top"></div>
                                    <div class="attendance-box bottom"></div>
                                </div>
                            </div>
                        </div>

                        <div class="attendance-form-shell">
                            <div class="attendance-form-panel">
                                <form
                                    method="POST"
                                    action="{{ route('public.attendance-guess.store') }}"
                                    data-attendance-form
                                    data-max-capacity="{{ $maxCapacity ?? '' }}"
                                    data-initial-guess="{{ $initialGuess ?? '' }}"
                                    data-mobile-step="1"
                                >
                                    @csrf

                                    <div class="attendance-mobile-step-indicator" aria-hidden="true">
                                        <span class="attendance-mobile-step-dot is-active" data-mobile-step-dot="1"></span>
                                        <span class="attendance-mobile-step-dot" data-mobile-step-dot="2"></span>
                                    </div>

                                    <div class="attendance-mobile-step is-active" data-mobile-step-panel="1">
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
                                            <div class="attendance-progress-shell">
                                                <div class="attendance-progress-main">
                                                    <div class="attendance-progress-top">
                                                        <div>
                                                            <span class="attendance-progress-label">Ocupación estimada</span>
                                                            <div class="attendance-progress-value" id="attendance-progress-value">0%</div>
                                                        </div>
                                                        <div class="attendance-progress-note" id="attendance-progress-note">Esperando tu predicción</div>
                                                    </div>
                                                    <div class="attendance-progress-track">
                                                        <div class="attendance-progress-bar" id="attendance-progress-bar"></div>
                                                    </div>
                                                </div>
                                                <div class="attendance-progress-visual" aria-hidden="true">
                                                    <div class="attendance-mini-stadium">
                                                        <div class="attendance-mini-seats" id="attendance-mini-seats"></div>
                                                        <div class="attendance-mini-pitch">
                                                            <div class="attendance-mini-center-circle"></div>
                                                            <div class="attendance-mini-center-dot"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="attendance-mobile-nav">
                                            <button type="button" class="btn btn-primary w-100" data-mobile-step-next>Continuar</button>
                                        </div>
                                    </div>

                                    <div class="attendance-mobile-step" data-mobile-step-panel="2">
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

                                        <div class="attendance-mobile-nav attendance-mobile-nav-split">
                                            <button type="button" class="btn btn-light" data-mobile-step-prev>Volver</button>
                                            <button type="submit" class="btn btn-primary">{{ __('Enviar participación') }}</button>
                                        </div>
                                    </div>

                                    <div class="attendance-desktop-submit mt-5">
                                        <button type="submit" class="btn btn-primary w-100 btn-lg">{{ __('Enviar participación') }}</button>
                                    </div>
                                </form>
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
            const miniSeatsLayer = document.getElementById('attendance-mini-seats');
            const progressValue = document.getElementById('attendance-progress-value');
            const progressBar = document.getElementById('attendance-progress-bar');
            const progressNote = document.getElementById('attendance-progress-note');
            const stageStatus = document.getElementById('attendance-stage-status');
            const stadium = form.closest('.attendance-stadium');
            const mobileSteps = Array.from(form.querySelectorAll('[data-mobile-step-panel]'));
            const mobileStepDots = Array.from(form.querySelectorAll('[data-mobile-step-dot]'));
            const mobileNextButton = form.querySelector('[data-mobile-step-next]');
            const mobilePrevButton = form.querySelector('[data-mobile-step-prev]');
            const mobileMediaQuery = window.matchMedia('(max-width: 767.98px)');

            const maxCapacity = Number(form.dataset.maxCapacity || 0);
            const seats = [];
            const miniSeats = [];
            let currentMobileStep = Number(form.dataset.mobileStep || 1);
            let isMobileSeatLayout = window.innerWidth < 768;

            const formatNumber = (value) => new Intl.NumberFormat('es-ES').format(value);

            const seededValue = (seed, index) => {
                const raw = Math.sin((seed + 1) * (index + 3) * 12.9898) * 43758.5453;
                return raw - Math.floor(raw);
            };

            const createSeatElement = (layer, collection, className, x, y) => {
                if (!layer) {
                    return;
                }

                const seat = document.createElement('span');
                seat.className = className;
                seat.style.left = `${x}%`;
                seat.style.top = `${y}%`;
                layer.appendChild(seat);
                collection.push(seat);
            };

            const createSeat = (x, y) => {
                createSeatElement(seatsLayer, seats, 'attendance-seat', x, y);
            };

            const createMiniSeat = (x, y) => {
                createSeatElement(miniSeatsLayer, miniSeats, 'attendance-mini-seat', x, y);
            };

            const createStandGrid = ({ rows, cols, xStart, xEnd, yStart, yEnd, trimAxis }) => {
                for (let row = 0; row < rows; row += 1) {
                    const rowRatio = rows === 1 ? 0.5 : row / (rows - 1);
                    const curveStrength = Math.abs(rowRatio - 0.5) * 2;

                    for (let col = 0; col < cols; col += 1) {
                        const colRatio = cols === 1 ? 0.5 : col / (cols - 1);
                        const crossCurve = Math.abs(colRatio - 0.5) * 2;
                        let localXStart = xStart;
                        let localXEnd = xEnd;
                        let localYStart = yStart;
                        let localYEnd = yEnd;

                        if (trimAxis === 'x') {
                            const trim = curveStrength * 6;
                            localXStart += trim;
                            localXEnd -= trim;
                        } else {
                            const trim = crossCurve * 4.5;
                            localYStart += trim;
                            localYEnd -= trim;
                        }

                        if (localXEnd <= localXStart || localYEnd <= localYStart) {
                            continue;
                        }

                        const x = localXStart + ((localXEnd - localXStart) * colRatio);
                        const y = localYStart + ((localYEnd - localYStart) * rowRatio);

                        const isAisleColumn = cols > 10 && col > 0 && col < cols - 1 && col % Math.max(6, Math.round(cols / 5)) === 0;
                        const isAisleRow = rows > 10 && row > 0 && row < rows - 1 && row % Math.max(5, Math.round(rows / 4)) === 0;

                        if (isAisleColumn || isAisleRow) {
                            continue;
                        }

                        createSeat(x, y);
                    }
                }
            };

            const buildSeats = () => {
                if (window.innerWidth < 768) {
                    createStandGrid({ rows: 8, cols: 28, xStart: 20, xEnd: 80, yStart: 8.5, yEnd: 18, trimAxis: 'x' });
                    createStandGrid({ rows: 8, cols: 28, xStart: 20, xEnd: 80, yStart: 82, yEnd: 91.5, trimAxis: 'x' });
                    createStandGrid({ rows: 20, cols: 8, xStart: 10, xEnd: 18.5, yStart: 21, yEnd: 79, trimAxis: 'y' });
                    createStandGrid({ rows: 20, cols: 8, xStart: 81.5, xEnd: 90, yStart: 21, yEnd: 79, trimAxis: 'y' });

                    return;
                }

                createStandGrid({ rows: 10, cols: 42, xStart: 18, xEnd: 82, yStart: 8.5, yEnd: 19.5, trimAxis: 'x' });
                createStandGrid({ rows: 10, cols: 42, xStart: 18, xEnd: 82, yStart: 80.5, yEnd: 91.5, trimAxis: 'x' });
                createStandGrid({ rows: 24, cols: 10, xStart: 8.5, xEnd: 18.5, yStart: 20, yEnd: 80, trimAxis: 'y' });
                createStandGrid({ rows: 24, cols: 10, xStart: 81.5, xEnd: 91.5, yStart: 20, yEnd: 80, trimAxis: 'y' });
            };

            const buildMiniSeats = () => {
                if (!miniSeatsLayer) {
                    return;
                }

                const createMiniStandGrid = ({ rows, cols, xStart, xEnd, yStart, yEnd, trimAxis }) => {
                    for (let row = 0; row < rows; row += 1) {
                        const rowRatio = rows === 1 ? 0.5 : row / (rows - 1);
                        const curveStrength = Math.abs(rowRatio - 0.5) * 2;

                        for (let col = 0; col < cols; col += 1) {
                            const colRatio = cols === 1 ? 0.5 : col / (cols - 1);
                            const crossCurve = Math.abs(colRatio - 0.5) * 2;
                            let localXStart = xStart;
                            let localXEnd = xEnd;
                            let localYStart = yStart;
                            let localYEnd = yEnd;

                            if (trimAxis === 'x') {
                                const trim = curveStrength * 4.5;
                                localXStart += trim;
                                localXEnd -= trim;
                            } else {
                                const trim = crossCurve * 3.2;
                                localYStart += trim;
                                localYEnd -= trim;
                            }

                            if (localXEnd <= localXStart || localYEnd <= localYStart) {
                                continue;
                            }

                            const x = localXStart + ((localXEnd - localXStart) * colRatio);
                            const y = localYStart + ((localYEnd - localYStart) * rowRatio);
                            createMiniSeat(x, y);
                        }
                    }
                };

                createMiniStandGrid({ rows: 4, cols: 16, xStart: 16, xEnd: 84, yStart: 9, yEnd: 22, trimAxis: 'x' });
                createMiniStandGrid({ rows: 4, cols: 16, xStart: 16, xEnd: 84, yStart: 78, yEnd: 91, trimAxis: 'x' });
                createMiniStandGrid({ rows: 11, cols: 4, xStart: 8, xEnd: 18, yStart: 24, yEnd: 76, trimAxis: 'y' });
                createMiniStandGrid({ rows: 11, cols: 4, xStart: 82, xEnd: 92, yStart: 24, yEnd: 76, trimAxis: 'y' });
            };

            const orderedSeatIndexes = (guess, seatCount) => {
                return Array.from({ length: seatCount }, (_, index) => ({
                        index,
                        value: seededValue((guess || 1) + seatCount, index),
                    }))
                    .sort((a, b) => a.value - b.value)
                    .map((entry) => entry.index);
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
                const paintSeatCollection = (collection) => {
                    const filledSeatCount = Math.round(collection.length * ratio);
                    const orderedIndexes = orderedSeatIndexes(guess, collection.length);
                    const filledIndexes = new Set(orderedIndexes.slice(0, filledSeatCount));

                    collection.forEach((seat, index) => {
                        seat.classList.toggle('is-filled', filledIndexes.has(index));
                    });

                    return collection.length > 0 ? filledSeatCount / collection.length : ratio;
                };

                const visualRatio = paintSeatCollection(seats);
                paintSeatCollection(miniSeats);

                progressValue.textContent = `${Math.round(visualRatio * 100)}%`;
                progressBar.style.width = `${visualRatio * 100}%`;
                progressNote.textContent = maxCapacity > 0
                    ? `${formatNumber(Math.min(guess, maxCapacity))} / ${formatNumber(maxCapacity)} espectadores`
                    : 'Configura un aforo máximo para calibrar la simulación';

                if (stageStatus) {
                    stageStatus.textContent = occupancyLabel(visualRatio);
                }
            };

            const clampGuess = () => {
                if (maxCapacity <= 0) {
                    return Number(input.value || 0);
                }

                const nextGuess = Math.max(0, Math.min(Number(input.value || 0), maxCapacity));

                if (String(nextGuess) !== input.value) {
                    input.value = String(nextGuess);
                }

                return nextGuess;
            };

            const setMobileStep = (step) => {
                currentMobileStep = step;
                form.dataset.mobileStep = String(step);

                if (stadium) {
                    stadium.dataset.mobileStep = String(step);
                }

                mobileSteps.forEach((panel) => {
                    panel.classList.toggle('is-active', Number(panel.dataset.mobileStepPanel) === step);
                });

                mobileStepDots.forEach((dot) => {
                    dot.classList.toggle('is-active', Number(dot.dataset.mobileStepDot) === step);
                });
            };

            const validateStep = (step) => {
                const stepPanel = form.querySelector(`[data-mobile-step-panel="${step}"]`);

                if (!stepPanel) {
                    return true;
                }

                const fields = Array.from(stepPanel.querySelectorAll('input, select, textarea'))
                    .filter((field) => !field.disabled && field.type !== 'hidden');

                for (const field of fields) {
                    if (!field.checkValidity()) {
                        field.reportValidity();
                        return false;
                    }
                }

                return true;
            };

            const syncSeatLayout = () => {
                const nextMobileSeatLayout = window.innerWidth < 768;

                if (nextMobileSeatLayout === isMobileSeatLayout) {
                    return;
                }

                isMobileSeatLayout = nextMobileSeatLayout;
                seatsLayer.innerHTML = '';
                seats.length = 0;
                buildSeats();
                paintSeats(clampGuess());
            };

            buildSeats();
            buildMiniSeats();
            setMobileStep(currentMobileStep);
            paintSeats(clampGuess());

            input.addEventListener('input', () => {
                const guess = clampGuess();
                paintSeats(guess);
            });

            input.addEventListener('blur', () => {
                const guess = clampGuess();
                paintSeats(guess);
            });

            mobileNextButton?.addEventListener('click', () => {
                if (!mobileMediaQuery.matches || validateStep(1)) {
                    setMobileStep(2);
                }
            });

            mobilePrevButton?.addEventListener('click', () => {
                setMobileStep(1);
            });

            form.addEventListener('submit', (event) => {
                if (!mobileMediaQuery.matches) {
                    return;
                }

                if (currentMobileStep === 1) {
                    event.preventDefault();

                    if (validateStep(1)) {
                        setMobileStep(2);
                    }
                }
            });

            window.addEventListener('resize', syncSeatLayout);
        })();
    </script>
@endpush
