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
        Schema::create('product_xomashyolari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('xomashyo_id')->constrained('xomashyolar')->cascadeOnDelete();
            $table->decimal('sarf_miqdori', 15, 3)->unsigned();
            $table->timestamps();

            $table->unique(['product_id', 'xomashyo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_xomashyolar');
    }
};
