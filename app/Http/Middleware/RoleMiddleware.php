<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (! in_array($user->role, $roles, true)) {
            $targetRoute = match ($user->role) {
                'admin' => 'admin.dashboard',
                'teacher' => 'teacher.dashboard',
                default => 'parent.dashboard',
            };

            if ($request->route()?->getName() !== $targetRoute) {
                return redirect()->route($targetRoute);
            }

            abort(403);
        }

        return $next($request);
    }
}
