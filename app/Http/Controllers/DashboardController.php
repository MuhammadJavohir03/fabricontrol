<?php

namespace App\Http\Controllers;

use App\Models\Brak;
use App\Models\Buyurtma;
use App\Models\Chiqim;
use App\Models\Company;
use App\Models\Products;
use App\Models\Sotuv;
use App\Models\Xomashyo;
use App\Models\Yoqotish;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController
{
    public function index()
    {
        $companyId = Company::activeId();
        $now = now();

        $hasBuyurtma = Schema::hasTable('buyurtmalar') && class_exists(Buyurtma::class);

        // ---- Asosiy ko'rsatkichlar ----
        $jamiMahsulot = Products::where('company_id', $companyId)->count();

        $jamiSotuv = Sotuv::where('company_id', $companyId)->count();
        $jamiBuyurtma = $hasBuyurtma
            ? Buyurtma::where('company_id', $companyId)->count()
            : Sotuv::where('company_id', $companyId)->where('holat', 'buyurtma')->count();

        $yangiBuyurtma = $hasBuyurtma
            ? Buyurtma::where('company_id', $companyId)->where('holat', 'yangi')->count()
            : Sotuv::where('company_id', $companyId)->where('holat', 'buyurtma')->count();

        $sotuvFoyda = round(
            Sotuv::where('company_id', $companyId)->get()->sum(fn ($s) => $s->foyda),
            2
        );

        $buyurtmaFoyda = 0.0;
        if ($hasBuyurtma) {
            $buyurtmaFoyda = round(
                Buyurtma::where('company_id', $companyId)
                    ->where('holat', 'bajarildi')
                    ->get()
                    ->sum(fn ($b) => $b->foyda),
                2
            );
        }

        $jamiChiqim = (float) Chiqim::where('company_id', $companyId)->sum('summa');
        $jamiYoqotish = (float) Yoqotish::where('company_id', $companyId)->sum('summa');
        $jamiBrak = (float) Brak::where('company_id', $companyId)->sum('summa');
        $jamiZarar = round($jamiChiqim + $jamiYoqotish + $jamiBrak, 2);

        $sofFoyda = round($sotuvFoyda + $buyurtmaFoyda - $jamiZarar, 2);

        $jamiTushum = round(
            (float) Sotuv::where('company_id', $companyId)->sum('jami_summa'),
            2
        );
        if ($hasBuyurtma) {
            $jamiTushum += round(
                Buyurtma::where('company_id', $companyId)
                    ->whereIn('holat', ['yangi', 'jarayonda', 'bajarildi'])
                    ->get()
                    ->sum(fn ($b) => $b->jami_summa),
                2
            );
        }

        $xomashyoQiymati = round(
            Xomashyo::where('company_id', $companyId)
                ->get()
                ->sum(fn ($x) => $x->narxi_birlik_uchun * $x->ombordagi_qoldiq),
            2
        );

        $stats = [
            'jami_mahsulotlar'  => $jamiMahsulot,
            'jami_buyurtmalar' => $jamiBuyurtma,
            'jami_sotuvlar'    => $jamiSotuv,
            'yangi_buyurtmalar'=> $yangiBuyurtma,
            'jami_foyda'       => $sofFoyda,
            'jami_tushum'      => $jamiTushum,
            'jami_zarar'       => $jamiZarar,
            'xomashyo_qiymati' => $xomashyoQiymati,
            'sotuv_foyda'      => $sotuvFoyda,
            'buyurtma_foyda'   => $buyurtmaFoyda,
        ];

        // ---- Davrlar: hafta / oy / yil ----
        $periods = [
            'hafta' => $this->periodStats($companyId, $hasBuyurtma, $now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
            'oy'    => $this->periodStats($companyId, $hasBuyurtma, $now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
            'yil'   => $this->periodStats($companyId, $hasBuyurtma, $now->copy()->startOfYear(), $now->copy()->endOfYear()),
        ];

        // ---- Haftalik grafik (so'nggi 7 kun) ----
        $weeklyChart = $this->weeklyChart($companyId, $hasBuyurtma);

        // ---- Top mahsulotlar (sotuv bo'yicha) ----
        $topProducts = Sotuv::where('company_id', $companyId)
            ->select(
                'product_id',
                DB::raw('SUM(miqdori) as jami_miqdor'),
                DB::raw('SUM(jami_summa) as jami_summa')
            )
            ->groupBy('product_id')
            ->orderByDesc('jami_miqdor')
            ->take(5)
            ->with('product')
            ->get();

        // ---- Oxirgi buyurtmalar ----
        $oxirgiBuyurtmalar = collect();
        if ($hasBuyurtma) {
            $oxirgiBuyurtmalar = Buyurtma::with(['product', 'productRang', 'mijoz'])
                ->where('company_id', $companyId)
                ->latest('sana')
                ->latest('id')
                ->take(8)
                ->get();
        } else {
            $oxirgiBuyurtmalar = Sotuv::with(['product', 'productRang'])
                ->where('company_id', $companyId)
                ->where('holat', 'buyurtma')
                ->latest('sana')
                ->latest('id')
                ->take(8)
                ->get();
        }

        // ---- Oxirgi sotuvlar ----
        $oxirgiSotuvlar = Sotuv::with(['product', 'productRang'])
            ->where('company_id', $companyId)
            ->latest('sana')
            ->latest('id')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'periods',
            'weeklyChart',
            'topProducts',
            'oxirgiBuyurtmalar',
            'oxirgiSotuvlar',
            'hasBuyurtma'
        ));
    }

    /**
     * Berilgan oralikdagi sotuv + buyurtma + zarar + foyda.
     */
    private function periodStats(?int $companyId, bool $hasBuyurtma, Carbon $from, Carbon $to): array
    {
        $sotuvlar = Sotuv::where('company_id', $companyId)
            ->whereBetween('sana', [$from->toDateString(), $to->toDateString()])
            ->get();

        $sotuvSumma = round($sotuvlar->sum('jami_summa'), 2);
        $sotuvFoyda = round($sotuvlar->sum(fn ($s) => $s->foyda), 2);
        $sotuvSoni  = $sotuvlar->count();
        $sotuvDona  = (int) $sotuvlar->sum('miqdori');

        $buyurtmaSumma = 0.0;
        $buyurtmaFoyda = 0.0;
        $buyurtmaSoni  = 0;
        $buyurtmaYangi = 0;
        $buyurtmaBajar = 0;

        if ($hasBuyurtma) {
            $buyurtmalar = Buyurtma::where('company_id', $companyId)
                ->whereBetween('sana', [$from->toDateString(), $to->toDateString()])
                ->get();
            $buyurtmaSoni  = $buyurtmalar->count();
            $buyurtmaSumma = round($buyurtmalar->sum(fn ($b) => $b->jami_summa), 2);
            $buyurtmaFoyda = round(
                $buyurtmalar->where('holat', 'bajarildi')->sum(fn ($b) => $b->foyda),
                2
            );
            $buyurtmaYangi = $buyurtmalar->where('holat', 'yangi')->count();
            $buyurtmaBajar = $buyurtmalar->where('holat', 'bajarildi')->count();
        }

        $chiqim = (float) Chiqim::where('company_id', $companyId)
            ->whereBetween('sana', [$from->toDateString(), $to->toDateString()])
            ->sum('summa');
        $yoqotish = (float) Yoqotish::where('company_id', $companyId)
            ->whereBetween('sana', [$from->toDateString(), $to->toDateString()])
            ->sum('summa');
        $brak = (float) Brak::where('company_id', $companyId)
            ->whereBetween('sana', [$from->toDateString(), $to->toDateString()])
            ->sum('summa');
        $zarar = round($chiqim + $yoqotish + $brak, 2);

        $sof = round($sotuvFoyda + $buyurtmaFoyda - $zarar, 2);

        return [
            'sotuv_soni'      => $sotuvSoni,
            'sotuv_dona'      => $sotuvDona,
            'sotuv_summa'     => $sotuvSumma,
            'sotuv_foyda'     => $sotuvFoyda,
            'buyurtma_soni'   => $buyurtmaSoni,
            'buyurtma_summa'  => $buyurtmaSumma,
            'buyurtma_foyda'  => $buyurtmaFoyda,
            'buyurtma_yangi'  => $buyurtmaYangi,
            'buyurtma_bajar'  => $buyurtmaBajar,
            'zarar'           => $zarar,
            'sof_foyda'       => $sof,
            'tushum'          => round($sotuvSumma + $buyurtmaSumma, 2),
        ];
    }

    /** So'nggi 7 kun: har kun buyurtma + sotuv soni */
    private function weeklyChart(?int $companyId, bool $hasBuyurtma): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $date = $day->toDateString();

            $sotuv = Sotuv::where('company_id', $companyId)->whereDate('sana', $date)->count();
            $buyurtma = 0;
            if ($hasBuyurtma) {
                $buyurtma = Buyurtma::where('company_id', $companyId)->whereDate('sana', $date)->count();
            }

            $days[] = [
                'label'    => $day->locale('uz')->translatedFormat('D'),
                'date'     => $day->format('d.m'),
                'sotuv'    => $sotuv,
                'buyurtma' => $buyurtma,
                'value'    => $sotuv + $buyurtma,
            ];
        }

        return $days;
    }
}
