<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('xomashyolar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi');
            $table->string('rangi')->nullable();
            $table->enum('birlik', ['metr', 'kg', 'dona', 'litr']);
            $table->decimal('narxi_birlik_uchun', 15, 2)->unsigned();
            $table->decimal('ombordagi_qoldiq', 15, 3)->unsigned()->default(0);
            $table->unsignedInteger('rulon_soni')->nullable(); // faqat kg/metr uchun ixtiyoriy
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xomashyolar');
    }
};