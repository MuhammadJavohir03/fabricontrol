<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * company_id qo'shiladigan jadvallar.
     * "users" alohida ishlanadi, chunki super_admin hech qanday companiyaga bog'lanmasligi kerak.
     */
    private array $tables = [
        'products',
        'xomashyolar',
        'product_ranglar',
        'sotuvlar',
        'ishlab_chiqarishlar',
        'braklar',
        'chiqimlar',
        'yoqotishlar',
        'chevar_ishlari',
        'chevar_tolovlar',
    ];

    public function up(): void
    {
        // 1) users jadvaliga company_id
        if (! Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id')
                    ->constrained('companies')->nullOnDelete();
            });
        }

        // 2) qolgan biznes jadvallariga company_id
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreignId('company_id')->nullable()->after('id')
                        ->constrained('companies')->nullOnDelete();
                });
            }
        }

        // 3) Companiyalar tizimidan oldingi mavjud ma'lumotlarni "Standart" companiyaga biriktiramiz —
        //    hech narsa yo'qolmasin va sayt darrov ishlayversin.
        $defaultId = DB::table('companies')->where('nomi', 'Standart')->value('id');
        if (! $defaultId) {
            $defaultId = DB::table('companies')->insertGetId([
                'nomi'           => 'Standart',
                'tel_nomer'      => null,
                'kirish_muddati' => null, // muddatsiz
                'faol'           => true,
                'izoh'           => "Companiyalar tizimi joriy etilishidan oldingi mavjud ma'lumotlar.",
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // super_admin'dan boshqa barcha foydalanuvchilarni "Standart"ga bog'laymiz
        DB::table('users')
            ->whereNull('company_id')
            ->where('role', '!=', 'super_admin')
            ->update(['company_id' => $defaultId]);

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('company_id');
                });
            }
        }
    }
};
