<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- products ----
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'razmer')) {
                $table->dropColumn('razmer');
            }
            if (Schema::hasColumn('products', 'sotish_narxi')) {
                $table->dropColumn('sotish_narxi');
            }
            if (! Schema::hasColumn('products', 'chevar_puli')) {
                $table->decimal('chevar_puli', 15, 2)->unsigned()->default(0)->after('soni');
            }
        });

        // ---- ishlab_chiqarishlar: eski chevar_id bo'lsa olib tashlash ----
        if (Schema::hasColumn('ishlab_chiqarishlar', 'chevar_id')) {
            Schema::table('ishlab_chiqarishlar', function (Blueprint $table) {
                $table->dropConstrainedForeignId('chevar_id');
            });
        }
        if (Schema::hasColumn('ishlab_chiqarishlar', 'chevar_puli_dona')) {
            Schema::table('ishlab_chiqarishlar', function (Blueprint $table) {
                $table->dropColumn('chevar_puli_dona');
            });
        }

        // ---- chevar ishlari ----
        Schema::create('chevar_ishlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chevar_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('miqdori');
            $table->decimal('pul_dona', 15, 2)->unsigned(); // shu ish uchun 1 dona stavkasi
            $table->decimal('jami_pul', 15, 2)->unsigned(); // miqdori * pul_dona
            $table->string('izoh')->nullable(); // "yeng", "asosi", "tugma"...
            $table->date('sana');
            $table->timestamps();
        });

        // ---- chevar to'lovlari ----
        Schema::create('chevar_tolovlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chevar_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('summa', 15, 2)->unsigned();
            $table->date('sana');
            $table->text('izoh')->nullable();
            $table->timestamps();
        });

        // ---- brak (yaroqsiz mahsulot) ----
        Schema::create('braklar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('miqdori');
            $table->decimal('tan_narxi_dona', 15, 2)->unsigned();
            $table->decimal('summa', 15, 2)->unsigned(); // zarar
            $table->string('sabab')->nullable();
            $table->date('sana');
            $table->timestamps();
        });

        // ---- chiqimlar ----
        if (! Schema::hasTable('chiqimlar')) {
            Schema::create('chiqimlar', function (Blueprint $table) {
                $table->id();
                $table->string('nomi');
                $table->string('kategoriya')->nullable();
                $table->decimal('summa', 15, 2)->unsigned();
                $table->date('sana');
                $table->text('izoh')->nullable();
                $table->timestamps();
            });
        }

        // ---- yo'qotishlar (mato qoldig'i) ----
        if (! Schema::hasTable('yoqotishlar')) {
            Schema::create('yoqotishlar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('xomashyo_id')->constrained('xomashyolar')->cascadeOnDelete();
                $table->decimal('miqdori', 15, 3)->unsigned();
                $table->decimal('summa', 15, 2)->unsigned();
                $table->string('sabab')->nullable();
                $table->date('sana');
                $table->text('izoh')->nullable();
                $table->timestamps();
            });
        }

        // ---- xomashyo rulon_soni ----
        if (! Schema::hasColumn('xomashyolar', 'rulon_soni')) {
            Schema::table('xomashyolar', function (Blueprint $table) {
                $table->unsignedInteger('rulon_soni')->nullable()->after('ombordagi_qoldiq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('braklar');
        Schema::dropIfExists('chevar_tolovlar');
        Schema::dropIfExists('chevar_ishlari');
        Schema::dropIfExists('yoqotishlar');
        Schema::dropIfExists('chiqimlar');
    }
};
