<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('buyurtmalar')) {
            return;
        }

        // Kerakli jadvallar borligini tekshiramiz
        foreach (['companies', 'users', 'products', 'product_ranglar'] as $t) {
            if (! Schema::hasTable($t)) {
                throw new RuntimeException(
                    "buyurtmalar migratsiyasi uchun «{$t}» jadvali kerak. Avval eski migratsiyalarni ishga tushiring."
                );
            }
        }

        Schema::create('buyurtmalar', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->cascadeOnDelete();

            // Kim buyurtma qildi
            $table->unsignedBigInteger('mijoz_id')->nullable();
            $table->foreign('mijoz_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->string('mijoz_ism')->nullable();
            $table->string('mijoz_tel', 32)->nullable();

            // Bizdagi mahsulot — yo'q bo'lsa null
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->nullOnDelete();

            $table->unsignedBigInteger('product_rang_id')->nullable();
            $table->foreign('product_rang_id')
                ->references('id')->on('product_ranglar')
                ->nullOnDelete();

            $table->string('buyurtma_nomi')->nullable();
            $table->string('rangi', 100)->nullable();

            $table->unsignedInteger('miqdori')->default(1);
            $table->decimal('narxi_dona', 15, 2)->default(0);
            $table->decimal('tan_narxi_dona', 15, 2)->nullable();

            $table->date('muddat')->nullable();
            $table->string('holat', 20)->default('yangi'); // yangi|jarayonda|bajarildi|bekor
            $table->date('sana')->nullable();
            $table->text('izoh')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'holat']);
            $table->index(['company_id', 'muddat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyurtmalar');
    }
};
