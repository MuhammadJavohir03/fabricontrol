<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        'nomi',
        'company_id',
        'chevar_puli',
        'izoh',
    ];

    public function ranglar()
    {
        return $this->hasMany(ProductRang::class, 'product_id');
    }

    public function xomashyolar()
    {
        return $this->belongsToMany(Xomashyo::class, 'product_xomashyolari', 'product_id', 'xomashyo_id')
                    ->withPivot('sarf_miqdori');
    }

    public function ishlabChiqarishlar()
    {
        return $this->hasMany(IshlabChiqarish::class, 'product_id');
    }

    public function sotuvlar()
    {
        return $this->hasMany(Sotuv::class, 'product_id');
    }

    public function chevarIshlari()
    {
        return $this->hasMany(ChevarIsh::class, 'product_id');
    }

    public function braklar()
    {
        return $this->hasMany(Brak::class, 'product_id');
    }

    /** Barcha ranglar bo‘yicha jami soni */
    public function getJamiSoniAttribute(): int
    {
        return (int) $this->ranglar()->sum('soni');
    }

    /** 1 dona tan narx = xomashyo + chevar_puli */
    public function hisoblaTanNarxi(): float
    {
        $material = $this->xomashyolar->sum(
            fn ($x) => $x->narxi_birlik_uchun * $x->pivot->sarf_miqdori
        );

        return round($material + (float) $this->chevar_puli, 2);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
