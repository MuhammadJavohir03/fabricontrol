<?php

namespace App\Http\Controllers;

use App\Models\Brak;
use App\Models\ChevarIsh;
use App\Models\User;
use App\Models\Chiqim;
use App\Models\Company;
use App\Models\IshlabChiqarish;
use App\Models\Products;
use App\Models\ProductRang;
use App\Models\Sotuv;
use App\Models\Xomashyo;
use App\Models\Yoqotish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index()
    {
        $companyId = Company::activeId();

        // MAHSULOTLAR
        $products = Products::with([
            'xomashyolar',
            'ranglar',
            'company',
        ])
            ->where('company_id', $companyId)
            ->latest()
            ->get()
            ->map(function ($product) {
                $product->tan_narxi = $product->hisoblaTanNarxi();
                $product->jami_soni = (int) $product->ranglar->sum('soni');

                return $product;
            });

        // XOMASHYOLAR
        $xomashyolar = Xomashyo::with('company')
            ->where('company_id', $companyId)
            ->orderBy('nomi')
            ->get();

        // SOTUVLAR
        $sotuvlar = Sotuv::with(['product', 'productRang'])
            ->where('company_id', $companyId)
            ->latest('sana')
            ->latest('id')
            ->take(50)
            ->get();

        // CHIQIMLAR
        $chiqimlar = Chiqim::where('company_id', $companyId)
            ->latest('sana')
            ->latest('id')
            ->take(50)
            ->get();

        // YO'QOTISHLAR
        $yoqotishlar = Yoqotish::with('xomashyo')
            ->where('company_id', $companyId)
            ->latest('sana')
            ->latest('id')
            ->take(50)
            ->get();

        // BRAKLAR
        $braklar = Brak::with(['product', 'productRang'])
            ->where('company_id', $companyId)
            ->latest('sana')
            ->latest('id')
            ->take(50)
            ->get();

        // STATISTIKA
        $sotuvFoyda = round(
            Sotuv::where('company_id', $companyId)
                ->get()
                ->sum(fn($s) => $s->foyda),
            2
        );

        $jamiChiqim = round(
            (float) Chiqim::where('company_id', $companyId)->sum('summa'),
            2
        );

        $jamiYoqotish = round(
            (float) Yoqotish::where('company_id', $companyId)->sum('summa'),
            2
        );

        $jamiBrak = round(
            (float) Brak::where('company_id', $companyId)->sum('summa'),
            2
        );

        $jamiZarar = round(
            $jamiChiqim + $jamiYoqotish + $jamiBrak,
            2
        );

        $sofFoyda = round(
            $sotuvFoyda - $jamiZarar,
            2
        );

        $stats = [
            'jami_mahsulot' => $products->count(),

            'xomashyo_qiymati' => round(
                $xomashyolar->sum(
                    fn($x) => $x->narxi_birlik_uchun * $x->ombordagi_qoldiq
                ),
                2
            ),

            'mahsulot_tan_qiymati' => round(
                $products->sum(
                    fn($p) => $p->tan_narxi * $p->jami_soni
                ),
                2
            ),

            'jami_tushum' => round(
                (float) Sotuv::where('company_id', $companyId)
                    ->sum('jami_summa'),
                2
            ),

            'sotuv_foyda' => $sotuvFoyda,
            'jami_chiqim' => $jamiChiqim,
            'jami_yoqotish' => $jamiYoqotish,
            'jami_brak' => $jamiBrak,
            'jami_zarar' => $jamiZarar,
            'jami_foyda' => $sofFoyda,
        ];

        // CHEVARLAR
        $chevarlar = User::where('role', 'chevar')
            ->where('company_id', $companyId)
            ->orderBy('toliq_ism')
            ->get();

        // RANGLAR
        $barchaRanglar = ProductRang::with('product')
            ->where('company_id', $companyId)
            ->orderBy('product_id')
            ->get();

        return view('admin.products', compact(
            'products',
            'stats',
            'xomashyolar',
            'sotuvlar',
            'chiqimlar',
            'yoqotishlar',
            'braklar',
            'chevarlar',
            'barchaRanglar'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $syncData = $this->buildSyncData($validated);
        $chevarPuli = (float) ($validated['chevar_puli'] ?? 0);

        $ranglar = $validated['ranglar'] ?? [];
        $totalSoni = 0;
        foreach ($ranglar as $r) {
            $totalSoni += (int) ($r['soni'] ?? 0);
        }

        if ($totalSoni > 0) {
            $err = $this->checkMaterialStock($syncData, $totalSoni);
            if ($err) {
                return back()->withInput()->with('error', $err);
            }
        }

        $product = DB::transaction(function () use ($validated, $syncData, $chevarPuli, $ranglar, $totalSoni) {
            $product = Products::create([
                'nomi'        => $validated['nomi'],
                'company_id'  => $validated['company_id'],
                'chevar_puli' => $chevarPuli,
                'izoh'        => $validated['izoh'] ?? null,
            ]);

            $product->xomashyolar()->sync($syncData);

            foreach ($ranglar as $r) {
                $soni = (int) ($r['soni'] ?? 0);
                $product->ranglar()->create([
                    'rangi' => $r['rangi'],
                    'company_id' => $validated['company_id'],
                    'soni'  => $soni,
                ]);
            }

            if ($totalSoni > 0) {
                foreach ($syncData as $xomashyoId => $pivot) {
                    Xomashyo::where('id', $xomashyoId)
                        ->decrement('ombordagi_qoldiq', $pivot['sarf_miqdori'] * $totalSoni);
                }

                $product->load('xomashyolar');
                $tan = $product->hisoblaTanNarxi();

                // Har bir rang uchun ishlab chiqarish yozuvi
                foreach ($product->ranglar as $rang) {
                    if ($rang->soni > 0) {
                        IshlabChiqarish::create([
                            'product_id'      => $product->id,
                            'product_rang_id' => $rang->id,
                            'miqdori'         => $rang->soni,
                            'tan_narxi_dona'  => $tan,
                            'sana'            => now()->toDateString(),
                        ]);
                    }
                }
            }

            return $product;
        });

        // Chevar ishlari
        $chevarIds = $request->input('chevar_id', []);
        $chevarMiq = $request->input('chevar_miqdori', []);
        $chevarPul = $request->input('chevar_pul', []);
        $chevarIzoh = $request->input('chevar_izoh', []);
        $stavkaJami = 0.0;
        foreach ($chevarIds as $i => $cid) {
            if (! $cid) {
                continue;
            }
            $mq = (int) ($chevarMiq[$i] ?? 0);
            $pul = (float) ($chevarPul[$i] ?? 0);
            if ($mq < 1) {
                continue;
            }
            $stavkaJami += $pul;
            ChevarIsh::create([
                'chevar_id'  => $cid,
                'product_id' => $product->id,
                'company_id' => $product->company_id,
                'miqdori'    => $mq,
                'pul_dona'   => $pul,
                'jami_pul'   => round($mq * $pul, 2),
                'izoh'       => $chevarIzoh[$i] ?? null,
                'sana'       => now()->toDateString(),
            ]);
        }
        if ($stavkaJami > 0) {
            $product->update(['chevar_puli' => $stavkaJami]);
        }

        $rangCount = count($ranglar);
        $msg = $totalSoni > 0
            ? "«{$product->nomi}» qo'shildi ({$rangCount} rang, jami {$totalSoni} dona), xomashyo ayirildi."
            : "«{$product->nomi}» qo'shildi ({$rangCount} rang, soni 0).";

        return redirect()->route('admin.products.index')->with('success', $msg);
    }

    public function edit(Products $product)
    {
        if ((int) $product->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $product->load(['xomashyolar', 'ranglar']);
        $xomashyolar = Xomashyo::where('company_id', Company::activeId())
            ->orderBy('nomi')
            ->get();

        return view('admin.mahsulot-tahrirlash', compact('product', 'xomashyolar'));
    }

    public function update(Request $request, Products $product)
    {
        if ((int) $product->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $validated = $this->validateProductUpdate($request);
        $syncData = $this->buildSyncData($validated);
        $companyId = (int) Company::activeId();

        $product->load(['xomashyolar', 'ranglar']);

        // ESKI retsept — kamaytirishda xomashyo SHU bo'yicha qaytariladi
        $oldRecipe = [];
        foreach ($product->xomashyolar as $x) {
            $oldRecipe[(int) $x->id] = (float) $x->pivot->sarf_miqdori;
        }

        $existingRanglar = $product->ranglar->keyBy(fn ($r) => (int) $r->id);

        $ranglar = $validated['ranglar'] ?? [];
        $diffs = [];
        $totalIncrease = 0;
        $totalDecrease = 0;

        foreach ($ranglar as $r) {
            $rid = isset($r['id']) && $r['id'] !== '' && $r['id'] !== null
                ? (int) $r['id']
                : null;

            if ($rid && ! $existingRanglar->has($rid)) {
                $rid = null; // boshqa mahsulot / yo'q id
            }

            $rangi = trim((string) ($r['rangi'] ?? ''));
            $newSoni = (int) ($r['soni'] ?? 0);
            $oldSoni = $rid ? (int) $existingRanglar[$rid]->soni : 0;
            $diff = $newSoni - $oldSoni;

            if ($diff > 0) {
                $totalIncrease += $diff;
            } elseif ($diff < 0) {
                $totalDecrease += abs($diff);
            }

            $diffs[] = [
                'id'      => $rid,
                'rangi'   => $rangi,
                'newSoni' => $newSoni,
                'oldSoni' => $oldSoni,
                'diff'    => $diff,
            ];
        }

        // Soni oshirilsa — YANGI retsept bo'yicha omborda yetarlimi
        if ($totalIncrease > 0) {
            $err = $this->checkMaterialStock($syncData, $totalIncrease);
            if ($err) {
                return back()->withInput()->with('error', $err);
            }
        }

        DB::transaction(function () use (
            $product, $validated, $syncData, $diffs, $oldRecipe, $companyId, $totalIncrease, $totalDecrease
        ) {
            $product->update([
                'nomi'        => $validated['nomi'],
                'company_id'  => $validated['company_id'],
                'chevar_puli' => (float) ($validated['chevar_puli'] ?? 0),
                'izoh'        => $validated['izoh'] ?? null,
            ]);

            // Retseptni yangilash
            $product->xomashyolar()->sync($syncData);
            $product->load('xomashyolar');

            $tan = $product->hisoblaTanNarxi();
            $existingIds = [];

            foreach ($diffs as $d) {
                if ($d['id']) {
                    $rang = $product->ranglar()->where('id', $d['id'])->first();
                    if (! $rang) {
                        continue;
                    }
                    $rang->update(['rangi' => $d['rangi']]);
                } else {
                    $rang = $product->ranglar()->create([
                        'rangi'      => $d['rangi'],
                        'company_id' => $companyId,
                        'soni'       => 0,
                    ]);
                }

                $existingIds[] = $rang->id;
                $diff = (int) $d['diff'];

                if ($diff === 0) {
                    continue;
                }

                if ($diff > 0) {
                    // Oshirish: YANGI retsept bo'yicha xomashyo ayiriladi
                    $rang->increment('soni', $diff);

                    foreach ($product->xomashyolar as $x) {
                        $sarf = (float) $x->pivot->sarf_miqdori;
                        if ($sarf > 0) {
                            Xomashyo::where('id', $x->id)
                                ->decrement('ombordagi_qoldiq', $sarf * $diff);
                        }
                    }

                    IshlabChiqarish::create([
                        'product_id'      => $product->id,
                        'product_rang_id' => $rang->id,
                        'miqdori'         => $diff,
                        'tan_narxi_dona'  => $tan,
                        'sana'            => now()->toDateString(),
                    ]);
                } else {
                    // Kamaytirish: ESKI retsept bo'yicha xomashyo QAYTARILADI
                    $abs = abs($diff);
                    $rang->decrement('soni', $abs);

                    // Agar eski retsept bo'sh bo'lsa (kamdan-kam), yangi retseptdan foydalanamiz
                    $recipeForReturn = $oldRecipe;
                    if ($recipeForReturn === []) {
                        foreach ($product->xomashyolar as $x) {
                            $recipeForReturn[(int) $x->id] = (float) $x->pivot->sarf_miqdori;
                        }
                    }

                    foreach ($recipeForReturn as $xomashyoId => $sarf) {
                        $sarf = (float) $sarf;
                        if ($sarf > 0) {
                            Xomashyo::where('id', $xomashyoId)
                                ->increment('ombordagi_qoldiq', $sarf * $abs);
                        }
                    }

                    IshlabChiqarish::create([
                        'product_id'      => $product->id,
                        'product_rang_id' => $rang->id,
                        'miqdori'         => -$abs,
                        'tan_narxi_dona'  => $tan,
                        'sana'            => now()->toDateString(),
                    ]);
                }
            }

            // Formada yo'q, soni 0 bo'lgan ranglarni o'chirish
            $product->ranglar()
                ->whereNotIn('id', $existingIds)
                ->where('soni', 0)
                ->delete();
        });

        $msg = 'Mahsulot yangilandi.';
        if ($totalIncrease > 0 && $totalDecrease > 0) {
            $msg = "Rang soni o'zgardi: +{$totalIncrease} / -{$totalDecrease} dona. Xomashyo omborda moslashtirildi.";
        } elseif ($totalIncrease > 0) {
            $msg = "Rang soni +{$totalIncrease} dona oshirildi, xomashyo ombordan ayirildi.";
        } elseif ($totalDecrease > 0) {
            $msg = "Rang soni -{$totalDecrease} dona kamaytirildi, xomashyo omborga qaytarildi.";
        }

        return redirect()->route('admin.products.index')->with('success', $msg);
    }

    public function destroy(Products $product)
    {
        if ((int) $product->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $nomi = $product->nomi;

        // Pivot va ranglar sonini to'g'ridan-to'g'ri DB dan olamiz (relationship ishonchsiz bo'lishi mumkin)
        $jamiSoni = (int) DB::table('product_ranglar')
            ->where('product_id', $product->id)
            ->sum('soni');

        $recipe = DB::table('product_xomashyolari')
            ->where('product_id', $product->id)
            ->get();

        $qaytarilgan = [];

        DB::transaction(function () use ($product, $jamiSoni, $recipe, &$qaytarilgan) {
            if ($jamiSoni > 0 && $recipe->isNotEmpty()) {
                foreach ($recipe as $row) {
                    $sarf = (float) $row->sarf_miqdori;
                    if ($sarf <= 0) {
                        continue;
                    }
                    $miqdor = $sarf * $jamiSoni;

                    // Omborga qaytarish — model emas, DB (aniq ishlaydi)
                    DB::table('xomashyolar')
                        ->where('id', $row->xomashyo_id)
                        ->increment('ombordagi_qoldiq', $miqdor);

                    $x = DB::table('xomashyolar')->where('id', $row->xomashyo_id)->first();
                    if ($x) {
                        $qaytarilgan[] = ($x->nomi ?? ('#'.$row->xomashyo_id))
                            . ': +' . rtrim(rtrim(number_format($miqdor, 3, '.', ''), '0'), '.');
                    }
                }
            }

            // Avval pivot / bog'liq yozuvlar, keyin mahsulot
            DB::table('product_xomashyolari')->where('product_id', $product->id)->delete();
            $product->delete();
        });

        if ($jamiSoni > 0 && $qaytarilgan) {
            $msg = "«{$nomi}» o'chirildi ({$jamiSoni} dona). Xomashyo qaytarildi: "
                . implode(', ', $qaytarilgan) . '.';
        } elseif ($jamiSoni > 0) {
            $msg = "«{$nomi}» o'chirildi ({$jamiSoni} dona), lekin retsept topilmadi — xomashyo qaytarilmadi.";
        } else {
            $msg = "«{$nomi}» o'chirildi. Omborda 0 dona edi — xomashyo qaytarilmadi.";
        }

        return redirect()->route('admin.products.index')->with('success', $msg);
    }

    public function produce(Request $request)
    {
        $validated = $request->validate([
            'product_rang_id' => 'required|exists:product_ranglar,id',
            'miqdori'         => 'required|integer|min:1',
        ]);

        $miqdori = $validated['miqdori'];
        $rang = ProductRang::with('product.xomashyolar')->findOrFail($validated['product_rang_id']);
        $product = $rang->product;

        if ((int) $product->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        if ($product->xomashyolar->isEmpty()) {
            return back()->with('error', "«{$product->nomi}» uchun retsept yo'q.");
        }

        $yetishmayotgan = [];
        foreach ($product->xomashyolar as $x) {
            $kerak = $x->pivot->sarf_miqdori * $miqdori;
            if ($x->ombordagi_qoldiq < $kerak) {
                $k = rtrim(rtrim(number_format($kerak, 3, '.', ''), '0'), '.');
                $b = rtrim(rtrim(number_format($x->ombordagi_qoldiq, 3, '.', ''), '0'), '.');
                $yetishmayotgan[] = "{$x->nomi}: kerak {$k} {$x->birlik}, bor {$b} {$x->birlik}";
            }
        }

        if ($yetishmayotgan) {
            return back()->with('error', "Omborda yetarli xomashyo yo'q — " . implode('; ', $yetishmayotgan));
        }

        DB::transaction(function () use ($product, $rang, $miqdori) {
            foreach ($product->xomashyolar as $x) {
                $x->decrement('ombordagi_qoldiq', $x->pivot->sarf_miqdori * $miqdori);
            }

            IshlabChiqarish::create([
                'product_id'      => $product->id,
                'product_rang_id' => $rang->id,
                'miqdori'         => $miqdori,
                'tan_narxi_dona'  => $product->hisoblaTanNarxi(),
                'sana'            => now()->toDateString(),
            ]);

            $rang->increment('soni', $miqdori);
        });

        return redirect()->route('admin.products.index')
            ->with('success', "«{$rang->label}»dan {$miqdori} dona ishlab chiqarildi.");
    }

    public function sell(Request $request)
    {
        $validated = $request->validate([
            'product_rang_id' => 'required|exists:product_ranglar,id',
            'miqdori'         => 'required|integer|min:1',
            'narxi_dona'      => 'required|numeric|min:0',
            'sana'            => 'nullable|date',
        ]);

        $rang = ProductRang::with('product.xomashyolar')->findOrFail($validated['product_rang_id']);
        $product = $rang->product;

        if ((int) $product->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        if ($validated['miqdori'] > $rang->soni) {
            return back()->with('error', "Omborda faqat {$rang->soni} dona ({$rang->rangi}) bor.");
        }

        $tan = $product->hisoblaTanNarxi();
        $jami = $validated['narxi_dona'] * $validated['miqdori'];

        DB::transaction(function () use ($product, $rang, $validated, $tan, $jami) {
            $rang->decrement('soni', $validated['miqdori']);

            Sotuv::create([
                'product_id'      => $product->id,
                'product_rang_id' => $rang->id,
                'company_id'      => $product->company_id,
                'miqdori'         => $validated['miqdori'],
                'narxi_dona'      => $validated['narxi_dona'],
                'tan_narxi_dona'  => $tan,
                'jami_summa'      => $jami,
                'sana'            => $validated['sana'] ?? now()->toDateString(),
            ]);
        });

        return redirect()->route('admin.products.index')
            ->with('success', "«{$rang->label}»dan {$validated['miqdori']} dona sotildi.");
    }

    public function updateSell(Request $request, Sotuv $sotuv)
    {
        if ((int) $sotuv->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $validated = $request->validate([
            'miqdori'    => 'required|integer|min:1',
            'narxi_dona' => 'required|numeric|min:0',
            'sana'       => 'nullable|date',
        ]);

        $rang = $sotuv->productRang;
        $product = $sotuv->product;
        $oldMiq = (int) $sotuv->miqdori;
        $newMiq = (int) $validated['miqdori'];
        $diff = $newMiq - $oldMiq;

        if ($diff > 0 && $rang && $rang->soni < $diff) {
            return back()->with('error', "Omborda faqat {$rang->soni} dona bor (qo'shimcha {$diff} kerak).");
        }

        $tan = $product ? $product->hisoblaTanNarxi() : (float) $sotuv->tan_narxi_dona;
        $jami = $validated['narxi_dona'] * $newMiq;

        DB::transaction(function () use ($rang, $sotuv, $validated, $diff, $tan, $jami, $newMiq) {
            if ($rang) {
                if ($diff > 0) {
                    $rang->decrement('soni', $diff);
                } elseif ($diff < 0) {
                    $rang->increment('soni', abs($diff));
                }
            }

            $sotuv->update([
                'miqdori'        => $newMiq,
                'narxi_dona'     => $validated['narxi_dona'],
                'tan_narxi_dona' => $tan,
                'jami_summa'     => $jami,
                'sana'           => $validated['sana'] ?? $sotuv->sana,
            ]);
        });

        return back()->with('success', 'Sotuv yangilandi.');
    }

    public function destroySell(Sotuv $sotuv)
    {
        if ((int) $sotuv->company_id !== (int) Company::activeId()) {
            abort(403);
        }

        $rang = $sotuv->productRang;
        $miq = (int) $sotuv->miqdori;

        DB::transaction(function () use ($rang, $sotuv, $miq) {
            if ($rang) {
                $rang->increment('soni', $miq);
            }
            $sotuv->delete();
        });

        return back()->with('success', "Sotuv o'chirildi, {$miq} dona omborga qaytarildi.");
    }

    private function validateProduct(Request $request): array
    {
        $request['company_id'] = Company::activeId();
        return $request->validate([
            'nomi'             => 'required|string|max:255',
            'company_id'       => 'required|exists:companies,id',
            'chevar_puli'      => 'nullable|numeric|min:0',
            'izoh'             => 'nullable|string',
            'xomashyo_id'      => 'required|array|min:1',
            'xomashyo_id.*'    => 'required|distinct|exists:xomashyolar,id',
            'sarf_miqdori'     => 'required|array|min:1',
            'sarf_miqdori.*'   => 'required|numeric|min:0.001',
            'ranglar'          => 'required|array|min:1',
            'ranglar.*.rangi'  => 'required|string|max:100',
            'ranglar.*.soni'   => 'nullable|integer|min:0',
            'chevar_id'        => 'nullable|array',
            'chevar_id.*'      => 'nullable|exists:users,id',
            'chevar_miqdori'   => 'nullable|array',
            'chevar_miqdori.*' => 'nullable|integer|min:1',
            'chevar_pul'       => 'nullable|array',
            'chevar_pul.*'     => 'nullable|numeric|min:0',
            'chevar_izoh'      => 'nullable|array',
            'chevar_izoh.*'    => 'nullable|string|max:255',
        ], [
            'xomashyo_id.required'   => 'Kamida bitta xomashyo tanlanishi shart.',
            'xomashyo_id.*.distinct' => 'Bitta xomashyo faqat bir marta tanlanishi mumkin.',
            'ranglar.required'       => 'Kamida bitta rang qo\'shilishi shart.',
            'ranglar.*.rangi.required' => 'Rang nomi majburiy.',
        ]);
    }

    private function validateProductUpdate(Request $request): array
    {
        $request['company_id'] = Company::activeId();
        return $request->validate([
            'nomi'             => 'required|string|max:255',
            'company_id'       => 'required|exists:companies,id',
            'chevar_puli'      => 'nullable|numeric|min:0',
            'izoh'             => 'nullable|string',
            'xomashyo_id'      => 'required|array|min:1',
            'xomashyo_id.*'    => 'required|distinct|exists:xomashyolar,id',
            'sarf_miqdori'     => 'required|array|min:1',
            'sarf_miqdori.*'   => 'required|numeric|min:0.001',
            'ranglar'          => 'required|array|min:1',
            'ranglar.*.id'     => 'nullable|integer|exists:product_ranglar,id',
            'ranglar.*.rangi'  => 'required|string|max:100',
            'ranglar.*.soni'   => 'nullable|integer|min:0',
        ], [
            'xomashyo_id.required'   => 'Kamida bitta xomashyo tanlanishi shart.',
            'ranglar.required'       => 'Kamida bitta rang bo\'lishi shart.',
        ]);
    }

    private function buildSyncData(array $validated): array
    {
        $sync = [];
        foreach ($validated['xomashyo_id'] as $i => $id) {
            $sync[$id] = ['sarf_miqdori' => $validated['sarf_miqdori'][$i]];
        }

        return $sync;
    }

    private function checkMaterialStock(array $syncData, int $soni): ?string
    {
        $xs = Xomashyo::whereIn('id', array_keys($syncData))->get()->keyBy('id');
        $list = [];

        foreach ($syncData as $id => $pivot) {
            $x = $xs->get($id);
            if (! $x) {
                continue;
            }
            $kerak = $pivot['sarf_miqdori'] * $soni;
            if ($x->ombordagi_qoldiq < $kerak) {
                $k = rtrim(rtrim(number_format($kerak, 3, '.', ''), '0'), '.');
                $b = rtrim(rtrim(number_format($x->ombordagi_qoldiq, 3, '.', ''), '0'), '.');
                $list[] = "{$x->nomi}: kerak {$k} {$x->birlik}, bor {$b} {$x->birlik}";
            }
        }

        return $list ? "Omborda yetarli xomashyo yo'q — " . implode('; ', $list) : null;
    }
}