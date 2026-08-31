<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChevarIsh extends Model
{
    protected $table = 'chevar_ishlari';

    protected $fillable = [
        'chevar_id',
        'product_id',
        'company_id',
        'miqdori',
        'pul_dona',
        'jami_pul',
        'izoh',
        'sana',
    ];

    public function chevar()
    {
        return $this->belongsTo(User::class, 'chevar_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function company(){
        return $this->belongsTo(Company::class, 'company_id');
    }
}
