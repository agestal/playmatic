@php
    $pageTitle = trim($__env->yieldContent('page_title')) ?: trim($__env->yieldContent('title')) ?: config('app.name');
@endphp

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div class="app-toolbar py-3 py-lg-6" id="kt_app_toolbar">
            <div class="app-container container-xxl d-flex flex-stack" id="kt_app_toolbar_container">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        {{ $pageTitle }}
                    </h1>

                    @hasSection('page_description')
                        <p class="text-muted fs-7 fw-semibold mt-1 mb-0">@yield('page_description')</p>
                    @endif
                </div>

                @hasSection('page_actions')
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        @yield('page_actions')
                    </div>
                @endif
            </div>
        </div>

        <div class="app-content flex-column-fluid" id="kt_app_content">
            <div class="app-container container-xxl" id="kt_app_content_container">
                @yield('content')
            </div>
        </div>
    </div>

    @include('layouts.metronic.partials.footer')
</div>
