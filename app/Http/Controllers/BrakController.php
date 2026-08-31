<?php

namespace App\Http\Controllers;

use App\Models\Brak;
use App\Models\Company;
use App\Models\ProductRang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrakController extends Controller
{
    public function store(Request $request)
    {
        $companyId = Company::activeId();

        $validated = $request->validate([
            'product_rang_id' => 'nullable|exists:product_ranglar,id',
            'product_id'      => 'nullable|exists:products,id',
            'miqdori'         => 'required|integer|min:1',
            'chevar_id'       => 'nullable|exists:users,id',
            'pul_dona'        => 'nullable|numeric|min:0',
            'sabab'           => 'nullable|string|max:255',
            'sana'            => 'nullable|date',
        ]);

        if (! empty($validated['product_rang_id'])) {
            $rang = ProductRang::with('product.xomashyolar')->findOrFail($validated['product_rang_id']);
        } elseif (! empty($validated['product_id'])) {
            // Xodimlar sahifasidan: product_id keladi — birinchi rangni olamiz yoki xato
            $rang = ProductRang::with('product.xomashyolar')
                ->where('product_id', $validated['product_id'])
                ->orderBy('id')
                ->first();
            if (! $rang) {
                return back()->with('error', 'Bu mahsulotda rang yo\'q. Avval rang qo\'shing.');
            }
        } else {
            return back()->with('error', 'Mahsulot/rang tanlanmagan.');
        }
        $product = $rang->product;

        if ((int) $product->company_id !== (int) $companyId) {
            abort(403);
        }

        if ($validated['miqdori'] > $rang->soni) {
            return back()->with('error', "Omborda faqat {$rang->soni} dona ({$rang->rangi}) bor.");
        }

        $chevarId = $validated['chevar_id'] ?? null;
        $chevarJarima = 0;

        if ($chevarId) {
            $chevar = User::find($chevarId);
            if (! $chevar || $chevar->role !== 'chevar' || (int) $chevar->company_id !== (int) $companyId) {
                return back()->with('error', 'Tanlangan foydalanuvchi chevar emas.');
            }
            $pulDona = array_key_exists('pul_dona', $validated) && $validated['pul_dona'] !== null
                ? (float) $validated['pul_dona']
                : (float) $product->chevar_puli;
            $chevarJarima = round($pulDona * $validated['miqdori'], 2);
        }

        $tan = $product->hisoblaTanNarxi();
        $summa = round($tan * $validated['miqdori'], 2);

        DB::transaction(function () use ($product, $rang, $validated, $tan, $summa, $chevarId, $chevarJarima, $companyId) {
            $rang->decrement('soni', $validated['miqdori']);

            Brak::create([
                'product_id'      => $product->id,
                'product_rang_id' => $rang->id,
                'company_id'      => $companyId,
                'chevar_id'       => $chevarId,
                'miqdori'         => $validated['miqdori'],
                'tan_narxi_dona'  => $tan,
                'summa'           => $summa,
                'chevar_jarima'   => $chevarJarima,
                'sabab'           => $validated['sabab'] ?? 'Brak',
                'sana'            => $validated['sana'] ?? now()->toDateString(),
            ]);
        });

        $msg = "«{$rang->label}»dan {$validated['miqdori']} dona brak ({$summa} so'm zarar)";
        if ($chevarJarima > 0) {
            $msg .= ", chevar balansidan -{$chevarJarima} so'm";
        }

        return back()->with('success', $msg . '.');
    }

    public function update(Request $request, Brak $brak)
    {
        if ((int) $brak->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $validated = $request->validate([
            'miqdori'   => 'required|integer|min:1',
            'chevar_id' => 'nullable|exists:users,id',
            'pul_dona'  => 'nullable|numeric|min:0',
            'sabab'     => 'nullable|string|max:255',
            'sana'      => 'nullable|date',
        ]);

        $rang = $brak->productRang;
        $product = $brak->product;
        $oldMiq = (int) $brak->miqdori;
        $newMiq = (int) $validated['miqdori'];
        $diff = $newMiq - $oldMiq;

        if ($diff > 0 && $rang && $rang->soni < $diff) {
            return back()->with('error', "Omborda yetarli mahsulot yo'q.");
        }

        $chevarId = $validated['chevar_id'] ?? null;
        $pulDona = array_key_exists('pul_dona', $validated) && $validated['pul_dona'] !== null
            ? (float) $validated['pul_dona']
            : ((float) ($product->chevar_puli ?? 0));
        $jarima = $chevarId ? round($pulDona * $newMiq, 2) : 0;
        $tan = $product ? $product->hisoblaTanNarxi() : (float) $brak->tan_narxi_dona;
        $summa = round($tan * $newMiq, 2);

        DB::transaction(function () use ($rang, $brak, $diff, $validated, $chevarId, $jarima, $tan, $summa, $newMiq) {
            if ($rang) {
                if ($diff > 0) {
                    $rang->decrement('soni', $diff);
                } elseif ($diff < 0) {
                    $rang->increment('soni', abs($diff));
                }
            }
            $brak->update([
                'miqdori'        => $newMiq,
                'chevar_id'      => $chevarId,
                'tan_narxi_dona' => $tan,
                'summa'          => $summa,
                'chevar_jarima'  => $jarima,
                'sabab'          => $validated['sabab'] ?? $brak->sabab,
                'sana'           => $validated['sana'] ?? $brak->sana,
            ]);
        });

        return back()->with('success', 'Brak yangilandi.');
    }

    public function destroy(Brak $brak)
    {
        if ((int) $brak->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        DB::transaction(function () use ($brak) {
            if ($brak->productRang) {
                $brak->productRang->increment('soni', $brak->miqdori);
            }
            $brak->delete();
        });

        return back()->with('success', "Brak o'chirildi.");
    }
}
