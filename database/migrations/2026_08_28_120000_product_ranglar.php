<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ranglar jadvali
        if (! Schema::hasTable('product_ranglar')) {
            Schema::create('product_ranglar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('rangi');
                $table->unsignedInteger('soni')->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'rangi']);
            });
        }

        // 2. Eski ma'lumotlarni ko‘chirish (rangi + soni → product_ranglar)
        if (Schema::hasColumn('products', 'rangi') || Schema::hasColumn('products', 'soni')) {
            $products = DB::table('products')->get();
            foreach ($products as $p) {
                $exists = DB::table('product_ranglar')
                    ->where('product_id', $p->id)
                    ->exists();
                if ($exists) {
                    continue;
                }
                DB::table('product_ranglar')->insert([
                    'product_id' => $p->id,
                    'rangi'      => (isset($p->rangi) && $p->rangi !== null && $p->rangi !== '')
                        ? $p->rangi
                        : 'Standart',
                    'soni'       => (int) ($p->soni ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. sotuvlar, ishlab_chiqarishlar, braklar ga product_rang_id
        if (! Schema::hasColumn('sotuvlar', 'product_rang_id')) {
            Schema::table('sotuvlar', function (Blueprint $table) {
                $table->foreignId('product_rang_id')->nullable()->after('product_id')
                    ->constrained('product_ranglar')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('ishlab_chiqarishlar', 'product_rang_id')) {
            Schema::table('ishlab_chiqarishlar', function (Blueprint $table) {
                $table->foreignId('product_rang_id')->nullable()->after('product_id')
                    ->constrained('product_ranglar')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('braklar', 'product_rang_id')) {
            Schema::table('braklar', function (Blueprint $table) {
                $table->foreignId('product_rang_id')->nullable()->after('product_id')
                    ->constrained('product_ranglar')->nullOnDelete();
            });
        }

        // Eski yozuvlarni birinchi rangga bog‘lash
        $this->linkOldRecords('sotuvlar');
        $this->linkOldRecords('ishlab_chiqarishlar');
        $this->linkOldRecords('braklar');

        // 4. products dan rangi va soni ni olib tashlash
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'rangi')) {
                $table->dropColumn('rangi');
            }
            if (Schema::hasColumn('products', 'soni')) {
                $table->dropColumn('soni');
            }
        });
    }

    private function linkOldRecords(string $table): void
    {
        if (! Schema::hasColumn($table, 'product_rang_id')) {
            return;
        }
        $rows = DB::table($table)->whereNull('product_rang_id')->get();
        foreach ($rows as $row) {
            $rang = DB::table('product_ranglar')
                ->where('product_id', $row->product_id)
                ->orderBy('id')
                ->first();
            if ($rang) {
                DB::table($table)->where('id', $row->id)
                    ->update(['product_rang_id' => $rang->id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sotuvlar', 'product_rang_id')) {
            Schema::table('sotuvlar', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_rang_id');
            });
        }
        if (Schema::hasColumn('ishlab_chiqarishlar', 'product_rang_id')) {
            Schema::table('ishlab_chiqarishlar', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_rang_id');
            });
        }
        if (Schema::hasColumn('braklar', 'product_rang_id')) {
            Schema::table('braklar', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_rang_id');
            });
        }

        // products ga rangi/soni qaytarish
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'rangi')) {
                $table->string('rangi')->nullable()->after('nomi');
            }
            if (! Schema::hasColumn('products', 'soni')) {
                $table->unsignedInteger('soni')->default(0)->after('rangi');
            }
        });

        // Ranglar sonini products.soni ga yig‘ish
        $products = DB::table('products')->get();
        foreach ($products as $p) {
            $first = DB::table('product_ranglar')->where('product_id', $p->id)->orderBy('id')->first();
            $sum = (int) DB::table('product_ranglar')->where('product_id', $p->id)->sum('soni');
            DB::table('products')->where('id', $p->id)->update([
                'rangi' => $first->rangi ?? null,
                'soni'  => $sum,
            ]);
        }

        Schema::dropIfExists('product_ranglar');
    }
};
