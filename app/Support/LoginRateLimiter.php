<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Applies consistent credential-stuffing protection to browser and API logins. */
class LoginRateLimiter
{
    public function ensureNotLocked(Request $request): void
    {
        $accountKey = $this->accountKey($request);
        $ipKey = $this->ipKey($request);

        if (RateLimiter::tooManyAttempts($accountKey, $this->maxAttempts())) {
            $this->throwLockout(RateLimiter::availableIn($accountKey));
        }

        if (RateLimiter::tooManyAttempts($ipKey, $this->ipMaxAttempts())) {
            $this->throwLockout(RateLimiter::availableIn($ipKey));
        }
    }

    public function recordFailure(Request $request): void
    {
        RateLimiter::hit($this->accountKey($request), $this->decaySeconds());
        $ipKey = $this->ipKey($request);
        RateLimiter::hit($ipKey, $this->decaySeconds());

        $attempts = RateLimiter::attempts($ipKey);
        if ($attempts >= (int) config('security.alerts.login_alert_threshold', 10)) {
            app(SecurityAlertService::class)->raise('login_burst', 'warning', [
                'ip_address' => $request->ip(),
                'count_15_minutes' => $attempts,
            ]);
        }
    }

    public function clearAccount(Request $request): void
    {
        // A successful login must not reset the IP-wide anti-stuffing counter.
        RateLimiter::clear($this->accountKey($request));
    }

    public function accountKey(Request $request): string
    {
        return 'login:account:'.$this->fingerprint($this->login($request).'|'.$request->ip());
    }

    private function ipKey(Request $request): string
    {
        return 'login:ip:'.$this->fingerprint((string) $request->ip());
    }

    private function login(Request $request): string
    {
        return Str::lower(trim((string) $request->input('login')));
    }

    private function fingerprint(string $value): string
    {
        return hash('sha256', $value);
    }

    private function maxAttempts(): int
    {
        return max(1, (int) config('security.login.max_attempts', 5));
    }

    private function ipMaxAttempts(): int
    {
        return max($this->maxAttempts(), (int) config('security.login.ip_max_attempts', 25));
    }

    private function decaySeconds(): int
    {
        return max(60, (int) config('security.login.lockout_seconds', 900));
    }

    private function throwLockout(int $seconds): never
    {
        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => max(1, $seconds),
                'minutes' => max(1, (int) ceil($seconds / 60)),
            ]),
        ]);
    }
}
