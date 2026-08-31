<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IshlabChiqarish extends Model
{
    protected $table = 'ishlab_chiqarishlar';

    protected $fillable = [
        'product_id',
        'company_id',
        'product_rang_id',
        'miqdori',
        'tan_narxi_dona',
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

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
