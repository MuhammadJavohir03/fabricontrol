<?php

namespace App\Http\Controllers;

use App\Models\Chiqim;
use App\Models\Company;
use Illuminate\Http\Request;

class ChiqimController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomi'       => 'required|string|max:255',
            'kategoriya' => 'nullable|string|max:100',
            'summa'      => 'required|numeric|min:0.01',
            'sana'       => 'nullable|date',
            'izoh'       => 'nullable|string',
        ]);

        $validated['sana'] = $validated['sana'] ?? now()->toDateString();
        $validated['kategoriya'] = $validated['kategoriya'] ?: 'boshqa';
        $validated['company_id'] = Company::activeId();

        Chiqim::create($validated);

        return back()->with('success', "Chiqim «{$validated['nomi']}» zarar sifatida yozildi ({$validated['summa']} so'm).");
    }

    public function destroy(Chiqim $chiqim)
    {
        if ((int) $chiqim->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $nomi = $chiqim->nomi;
        $chiqim->delete();

        return back()->with('success', "Chiqim «{$nomi}» o'chirildi.");
    }
}