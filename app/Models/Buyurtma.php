<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buyurtma extends Model
{
    protected $table = 'buyurtmalar';

    protected $fillable = [
        'company_id',
        'mijoz_id',          // users.id (role=client) — ixtiyoriy
        'mijoz_ism',         // qo'lda kiritilgan ism
        'mijoz_tel',
        'product_id',        // bizdagi mahsulot — tayyor bo'lguncha null
        'product_rang_id',   // rang — ixtiyoriy
        'buyurtma_nomi',     // nima so'ralgan (matn, mahsulot yo'q bo'lsa)
        'rangi',             // rang matni (mahsulot rangiga bog'lanmagan bo'lishi mumkin)
        'miqdori',
        'narxi_dona',        // mijoz to'laydigan 1 dona
        'tan_narxi_dona',    // tannarx (mahsulot bog'langanda yoki qo'lda)
        'muddat',            // bajarilish muddati
        'holat',             // yangi | jarayonda | bajarildi | bekor
        'sana',
        'izoh',
    ];

    protected $casts = [
        'muddat' => 'date',
        'sana'   => 'date',
        'miqdori' => 'integer',
        'narxi_dona' => 'float',
        'tan_narxi_dona' => 'float',
    ];

    public const HOLATLAR = [
        'yangi'      => 'Yangi',
        'jarayonda'  => 'Jarayonda',
        'bajarildi'  => 'Bajarildi',
        'bekor'      => 'Bekor',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function mijoz()
    {
        return $this->belongsTo(User::class, 'mijoz_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function productRang()
    {
        return $this->belongsTo(ProductRang::class, 'product_rang_id');
    }

    /** Jami summa = miqdor × narx */
    public function getJamiSummaAttribute(): float
    {
        return round((float) $this->narxi_dona * (int) $this->miqdori, 2);
    }

    /** Foyda = (narx − tannarx) × miqdor. Tannarx null bo'lsa 0. */
    public function getFoydaAttribute(): float
    {
        $tan = (float) ($this->tan_narxi_dona ?? 0);

        return round(((float) $this->narxi_dona - $tan) * (int) $this->miqdori, 2);
    }

    public function getHolatLabelAttribute(): string
    {
        return self::HOLATLAR[$this->holat] ?? $this->holat;
    }

    /** Ko'rsatish uchun nom: mahsulot yoki matn */
    public function getNomiAttribute(): string
    {
        if ($this->product) {
            $base = $this->product->nomi;
            $rang = $this->productRang->rangi ?? $this->rangi;

            return $rang ? "{$base} — {$rang}" : $base;
        }

        $base = $this->buyurtma_nomi ?: 'Noma\'lum';

        return $this->rangi ? "{$base} — {$this->rangi}" : $base;
    }

    public function getMijozNomiAttribute(): string
    {
        if ($this->mijoz) {
            return $this->mijoz->toliq_ism ?? $this->mijoz->email;
        }

        return $this->mijoz_ism ?: '—';
    }
}
