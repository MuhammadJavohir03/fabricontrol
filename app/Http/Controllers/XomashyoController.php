<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Xomashyo;
use Illuminate\Http\Request;

class XomashyoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $this->validateXomashyo($request);
        Xomashyo::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', "«{$validated['nomi']}» xomashyo sifatida qo'shildi.");
    }

    public function update(Request $request, Xomashyo $xomashyo)
    {
        if ((int) $xomashyo->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $validated = $this->validateXomashyo($request);
        $xomashyo->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', "«{$xomashyo->nomi}» yangilandi.");
    }

    public function destroy(Xomashyo $xomashyo)
    {
        if ((int) $xomashyo->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        if ($xomashyo->products()->exists()) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', "«{$xomashyo->nomi}» ba'zi mahsulotlar retseptida ishlatilgan, o'chirib bo'lmaydi.");
        }

        $nomi = $xomashyo->nomi;
        $xomashyo->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', "«{$nomi}» o'chirildi.");
    }

    private function validateXomashyo(Request $request): array
    {
        $validated = $request->validate([
            'nomi'               => 'required|string|max:255',
            'rangi'              => 'nullable|string|max:100',
            'birlik'             => 'required|in:metr,kg,dona,litr',
            'narxi_birlik_uchun' => 'required|numeric|min:0',
            'ombordagi_qoldiq'   => 'nullable|numeric|min:0',
            'rulon_soni'         => 'nullable|integer|min:0',
        ]);

        // Har doim joriy companiyaga yoziladi/qayta tasdiqlanadi — boshqa companiyaga
        // hech qachon o'zgartirilmaydi, chunki ownership tekshiruvi update/destroy'da bajariladi.
        $validated['company_id'] = Company::activeId();

        $validated['ombordagi_qoldiq'] = $validated['ombordagi_qoldiq'] ?? 0;
        $validated['rulon_soni'] = $validated['rulon_soni'] ?? null;

        if (! in_array($validated['birlik'], ['kg', 'metr'], true)) {
            $validated['rulon_soni'] = null;
        }

        return $validated;
    }
}