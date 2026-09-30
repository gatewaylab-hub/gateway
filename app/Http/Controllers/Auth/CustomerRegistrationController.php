<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\BrazilianDocuments;
use App\Support\LoginTurnstileSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerRegistrationController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->intended($request->user()->canAccessSellerPanel() ? '/dashboard' : '/painel-cliente');
        }

        return Inertia::render('Marketplace/Auth/Register', [
            'login_turnstile' => LoginTurnstileSettings::publicConfig(),
            'intended' => $request->session()->get('url.intended'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:24'],
            'document' => ['required', 'string', 'max:14'],
            'birth_date' => ['required', 'date', 'before:'.now()->subYears(18)->format('Y-m-d')],
            'address_zip' => ['required', 'string', 'max:9'],
            'address_street' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:120'],
            'address_neighborhood' => ['required', 'string', 'max:120'],
            'address_city' => ['required', 'string', 'max:120'],
            'address_state' => ['required', 'string', 'size:2'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'accept_terms' => ['accepted'],
        ], [
            'birth_date.before' => 'Você precisa ter pelo menos 18 anos.',
            'accept_terms.accepted' => 'Aceite os termos para continuar.',
            'address_state.size' => 'Informe a UF com 2 letras.',
        ]);

        $cpf = BrazilianDocuments::digits($validated['document']);
        if (! BrazilianDocuments::isValidCpf($cpf)) {
            throw ValidationException::withMessages(['document' => 'CPF inválido.']);
        }

        if (User::query()->where('document', $cpf)->exists()) {
            throw ValidationException::withMessages(['document' => 'Este CPF já está cadastrado.']);
        }

        $phone = preg_replace('/\D/', '', $validated['phone']) ?? '';
        if (strlen($phone) < 10 || strlen($phone) > 13) {
            throw ValidationException::withMessages(['phone' => 'Telefone inválido.']);
        }

        $zip = preg_replace('/\D/', '', $validated['address_zip']) ?? '';
        if (strlen($zip) !== 8) {
            throw ValidationException::withMessages(['address_zip' => 'CEP inválido.']);
        }

        $user = User::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => $phone,
            'person_type' => 'pf',
            'document' => $cpf,
            'birth_date' => $validated['birth_date'],
            'address_zip' => $zip,
            'address_street' => trim($validated['address_street']),
            'address_number' => trim($validated['address_number']),
            'address_complement' => trim((string) ($validated['address_complement'] ?? '')) ?: null,
            'address_neighborhood' => trim($validated['address_neighborhood']),
            'address_city' => trim($validated['address_city']),
            'address_state' => strtoupper(trim($validated['address_state'])),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_CLIENTE,
            'tenant_id' => null,
            'email_verified_at' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('panel_context', 'customer');

        $intended = $request->session()->pull('url.intended');
        if (is_string($intended) && str_starts_with($intended, '/c/')) {
            return redirect()->to($intended);
        }

        return redirect()->intended('/painel-cliente')->with('success', 'Conta criada com sucesso.');
    }
}
