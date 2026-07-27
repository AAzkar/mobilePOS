<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', [
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'pin' => ['required', 'digits_between:4,6'],
        ]);

        $throttleKey = 'login:'.$data['user_id'].'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'pin' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        $user = User::query()->where('is_active', true)->find($data['user_id']);

        if (! $user || ! Hash::check($data['pin'], $user->pin_hash)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors(['pin' => 'Incorrect PIN.'])->withInput(['user_id' => $data['user_id']]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('scan'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
