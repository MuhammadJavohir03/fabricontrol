<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sotuvlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('miqdori');
            $table->decimal('narxi_dona', 15, 2)->unsigned();      // sotuv paytida qanday narxda sotilgani
            $table->decimal('tan_narxi_dona', 15, 2)->unsigned();  // sotuv paytidagi 1 dona tan narxi (foyda hisoblash uchun)
            $table->decimal('jami_summa', 15, 2)->unsigned();      // narxi_dona * miqdori
            $table->date('sana');
            $table->enum('holat', ['sotildi', 'buyurtma'])->default('sotildi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sotuvlar');
    }
};