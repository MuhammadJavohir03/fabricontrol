<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sotuv extends Model
{
    protected $table = 'sotuvlar';

    protected $fillable = [
        'product_id',
        'company_id',
        'product_rang_id',
        'miqdori',
        'narxi_dona',
        'tan_narxi_dona',
        'jami_summa',
        'sana',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function productRang()
    {
        return $this->belongsTo(ProductRang::class, 'product_rang_id');
    }

    public function getFoydaAttribute(): float
    {
        return round(($this->narxi_dona - $this->tan_narxi_dona) * $this->miqdori, 2);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
