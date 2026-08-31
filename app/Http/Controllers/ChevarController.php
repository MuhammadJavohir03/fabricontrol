<?php

namespace App\Http\Controllers;

use App\Models\Brak;
use App\Models\ChevarIsh;
use App\Models\ChevarTolov;
use App\Models\Company;
use App\Models\Products;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChevarController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Company::activeId();

        $filterChevar = $request->get('chevar_id');
        $filterProduct = $request->get('product_id');
        $q = trim((string) $request->get('q', ''));

        $chevarlar = User::where('role', 'chevar')
            ->where('company_id', $companyId)
            ->orderBy('toliq_ism')
            ->get()
            ->map(function ($ch) {
                $ch->jami_ish_pul = (float) $ch->chevarIshlari()->sum('jami_pul');
                $ch->jami_tolov = (float) $ch->chevarTolovlar()->sum('summa');
                $ch->jami_jarima = (float) $ch->braklar()->sum('chevar_jarima');
                $ch->pul_dona = (float) $ch->chevarIshlari()->avg('pul_dona');
                $ch->jami_dona = (int) $ch->chevarIshlari()->sum('miqdori');
                $ch->brak_dona = (int) $ch->braklar()->sum('miqdori');
                $ch->balans = round($ch->jami_ish_pul - $ch->jami_tolov - $ch->jami_jarima, 2);

                return $ch;
            });

        $ishQuery = ChevarIsh::with(['chevar', 'product'])
            ->where('company_id', $companyId)
            ->latest('sana')->latest('id');
        $tolovQuery = ChevarTolov::with('chevar')
            ->where('company_id', $companyId)
            ->latest('sana')->latest('id');
        $brakQuery = Brak::with(['chevar', 'product'])
            ->where('company_id', $companyId)
            ->whereNotNull('chevar_id')
            ->latest('sana')->latest('id');

        if ($filterChevar) {
            $ishQuery->where('chevar_id', $filterChevar);
            $tolovQuery->where('chevar_id', $filterChevar);
            $brakQuery->where('chevar_id', $filterChevar);
        }
        if ($filterProduct) {
            $ishQuery->where('product_id', $filterProduct);
            $brakQuery->where('product_id', $filterProduct);
        }

        $ishlar = $ishQuery->take(100)->get();
        $tolovlar = $tolovQuery->take(100)->get();
        $braklar = $brakQuery->take(100)->get();

        if ($q !== '') {
            $ql = mb_strtolower($q);
            $ishlar = $ishlar->filter(function ($i) use ($ql) {
                $hay = mb_strtolower(($i->chevar->toliq_ism ?? '') . ' ' . ($i->product->nomi ?? '') . ' ' . ($i->izoh ?? ''));
                return str_contains($hay, $ql);
            })->values();
            $tolovlar = $tolovlar->filter(function ($t) use ($ql) {
                $hay = mb_strtolower(($t->chevar->toliq_ism ?? '') . ' ' . ($t->izoh ?? ''));
                return str_contains($hay, $ql);
            })->values();
            $braklar = $braklar->filter(function ($b) use ($ql) {
                $hay = mb_strtolower(($b->chevar->toliq_ism ?? '') . ' ' . ($b->product->nomi ?? '') . ' ' . ($b->sabab ?? ''));
                return str_contains($hay, $ql);
            })->values();
        }

        $products = Products::where('company_id', $companyId)->orderBy('nomi')->get();

        return view('admin.xodimlar', compact(
            'chevarlar', 'ishlar', 'tolovlar', 'braklar', 'products',
            'filterChevar', 'filterProduct', 'q'
        ));
    }

    public function storeChevar(Request $request)
    {
        $companyId = Company::activeId();

        $validated = $request->validate([
            'toliq_ism' => 'required|string|max:255',
            'tel_nomer' => 'required|string|max:30|unique:users,tel_nomer',
            'email'     => 'nullable|email|max:255|unique:users,email',
            'password'  => 'nullable|string|min:6',
        ]);

        $email = $validated['email']
            ?? ('chevar_' . preg_replace('/\D/', '', $validated['tel_nomer']) . '@local.test');

        if (User::where('email', $email)->exists()) {
            $email = 'chevar_' . time() . '@local.test';
        }

        User::create([
            'toliq_ism'  => $validated['toliq_ism'],
            'tel_nomer'  => $validated['tel_nomer'],
            'email'      => $email,
            'password'   => Hash::make($validated['password'] ?? 'chevar123'),
            'role'       => 'chevar',
            'company_id' => $companyId,
        ]);

        return back()->with('success', "Chevar «{$validated['toliq_ism']}» qo'shildi.");
    }

    public function storeIsh(Request $request)
    {
        $companyId = Company::activeId();

        $validated = $request->validate([
            'chevar_id'  => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'miqdori'    => 'required|integer|min:1',
            'pul_dona'   => 'required|numeric|min:0',
            'izoh'       => 'nullable|string|max:255',
            'sana'       => 'nullable|date',
        ]);

        $chevar = User::findOrFail($validated['chevar_id']);
        if ($chevar->role !== 'chevar' || (int) $chevar->company_id !== (int) $companyId) {
            return back()->with('error', 'Tanlangan foydalanuvchi chevar emas.');
        }

        $product = Products::findOrFail($validated['product_id']);
        if ((int) $product->company_id !== (int) $companyId) {
            abort(403);
        }

        $jami = round($validated['miqdori'] * $validated['pul_dona'], 2);

        ChevarIsh::create([
            'chevar_id'  => $validated['chevar_id'],
            'product_id' => $validated['product_id'],
            'company_id' => $companyId,
            'miqdori'    => $validated['miqdori'],
            'pul_dona'   => $validated['pul_dona'],
            'jami_pul'   => $jami,
            'izoh'       => $validated['izoh'] ?? null,
            'sana'       => $validated['sana'] ?? now()->toDateString(),
        ]);

        // 1 dona tan narx uchun: mahsulotdagi jami chevar stavkasini qayta hisobla
        $this->syncProductChevarPuli((int) $validated['product_id']);

        return back()->with('success', "Ish yozildi: {$chevar->toliq_ism} — {$validated['miqdori']} dona × {$validated['pul_dona']} = {$jami} so'm. Tan narx yangilandi.");
    }

    public function storeTolov(Request $request)
    {
        $companyId = Company::activeId();

        $validated = $request->validate([
            'chevar_id' => 'required|exists:users,id',
            'summa'     => 'required|numeric|min:0.01',
            'sana'      => 'nullable|date',
            'izoh'      => 'nullable|string',
        ]);

        $chevar = User::findOrFail($validated['chevar_id']);
        if ($chevar->role !== 'chevar' || (int) $chevar->company_id !== (int) $companyId) {
            return back()->with('error', 'Tanlangan foydalanuvchi chevar emas.');
        }

        $balans = $chevar->chevarBalans();
        if ($validated['summa'] > $balans + 0.001) {
            return back()->with('error', 'Balans faqat ' . number_format($balans, 0, '.', ' ') . " so'm.");
        }

        ChevarTolov::create([
            'chevar_id'  => $chevar->id,
            'company_id' => $companyId,
            'summa'      => $validated['summa'],
            'sana'       => $validated['sana'] ?? now()->toDateString(),
            'izoh'       => $validated['izoh'] ?? null,
        ]);

        $qoldiq = round($balans - $validated['summa'], 2);

        return back()->with('success', "«{$chevar->toliq_ism}»ga " . number_format($validated['summa'], 0, '.', ' ') . ' so\'m to\'landi. Qoldiq: ' . number_format($qoldiq, 0, '.', ' ') . ' so\'m.');
    }

    public function destroyIsh(ChevarIsh $ish)
    {
        if ((int) $ish->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $productId = (int) $ish->product_id;
        $ish->delete();
        $this->syncProductChevarPuli($productId);
        return back()->with('success', "Ish yozuvi o'chirildi, tan narx yangilandi.");
    }

    public function destroyTolov(ChevarTolov $tolov)
    {
        if ((int) $tolov->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $tolov->delete();
        return back()->with('success', "To'lov yozuvi o'chirildi.");
    }

    public function destroyChevar(User $user)
    {
        if ($user->role !== 'chevar' || (int) $user->company_id !== (int) Company::activeId()) {
            return back()->with('error', "Faqat chevar o'chiriladi.");
        }
        if ($user->chevarIshlari()->exists() || $user->chevarTolovlar()->exists()) {
            return back()->with('error', "Bu chevarda yozuvlar bor.");
        }
        $nomi = $user->toliq_ism;
        $user->delete();
        return back()->with('success', "Chevar «{$nomi}» o'chirildi.");
    }

    public function updateChevar(Request $request, User $user)
    {
        if ($user->role !== 'chevar' || (int) $user->company_id !== (int) Company::activeId()) {
            return back()->with('error', 'Faqat chevar tahrirlanadi.');
        }
        $validated = $request->validate([
            'toliq_ism' => 'required|string|max:255',
            'tel_nomer' => 'required|string|max:30|unique:users,tel_nomer,' . $user->id,
            'email'     => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:6',
        ]);
        $user->toliq_ism = $validated['toliq_ism'];
        $user->tel_nomer = $validated['tel_nomer'];
        if (! empty($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        return back()->with('success', "«{$user->toliq_ism}» yangilandi.");
    }

    public function updateIsh(Request $request, ChevarIsh $ish)
    {
        if ((int) $ish->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $validated = $request->validate([
            'chevar_id'  => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'miqdori'    => 'required|integer|min:1',
            'pul_dona'   => 'required|numeric|min:0',
            'izoh'       => 'nullable|string|max:255',
            'sana'       => 'nullable|date',
        ]);
        $jami = round($validated['miqdori'] * $validated['pul_dona'], 2);
        $oldProduct = (int) $ish->product_id;
        $ish->update([
            'chevar_id'  => $validated['chevar_id'],
            'product_id' => $validated['product_id'],
            'miqdori'    => $validated['miqdori'],
            'pul_dona'   => $validated['pul_dona'],
            'jami_pul'   => $jami,
            'izoh'       => $validated['izoh'] ?? null,
            'sana'       => $validated['sana'] ?? $ish->sana,
        ]);
        $this->syncProductChevarPuli($oldProduct);
        $this->syncProductChevarPuli((int) $validated['product_id']);
        return back()->with('success', 'Ish yangilandi, tan narx qayta hisoblandi.');
    }

    public function updateTolov(Request $request, ChevarTolov $tolov)
    {
        if ((int) $tolov->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $validated = $request->validate([
            'chevar_id' => 'required|exists:users,id',
            'summa'     => 'required|numeric|min:0.01',
            'sana'      => 'nullable|date',
            'izoh'      => 'nullable|string',
        ]);
        $tolov->update([
            'chevar_id' => $validated['chevar_id'],
            'summa'     => $validated['summa'],
            'sana'      => $validated['sana'] ?? $tolov->sana,
            'izoh'      => $validated['izoh'] ?? null,
        ]);
        return back()->with('success', "To'lov yangilandi.");
    }

    private function syncProductChevarPuli(int $productId): void
    {
        $rows = ChevarIsh::where('product_id', $productId)
            ->selectRaw('COALESCE(izoh, "") as iz, AVG(pul_dona) as rate')
            ->groupBy('iz')
            ->get();

        $jami = round((float) $rows->sum('rate'), 2);
        Products::where('id', $productId)->update(['chevar_puli' => $jami]);
    }
}