<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chiqim extends Model
{
    protected $table = 'chiqimlar';

    protected $fillable = [
        'nomi',
        'company_id',
        'kategoriya',
        'summa',
        'sana',
        'izoh',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
