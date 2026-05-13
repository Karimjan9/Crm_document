<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role1, $role2 = null, $role3 = null)
    {
        $user = $request->user();

        if (!$user) {
            abort(404);
        }

        $roles = collect([$role1, $role2, $role3])
            ->filter()
            ->flatMap(fn (string $role) => preg_split('/[|,]/', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->values()
            ->all();

        if (!$user->hasAnyRole($roles)) {
            abort(404);
        }

        return $next($request);
    }
}
