<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::with('users')->orderByDesc('created_at')->get();

        return view('admin.companies.index', compact('companies'));
    }

    public function select(Company $company)
    {
        session(['active_company_id' => $company->id]);

        return redirect()->route('admin.dashboard')
            ->with('success', "«{$company->nomi}» companiyasi tanlandi.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomi'       => 'required|string|max:255',
            'tel_nomer'  => 'nullable|string|max:32',
            'muddat_kun' => 'nullable|integer|min:1',
            'izoh'       => 'nullable|string',
        ]);

        $company = Company::create([
            'nomi'           => $validated['nomi'],
            'tel_nomer'      => $validated['tel_nomer'] ?? null,
            'kirish_muddati' => isset($validated['muddat_kun'])
                ? now()->addDays((int) $validated['muddat_kun'])->toDateString()
                : null,
            'faol' => true,
            'izoh' => $validated['izoh'] ?? null,
        ]);

        return back()->with('success', "«{$company->nomi}» qo'shildi.");
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'nomi'      => 'required|string|max:255',
            'tel_nomer' => 'nullable|string|max:32',
            'izoh'      => 'nullable|string',
        ]);

        $company->update($validated);

        return back()->with('success', 'Companiya yangilandi.');
    }

    /** Muddatni uzaytirish — to'lov qabul qilingandan keyin super_admin qo'lda bosadi */
    public function extend(Request $request, Company $company)
    {
        $validated = $request->validate([
            'kun' => 'required|integer|min:1',
        ]);

        $boshlanish = ($company->kirish_muddati && $company->kirish_muddati->isFuture())
            ? $company->kirish_muddati
            : now();

        $company->update([
            'kirish_muddati' => $boshlanish->copy()->addDays((int) $validated['kun'])->toDateString(),
            'faol'           => true,
        ]);

        return back()->with('success', "Muddat {$validated['kun']} kunga uzaytirildi.");
    }

    public function toggleBlock(Company $company)
    {
        $company->update(['faol' => ! $company->faol]);

        return back()->with('success', $company->faol ? 'Companiya faollashtirildi.' : 'Companiya bloklandi.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        if ((int) session('active_company_id') === $company->id) {
            session()->forget('active_company_id');
        }

        return back()->with('success', "Companiya o'chirildi.");
    }

    /**
     * Companiyaga login (email + parol) biriktirish.
     * Shu email bilan kirgan foydalanuvchi avtomatik shu companiyaga bog'lanadi
     * (company_id orqali) va faqat o'z companiyasining ma'lumotlarini ko'radi.
     */
    public function storeUser(Request $request, Company $company)
    {
        $validated = $request->validate([
            'toliq_ism' => 'required|string|max:255',
            'tel_nomer' => 'required|string|max:32|unique:users,tel_nomer',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role'      => 'required|in:admin,chevar,client',
        ], [
            'tel_nomer.unique' => 'Bu telefon raqami allaqachon ro\'yxatdan o\'tgan.',
            'email.unique'     => 'Bu email allaqachon ro\'yxatdan o\'tgan.',
        ]);

        User::create([
            'toliq_ism'  => $validated['toliq_ism'],
            'tel_nomer'  => $validated['tel_nomer'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => $validated['role'],
            'company_id' => $company->id,
        ]);

        return back()->with('success', "«{$company->nomi}» uchun login yaratildi: {$validated['email']}");
    }

    /** Companiyaga biriktirilgan loginni olib tashlash (foydalanuvchini o'chirish emas — companiyadan ajratish) */
    public function detachUser(Company $company, User $user)
    {
        if ($user->company_id !== $company->id) {
            abort(404);
        }

        $user->delete();

        return back()->with('success', 'Login o\'chirildi.');
    }

    /** Muddati tugagan/bloklangan foydalanuvchilarga ko'rsatiladigan sahifa */
    public function blocked()
    {
        $company = auth()->user()?->company;

        return view('admin.companies.blocked', compact('company'));
    }
}