<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Support\Setup\InitialInstallationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InstallationController extends Controller
{
    public function show(Request $request, InitialInstallationService $installationService): View|RedirectResponse
    {
        if (! $installationService->hasRequiredTables()) {
            abort(500, __('Run database migrations before opening the installer.'));
        }

        if ($installationService->isInstalled()) {
            return $this->redirectInstalled($request);
        }

        return view('setup.install', [
            'defaultDomain' => $installationService->normalizeDomain($request->getHost()),
        ]);
    }

    public function store(Request $request, InitialInstallationService $installationService): RedirectResponse
    {
        if (! $installationService->hasRequiredTables()) {
            abort(500, __('Run database migrations before opening the installer.'));
        }

        if ($installationService->isInstalled()) {
            return $this->redirectInstalled($request);
        }

        $validated = $request->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
            'tenant_name' => ['required', 'string', 'max:255'],
            'primary_domain' => ['required', 'string', 'max:255'],
        ]);

        $normalizedDomain = $installationService->normalizeDomain($validated['primary_domain']);

        if ($normalizedDomain === '' || ! $installationService->isValidDomain($normalizedDomain)) {
            return back()
                ->withErrors(['primary_domain' => __('The provided domain is not valid.')])
                ->withInput();
        }

        if ($installationService->emailExists($validated['admin_email'])) {
            return back()
                ->withErrors(['admin_email' => __('The provided email is already in use.')])
                ->withInput();
        }

        if ($installationService->domainExists($normalizedDomain)) {
            return back()
                ->withErrors(['primary_domain' => __('That domain is already assigned to another tenant.')])
                ->withInput();
        }

        $result = $installationService->install([
            'admin_name' => $validated['admin_name'],
            'admin_email' => $validated['admin_email'],
            'admin_password' => $validated['admin_password'],
            'tenant_name' => $validated['tenant_name'],
            'primary_domain' => $normalizedDomain,
        ]);

        Auth::login($result['user']);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard', [
                'locale' => $request->route('locale') ?? app()->getLocale(),
            ])
            ->with('status', __('Installation completed successfully.'));
    }

    protected function redirectInstalled(Request $request): RedirectResponse
    {
        $locale = $request->route('locale') ?? app()->getLocale();
        $target = $request->user() ? 'dashboard' : 'login';

        return redirect()->route($target, [
            'locale' => $locale,
        ]);
    }
}
