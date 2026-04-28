<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function loginAsAdmin(): RedirectResponse
    {
        if (! config('getfy.demo_mode')) {
            abort(404);
        }

        $user = User::query()
            ->where('role', User::ROLE_PLATFORM_ADMIN)
            ->whereNull('tenant_id')
            ->orderBy('id')
            ->first();

        if (! $user instanceof User) {
            return redirect()->route('login')->with('error', 'Modo demo: nenhuma conta de administrador da plataforma encontrada.');
        }

        Auth::login($user, remember: false);
        request()->session()->regenerate();

        return redirect()->intended(route('plataforma.dashboard'));
    }

    public function loginAsInfoprodutor(): RedirectResponse
    {
        if (! config('getfy.demo_mode')) {
            abort(404);
        }

        $user = User::query()
            ->where('role', User::ROLE_INFOPRODUTOR)
            ->orderBy('id')
            ->first();

        if (! $user instanceof User) {
            return redirect()->route('login')->with('error', 'Modo demo: nenhuma conta de infoprodutor encontrada.');
        }

        Auth::login($user, remember: false);
        request()->session()->regenerate();
        request()->session()->put('panel_context', 'seller');

        return redirect()->intended('/dashboard');
    }
}
