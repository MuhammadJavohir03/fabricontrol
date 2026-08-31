<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRang extends Model
{
    protected $table = 'product_ranglar';

    protected $fillable = [
        'product_id',
        'company_id',
        'rangi',
        'soni',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function sotuvlar()
    {
        return $this->hasMany(Sotuv::class, 'product_rang_id');
    }

    public function ishlabChiqarishlar()
    {
        return $this->hasMany(IshlabChiqarish::class, 'product_rang_id');
    }

    public function braklar()
    {
        return $this->hasMany(Brak::class, 'product_rang_id');
    }

    /** "Sarafan — Qizil" */
    public function getLabelAttribute(): string
    {
        $nomi = $this->product->nomi ?? '';

        return $nomi !== '' ? "{$nomi} — {$this->rangi}" : $this->rangi;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
