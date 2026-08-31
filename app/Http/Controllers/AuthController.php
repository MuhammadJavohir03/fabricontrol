<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login sahifasini ko'rsatish.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Login formasidan kelgan ma'lumotni tekshirib, tizimga kiritish.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email kiritish shart.',
            'email.email'       => 'Email noto\'g\'ri formatda.',
            'password.required' => 'Parol kiritish shart.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'Email yoki parol noto\'g\'ri.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Tizimdan chiqish.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}