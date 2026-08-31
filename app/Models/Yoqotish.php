<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yoqotish extends Model
{
    protected $table = 'yoqotishlar';

    protected $fillable = [
        'xomashyo_id',
        'company_id',
        'miqdori',
        'summa',
        'sabab',
        'sana',
        'izoh',
    ];

    public function xomashyo()
    {
        return $this->belongsTo(Xomashyo::class, 'xomashyo_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
