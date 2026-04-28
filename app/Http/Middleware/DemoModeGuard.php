<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoModeGuard
{
    /** @var list<string> */
    private const SENSITIVE_GET_ROUTE_NAMES = [
        'plataforma.gateways.show',
        'plataforma.financeiro.gateways.show',
    ];

    /** @var list<string> */
    private const PASSWORD_ROUTE_NAMES = [
        'password.email',
        'password.update',
        'profile.update-password',
        'plataforma.account.password',
        'member-area-app.conta.password',
        'member-area-app.conta.password.host',
    ];

    /** @var list<string> */
    private const MUTATION_ALLOW_ROUTE_NAMES = [
        'logout',
        'plataforma.logout',
        'panel.switch',
        'panel.language.switch',
        'demo.login.admin',
        'demo.login.infoprodutor',
        'panel.notifications.mark-read',
        'panel.notifications.mark-read-batch',
        'panel.notifications.mark-all-read',
        'panel.notifications.clear-all',
        'member-area-app.notifications.mark-read',
        'member-area-app.notifications.mark-all-read',
        'member-area-app.notifications.clear-all',
        'member-area-app.notifications.mark-read.host',
        'member-area-app.notifications.mark-all-read.host',
        'member-area-app.notifications.clear-all.host',
        'member-area-app.lesson.complete',
        'member-area-app.lesson.complete.host',
        'member-area-app.lesson.comments.store',
        'member-area-app.lesson.comments.store.host',
        'member-area-app.comunidade.posts.like',
        'member-area-app.comunidade.posts.unlike',
        'member-area-app.comunidade.posts.like.host',
        'member-area-app.comunidade.posts.unlike.host',
        'panel.pwa.push-subscribe',
        'member-area-app.push.subscribe',
        'member-area-app.push.subscribe.host',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('getfy.demo_mode')) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route?->getName();

        // Webhooks de gateways externos e cron por URL não devem ser bloqueados
        if ($name === 'cron.url' || ($name !== null && str_starts_with((string) $name, 'webhooks.'))) {
            return $next($request);
        }

        if ($request->isMethod('GET') && $name !== null && in_array($name, self::SENSITIVE_GET_ROUTE_NAMES, true)) {
            return $this->deny($request, 'Em modo demo as credenciais ficam ocultas.');
        }

        if ($name !== null && in_array($name, self::PASSWORD_ROUTE_NAMES, true)) {
            return $this->deny($request, 'Modo demo: alteração de senha está desabilitada.');
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            if ($name !== null && in_array($name, self::MUTATION_ALLOW_ROUTE_NAMES, true)) {
                return $next($request);
            }

            return $this->deny($request, 'Modo demo: alterações estão desabilitadas.');
        }

        return $next($request);
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->header('X-Inertia')) {
            return back()->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return back()->with('error', $message);
    }
}
