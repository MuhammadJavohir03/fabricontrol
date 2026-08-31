<?php

namespace App\Models;

use Dom\Comment;
use Illuminate\Database\Eloquent\Model;

class ChevarTolov extends Model
{
    protected $table = 'chevar_tolovlar';

    protected $fillable = [
        'chevar_id',
        'company_id',
        'summa',
        'sana',
        'izoh',
    ];

    public function chevar()
    {
        return $this->belongsTo(User::class, 'chevar_id');
    }

    public function company(){
        return $this->belongsTo(Company::class, 'company_id');
    }
}
