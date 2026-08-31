<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;

/**
 * Ushbu trait qo'shilgan modelning:
 *  - barcha so'rovlari avtomatik joriy companiya bilan filtrlanadi;
 *  - yangi yozuv yaratilganda company_id avtomatik to'ldiriladi (agar bo'sh bo'lsa).
 *
 * Ishlatish: model classiga
 *      use App\Models\Concerns\BelongsToCompany;
 *      ...
 *      use BelongsToCompany;
 * qo'shish kifoya.
 */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (empty($model->company_id) && $id = Company::activeId()) {
                $model->company_id = $id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
