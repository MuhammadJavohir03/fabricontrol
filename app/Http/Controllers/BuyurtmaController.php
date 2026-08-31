<?php

namespace App\Http\Controllers;

use App\Models\Buyurtma;
use App\Models\Company;
use App\Models\ProductRang;
use App\Models\Products;
use App\Models\User;
use Illuminate\Http\Request;

class BuyurtmaController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Company::activeId();

        $holat = $request->get('holat');
        $q = trim((string) $request->get('q', ''));

        $query = Buyurtma::with(['mijoz', 'product', 'productRang'])
            ->where('company_id', $companyId)
            ->latest('sana')
            ->latest('id');

        if ($holat && array_key_exists($holat, Buyurtma::HOLATLAR)) {
            $query->where('holat', $holat);
        }

        $buyurtmalar = $query->take(200)->get();

        if ($q !== '') {
            $ql = mb_strtolower($q);
            $buyurtmalar = $buyurtmalar->filter(function ($b) use ($ql) {
                $hay = mb_strtolower(
                    ($b->mijoz_ism ?? '') . ' ' .
                    ($b->mijoz_tel ?? '') . ' ' .
                    ($b->mijoz->toliq_ism ?? '') . ' ' .
                    ($b->buyurtma_nomi ?? '') . ' ' .
                    ($b->product->nomi ?? '') . ' ' .
                    ($b->rangi ?? '') . ' ' .
                    ($b->izoh ?? '')
                );

                return str_contains($hay, $ql);
            })->values();
        }

        $products = Products::with('ranglar')
            ->where('company_id', $companyId)
            ->orderBy('nomi')
            ->get();

        $mijozlar = User::where('company_id', $companyId)
            ->whereIn('role', ['client', 'mijoz'])
            ->orderBy('toliq_ism')
            ->get();

        $stats = [
            'yangi'      => Buyurtma::where('company_id', $companyId)->where('holat', 'yangi')->count(),
            'jarayonda'  => Buyurtma::where('company_id', $companyId)->where('holat', 'jarayonda')->count(),
            'bajarildi'  => Buyurtma::where('company_id', $companyId)->where('holat', 'bajarildi')->count(),
            'jami_summa' => round((float) Buyurtma::where('company_id', $companyId)
                ->whereIn('holat', ['yangi', 'jarayonda', 'bajarildi'])
                ->get()
                ->sum(fn ($b) => $b->jami_summa), 2),
            'jami_foyda' => round((float) Buyurtma::where('company_id', $companyId)
                ->where('holat', 'bajarildi')
                ->get()
                ->sum(fn ($b) => $b->foyda), 2),
        ];

        $barchaRanglar = ProductRang::with('product')
            ->where('company_id', $companyId)
            ->orderBy('product_id')
            ->get();

        return view('admin.buyurtmalar', compact(
            'buyurtmalar',
            'products',
            'mijozlar',
            'stats',
            'holat',
            'q',
            'barchaRanglar'
        ));
    }

    public function store(Request $request)
    {
        $companyId = Company::activeId();
        $validated = $this->validateBuyurtma($request);

        $tan = $this->resolveTanNarxi($validated);

        Buyurtma::create([
            'company_id'      => $companyId,
            'mijoz_id'        => $validated['mijoz_id'] ?? null,
            'mijoz_ism'       => $validated['mijoz_ism'] ?? null,
            'mijoz_tel'       => $validated['mijoz_tel'] ?? null,
            'product_id'      => $validated['product_id'] ?? null,
            'product_rang_id' => $validated['product_rang_id'] ?? null,
            'buyurtma_nomi'   => $validated['buyurtma_nomi'] ?? null,
            'rangi'           => $validated['rangi'] ?? null,
            'miqdori'         => $validated['miqdori'],
            'narxi_dona'      => $validated['narxi_dona'],
            'tan_narxi_dona'  => $tan,
            'muddat'          => $validated['muddat'] ?? null,
            'holat'           => $validated['holat'] ?? 'yangi',
            'sana'            => $validated['sana'] ?? now()->toDateString(),
            'izoh'            => $validated['izoh'] ?? null,
        ]);

        return back()->with('success', 'Buyurtma qo\'shildi.');
    }

    public function update(Request $request, Buyurtma $buyurtma)
    {
        $this->authorizeCompany($buyurtma);

        $validated = $this->validateBuyurtma($request, true);
        $tan = $this->resolveTanNarxi($validated, $buyurtma);

        $buyurtma->update([
            'mijoz_id'        => $validated['mijoz_id'] ?? null,
            'mijoz_ism'       => $validated['mijoz_ism'] ?? null,
            'mijoz_tel'       => $validated['mijoz_tel'] ?? null,
            'product_id'      => $validated['product_id'] ?? null,
            'product_rang_id' => $validated['product_rang_id'] ?? null,
            'buyurtma_nomi'   => $validated['buyurtma_nomi'] ?? null,
            'rangi'           => $validated['rangi'] ?? null,
            'miqdori'         => $validated['miqdori'],
            'narxi_dona'      => $validated['narxi_dona'],
            'tan_narxi_dona'  => $tan,
            'muddat'          => $validated['muddat'] ?? null,
            'holat'           => $validated['holat'] ?? $buyurtma->holat,
            'sana'            => $validated['sana'] ?? $buyurtma->sana,
            'izoh'            => $validated['izoh'] ?? null,
        ]);

        return back()->with('success', 'Buyurtma yangilandi.');
    }

    /** Holatni alohida o'zgartirish (masalan «Bajarildi» tugmasi) */
    public function updateHolat(Request $request, Buyurtma $buyurtma)
    {
        $this->authorizeCompany($buyurtma);

        $validated = $request->validate([
            'holat' => 'required|in:yangi,jarayonda,bajarildi,bekor',
        ]);

        $buyurtma->update(['holat' => $validated['holat']]);

        $label = Buyurtma::HOLATLAR[$validated['holat']] ?? $validated['holat'];

        return back()->with('success', "Holat: {$label}.");
    }

    public function destroy(Buyurtma $buyurtma)
    {
        $this->authorizeCompany($buyurtma);
        $buyurtma->delete();

        return back()->with('success', 'Buyurtma o\'chirildi.');
    }

    private function authorizeCompany(Buyurtma $buyurtma): void
    {
        if ((int) $buyurtma->company_id !== (int) Company::activeId()) {
            abort(403);
        }
    }

    private function validateBuyurtma(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'mijoz_id'        => 'nullable|exists:users,id',
            'mijoz_ism'       => 'nullable|string|max:255',
            'mijoz_tel'       => 'nullable|string|max:32',
            'product_id'      => 'nullable|exists:products,id',
            'product_rang_id' => 'nullable|exists:product_ranglar,id',
            'buyurtma_nomi'   => 'nullable|string|max:255',
            'rangi'           => 'nullable|string|max:100',
            'miqdori'         => 'required|integer|min:1',
            'narxi_dona'      => 'required|numeric|min:0',
            'tan_narxi_dona'  => 'nullable|numeric|min:0',
            'muddat'          => 'nullable|date',
            'holat'           => 'nullable|in:yangi,jarayonda,bajarildi,bekor',
            'sana'            => 'nullable|date',
            'izoh'            => 'nullable|string',
        ], [
            'miqdori.required'    => 'Miqdor majburiy.',
            'narxi_dona.required' => 'Narx majburiy.',
        ]);
    }

    /**
     * Tannarx: formadan kelsa o'sha, aks holda mahsulotdan hisoblanadi, yo'q bo'lsa null.
     */
    private function resolveTanNarxi(array $validated, ?Buyurtma $existing = null): ?float
    {
        if (array_key_exists('tan_narxi_dona', $validated) && $validated['tan_narxi_dona'] !== null && $validated['tan_narxi_dona'] !== '') {
            return (float) $validated['tan_narxi_dona'];
        }

        if (! empty($validated['product_id'])) {
            $product = Products::with('xomashyolar')->find($validated['product_id']);
            if ($product && (int) $product->company_id === (int) Company::activeId()) {
                return $product->hisoblaTanNarxi();
            }
        }

        return $existing?->tan_narxi_dona;
    }
}
