<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['nomi', 'tel_nomer', 'kirish_muddati', 'faol', 'izoh'];

    protected $casts = [
        'kirish_muddati' => 'date',
        'faol'           => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function muddatiTugaganmi(): bool
    {
        return $this->kirish_muddati !== null && $this->kirish_muddati->isPast();
    }

    /** Companiya haqiqatan ham faol (bloklanmagan va muddati tugamagan) */
    public function faolmi(): bool
    {
        return $this->faol && ! $this->muddatiTugaganmi();
    }

    /**
     * Joriy foydalanuvchi uchun "faol" companiya id'si.
     * super_admin uchun — sessiyada tanlangan companiya.
     * Boshqalar uchun — o'ziga biriktirilgan companiya.
     */
    public static function activeId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->role === 'super_admin') {
            return session('active_company_id');
        }

        return $user->company_id;
    }

    public static function active(): ?self
    {
        $id = self::activeId();

        return $id ? static::find($id) : null;
    }

    public function products()
    {
        return $this->hasMany(Products::class, 'company_id');
    }

    public function xomashyolar()
    {
        return $this->hasMany(Xomashyo::class, 'company_id');
    }

    public function sotuvlar()
    {
        return $this->hasMany(Sotuv::class, 'company_id');
    }

    public function ishlabChiqarishlar()
    {
        return $this->hasMany(IshlabChiqarish::class, 'company_id');
    }

    public function yoqotishlar(){
        return $this->hasMany(Yoqotish::class, 'company_id');
    }

    public function chiqimlar(){
        return $this->hasMany(Chiqim::class, 'company_id');
    }

    public function chevarIshlar(){
        return $this->hasMany(ChevarIsh::class, 'company_id');
    }

    public function chevarTolovlar(){
        return $this->hasMany(ChevarTolov::class, 'company_id');
    }

    public function braklar(){
        return $this->hasMany(Brak::class, 'company_id');
    }
}
