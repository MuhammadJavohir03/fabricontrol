<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'toliq_ism',
        'email',
        'company_id',
        'password',
        'tel_nomer',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isChevar(): bool
    {
        return $this->role === 'chevar';
    }

    public function chevarIshlari()
    {
        return $this->hasMany(ChevarIsh::class, 'chevar_id');
    }

    public function chevarTolovlar()
    {
        return $this->hasMany(ChevarTolov::class, 'chevar_id');
    }

    public function braklar()
    {
        return $this->hasMany(Brak::class, 'chevar_id');
    }

    /** Balans = ishlar − to'lovlar − brak jarimasi */
    public function chevarBalans(): float
    {
        $ishlar = (float) $this->chevarIshlari()->sum('jami_pul');
        $tolovlar = (float) $this->chevarTolovlar()->sum('summa');
        $jarima = (float) $this->braklar()->sum('chevar_jarima');

        return round($ishlar - $tolovlar - $jarima, 2);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
