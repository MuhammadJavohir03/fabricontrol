<?php

namespace App\Http\Controllers;

use App\Models\Brak;
use App\Models\Chiqim;
use App\Models\Company;
use App\Models\ProductRang;
use App\Models\Products;
use App\Models\Sotuv;
use App\Models\User;
use App\Models\Xomashyo;
use App\Models\Yoqotish;
use Illuminate\Http\Request;

class DashboardController
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

            'yangi_buyurtmalar' => Sotuv::where('company_id', $companyId)
                ->where('holat', 'buyurtma')
                ->count(),

            'sotuv_foyda' => $sotuvFoyda,
            'jami_mahsulotlar' => $products->count(),
            'jami_buyurtmalar' => Sotuv::where('company_id', $companyId)->count(),
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

        return view('admin.dashboard', compact(
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
}
