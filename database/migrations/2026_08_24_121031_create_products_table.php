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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nomi');
            $table->string('rangi')->nullable();
            $table->unsignedInteger('soni')->default(0);
            $table->string('razmer')->nullable();
            $table->decimal('sotish_narxi', 15, 2)->unsigned()->nullable();
            $table->text('izoh')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
