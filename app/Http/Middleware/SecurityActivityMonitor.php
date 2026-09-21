<?php

namespace App\Http\Middleware;

use App\Services\SecurityAlertService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SecurityActivityMonitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $status = $response->getStatusCode();

        if (in_array($status, [403, 429], true)) {
            $this->trackDeniedResponse($request, $status);
        }

        if ($status >= 500) {
            $this->trackServerError($request, $status);
        }

        return $response;
    }

    private function trackDeniedResponse(Request $request, int $status): void
    {
        $type = $status === 429 ? 'rate_limit_burst' : 'forbidden_burst';
        $limit = $status === 429
            ? (int) config('security.alerts.rate_limit_threshold', 5)
            : (int) config('security.alerts.forbidden_threshold', 10);
        $key = 'security-monitor:'.$type.':'.$request->ip();
        $count = RateLimiter::increment($key, 600);

        if ($count >= $limit) {
            app(SecurityAlertService::class)->raise($type, 'warning', [
                'ip_address' => $request->ip(),
                'user_id' => $request->user()?->id,
                'count_10_minutes' => $count,
                'path' => $request->path(),
                'status' => $status,
            ]);
        }
    }

    private function trackServerError(Request $request, int $status): void
    {
        $key = 'security-monitor:server-error:'.now()->format('YmdHi');
        $count = RateLimiter::increment($key, 120);
        if ($count >= (int) config('security.alerts.error_threshold', 10)) {
            app(SecurityAlertService::class)->raise('error_rate', 'critical', [
                'count_2_minutes' => $count,
                'path' => $request->path(),
                'status' => $status,
            ]);
        }
    }
}
