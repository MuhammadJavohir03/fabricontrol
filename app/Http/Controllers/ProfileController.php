<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profile.edit', [
            'title' => 'Profil',
            'user'  => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'toliq_ism' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', 'confirmed', Password::defaults()],
        ], [
            'toliq_ism.required' => 'To‘liq ism kiritish shart.',
            'email.required'     => 'Email kiritish shart.',
            'email.email'        => 'Email noto‘g‘ri formatda.',
            'email.unique'       => 'Bu email allaqachon band.',
            'password.confirmed' => 'Parol tasdiqlash mos kelmadi.',
        ]);

        $user->toliq_ism = $validated['toliq_ism'];
        $user->email     = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil muvaffaqiyatli yangilandi.');
    }
}