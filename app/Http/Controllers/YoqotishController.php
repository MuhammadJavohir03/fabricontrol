<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Xomashyo;
use App\Models\Yoqotish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YoqotishController extends Controller
{
    /**
     * Ishlatib bo'lmaydigan mato/material qoldig'i.
     * Ombordan ayiriladi → zarar.
     */
    public function store(Request $request)
    {
        $companyId = Company::activeId();

        $validated = $request->validate([
            'xomashyo_id' => 'required|exists:xomashyolar,id',
            'miqdori'     => 'required|numeric|min:0.001',
            'sabab'       => 'nullable|string|max:255',
            'sana'        => 'nullable|date',
            'izoh'        => 'nullable|string',
        ]);

        $x = Xomashyo::findOrFail($validated['xomashyo_id']);

        if ((int) $x->company_id !== (int) $companyId) {
            abort(403);
        }

        if ($x->ombordagi_qoldiq < $validated['miqdori']) {
            $bor = rtrim(rtrim(number_format($x->ombordagi_qoldiq, 3, '.', ''), '0'), '.');
            return back()->withInput()->with('error', "Omborda faqat {$bor} {$x->birlik} bor.");
        }

        $summa = round($validated['miqdori'] * $x->narxi_birlik_uchun, 2);

        DB::transaction(function () use ($x, $validated, $summa, $companyId) {
            $x->decrement('ombordagi_qoldiq', $validated['miqdori']);

            Yoqotish::create([
                'xomashyo_id' => $x->id,
                'company_id'  => $companyId,
                'miqdori'     => $validated['miqdori'],
                'summa'       => $summa,
                'sabab'       => $validated['sabab'] ?: "Ishlatib bo'lmaydigan qoldiq",
                'sana'        => $validated['sana'] ?? now()->toDateString(),
                'izoh'        => $validated['izoh'] ?? null,
            ]);
        });

        $fmt = rtrim(rtrim(number_format($validated['miqdori'], 3, '.', ''), '0'), '.');

        return back()->with('success', "«{$x->nomi}»dan {$fmt} {$x->birlik} yo'qotishga yozildi ({$summa} so'm zarar).");
    }

    public function destroy(Yoqotish $yoqotish)
    {
        if ((int) $yoqotish->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        DB::transaction(function () use ($yoqotish) {
            if ($yoqotish->xomashyo) {
                $yoqotish->xomashyo->increment('ombordagi_qoldiq', $yoqotish->miqdori);
            }
            $yoqotish->delete();
        });

        return back()->with('success', "Yo'qotish o'chirildi, xomashyo omborga qaytarildi.");
    }
}