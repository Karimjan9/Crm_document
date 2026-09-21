<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BotApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $configured = (string) config('bot.api_key');
        $provided = (string) $request->bearerToken();

        abort_unless($configured !== '' && $provided !== '' && hash_equals($configured, $provided), 401);

        return $next($request);
    }
}
