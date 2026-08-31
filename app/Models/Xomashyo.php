<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Xomashyo extends Model
{
    protected $table = 'xomashyolar';

    protected $fillable = [
        'nomi',
        'company_id',
        'rangi',
        'birlik',
        'narxi_birlik_uchun',
        'ombordagi_qoldiq',
        'rulon_soni',
    ];

    public function products()
    {
        return $this->belongsToMany(Products::class, 'product_xomashyolari', 'xomashyo_id', 'product_id')
                    ->withPivot('sarf_miqdori');
    }

    public function getJamiQiymatAttribute(): float
    {
        return round($this->narxi_birlik_uchun * $this->ombordagi_qoldiq, 2);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
