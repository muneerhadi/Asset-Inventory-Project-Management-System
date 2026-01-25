<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // #region agent log
        $logPath = 'c:\Users\hadim\project-asset-inventory-system\project-asset-inventory-system\Hadi project\.cursor\debug.log';
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run_check_version',
            'hypothesisId' => 'A',
            'location' => 'LoginRequest.php:43',
            'message' => 'authenticate method entered - Version: INLINED_LOGIC_V2',
            'timestamp' => time() * 1000,
            'data' => ['email' => $this->input('email')]
        ];
        @file_put_contents($logPath, json_encode($logData) . PHP_EOL, FILE_APPEND);
        // #endregion

        $key = $this->throttleKey();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            event(new Lockout($this));
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => (int) ceil($seconds / 60),
                ]),
            ]);
        }

        $email = $this->input('email');
        $stored = DB::table('users')->where('email', $email)->value('password');

        // #region agent log
        $log = ['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C', 'location' => 'LoginRequest.php:60', 'message' => 'Fetched stored password', 'data' => ['email' => $email, 'has_stored' => !is_null($stored)], 'timestamp' => time() * 1000];
        @file_put_contents('c:\Users\hadim\project-asset-inventory-system\project-asset-inventory-system\Hadi project\.cursor\debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        // #endregion

        if ($stored !== null && ! $this->isValidBcryptHash($stored)) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages([
                'email' => 'Your account password needs to be reset by an administrator. Please contact support.',
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($key);
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($key);
    }

    /**
     * Check if the stored value is a valid Bcrypt hash.
     * Prevents "This password does not use the Bcrypt algorithm" 500 when
     * passwords were imported as plain text, MD5, or from another system.
     */
    private function isValidBcryptHash(?string $hash): bool
    {
        // #region agent log
        $log = ['sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C', 'location' => 'LoginRequest.php:82', 'message' => 'Checking hash validity', 'data' => ['hash_prefix' => substr($hash ?? '', 0, 4)], 'timestamp' => time() * 1000];
        @file_put_contents('c:\Users\hadim\project-asset-inventory-system\project-asset-inventory-system\Hadi project\.cursor\debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        // #endregion

        if (! is_string($hash) || strlen($hash) !== 60) {
            return false;
        }

        return str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$');
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
