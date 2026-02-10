{{-- Metronic v9 demo1 branded sign-in (blindado: NO depende de utilidades tailwind) --}}

<style>
  .branded-bg { background-image: url('{{ asset('assets/media/images/2600x1600/fondo_playmatic.png') }}'); }
  .dark .branded-bg { background-image: url('{{ asset('assets/media/images/2600x1600/fondo_playmatic.png') }}'); }

  /* Layout blindado */
  .auth-grid{
    display: grid;
    min-height: 100vh;
    width: 100%;
  }
  @media (min-width: 1024px){
    .auth-grid{ grid-template-columns: 1fr 1fr; }
  }

  .auth-left{
    display:flex;
    justify-content:center;
    align-items:center;
    padding: 2rem;
  }
  @media (min-width: 1024px){
    .auth-left{ padding: 2.5rem; }
  }

  .auth-card-wrap{
    width: 100%;
    max-width: 420px; /* aquí controlas el ancho real del formulario */
  }

    .auth-right{
        position: relative;
        overflow: hidden;              /* clave: recorta bg y overlay con el borde */
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        padding: 1.25rem;              /* padding real (sustituye al p-5) */
    }
  @media (min-width: 1024px){
    .auth-right{
      border-radius: 0.75rem;
      border: 1px solid var(--border, #e5e7eb);
      margin: 1.25rem;
    }
  }
</style>

<div class="auth-grid">
  {{-- IZQUIERDA: FORM --}}
  <div class="auth-left">
    <div class="auth-card-wrap">
      <div class="kt-card w-full">
        <div class="kt-card-content p-6">
          <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf

            <div class="text-center mb-2.5">
              <h3 class="text-lg font-medium text-mono leading-none mb-2.5">{{ __('Sign in') }}</h3>

              <div class="flex items-center justify-center font-medium">
                <span class="text-sm text-secondary-foreground me-1.5">{{ __('Need an account?') }}</span>
                <a class="text-sm link" href="#">{{ __('Sign up') }}</a>
              </div>
            </div>

            @if ($errors->any())
              <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
                <ul class="list-disc pl-5">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="grid grid-cols-2 gap-2.5">
              <a class="kt-btn kt-btn-outline justify-center" href="#">
                <img alt="" class="size-3.5 shrink-0" src="{{ asset('assets/media/brand-logos/google.svg') }}"/>
                {{ __('Use Google') }}
              </a>
              <a class="kt-btn kt-btn-outline justify-center" href="#">
                <img alt="" class="size-3.5 shrink-0 dark:hidden" src="{{ asset('assets/media/brand-logos/apple-black.svg') }}"/>
                <img alt="" class="size-3.5 shrink-0 light:hidden" src="{{ asset('assets/media/brand-logos/apple-white.svg') }}"/>
                {{ __('Use Apple') }}
              </a>
            </div>

            <div class="flex items-center gap-2">
              <span class="border-t border-border w-full"></span>
              <span class="text-xs text-muted-foreground font-medium uppercase">{{ __('Or') }}</span>
              <span class="border-t border-border w-full"></span>
            </div>

            <div class="flex flex-col gap-1">
              <label class="kt-form-label font-normal text-mono" for="email">{{ __('Email') }}</label>
              <input
                id="email"
                class="kt-input"
                placeholder="{{ __('email@example.com') }}"
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                autofocus
              />
            </div>

            <div class="flex flex-col gap-1">
              <div class="flex items-center justify-between gap-1">
                <label class="kt-form-label font-normal text-mono" for="password">{{ __('Password') }}</label>
                <a class="text-sm kt-link shrink-0" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
              </div>

              <div class="kt-input" data-kt-toggle-password="true">
                <input
                  id="password"
                  name="password"
                  placeholder="{{ __('Enter password') }}"
                  type="password"
                  autocomplete="current-password"
                  required
                />
                <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5"
                        data-kt-toggle-password-trigger="true"
                        type="button">
                  <span class="kt-toggle-password-active:hidden">
                    <i class="ki-filled ki-eye text-muted-foreground"></i>
                  </span>
                  <span class="hidden kt-toggle-password-active:block">
                    <i class="ki-filled ki-eye-slash text-muted-foreground"></i>
                  </span>
                </button>
              </div>
            </div>

            <label class="kt-label">
              <input class="kt-checkbox kt-checkbox-sm" name="remember" type="checkbox" value="1"/>
              <span class="kt-checkbox-label">{{ __('Remember me') }}</span>
            </label>

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center w-full">
              {{ __('Sign in') }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- DERECHA: BRAND --}}
  <div class="auth-right branded-bg">
    <div class="flex flex-col p-8 lg:p-16 gap-4">
    </div>
  </div>
</div>
