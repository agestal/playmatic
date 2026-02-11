@php
    $pageTitle = trim($__env->yieldContent('page_title'));
@endphp

<main class="grow pt-5 pb-6" id="content" role="main">
    <div class="kt-container-fluid flex flex-col gap-5">
        @hasSection('page_header')
            @yield('page_header')
        @elseif ($pageTitle !== '')
            <div class="pm-page-head">
                <div class="min-w-0">
                    <h2 class="pm-page-title">{{ $pageTitle }}</h2>

                    @hasSection('page_description')
                        <p class="pm-page-description">@yield('page_description')</p>
                    @endif
                </div>

                @hasSection('page_actions')
                    <div class="pm-page-actions">
                        @yield('page_actions')
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </div>
</main>
