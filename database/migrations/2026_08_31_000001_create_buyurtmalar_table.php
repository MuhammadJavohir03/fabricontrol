<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyurtmalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // Kim buyurtma qildi
            $table->foreignId('mijoz_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mijoz_ism')->nullable();
            $table->string('mijoz_tel', 32)->nullable();

            // Nima buyurtma qildi (bizdagi mahsulot — yo'q bo'lsa null)
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_rang_id')->nullable()->constrained('product_ranglar')->nullOnDelete();
            $table->string('buyurtma_nomi')->nullable(); // matn: mahsulot hali yo'q
            $table->string('rangi', 100)->nullable();

            $table->unsignedInteger('miqdori')->default(1);
            $table->decimal('narxi_dona', 15, 2)->default(0);
            $table->decimal('tan_narxi_dona', 15, 2)->nullable(); // tannarx — bog'langanda yoki qo'lda

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
