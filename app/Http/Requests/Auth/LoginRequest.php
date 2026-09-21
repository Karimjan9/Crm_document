<?php

namespace App\Http\Requests\Auth;

use App\Support\LoginRateLimiter;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('login', 'password'), $this->boolean('remember'))) {
            app(LoginRateLimiter::class)->recordFailure($this);

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        app(LoginRateLimiter::class)->clearAccount($this);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        app(LoginRateLimiter::class)->ensureNotLocked($this);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return app(LoginRateLimiter::class)->accountKey($this);
    }
}
