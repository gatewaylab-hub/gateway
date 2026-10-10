<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->canAccessCustomerPanel()) {
            return redirect()->to($user->defaultAuthenticatedHomeUrl());
        }

        return $next($request);
    }
}
