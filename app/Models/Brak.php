<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brak extends Model
{
    protected $table = 'braklar';

    protected $fillable = [
        'product_id',
        'company_id',
        'product_rang_id',
        'chevar_id',
        'miqdori',
        'tan_narxi_dona',
        'summa',
        'chevar_jarima',
        'sabab',
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

    public function chevar()
    {
        return $this->belongsTo(User::class, 'chevar_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
